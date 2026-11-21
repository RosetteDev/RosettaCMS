# RosettaMaps.com — Live-Site Security Audit

**Date:** 2026-11-20
**Scope:** Black-box audit of the live site `rosettamaps.com` (web app, JSON API, subdomains). The local RosettaCMS codebase was **out of scope** — no code-level findings from it appear here.
**Method:** Unauthenticated + low-privilege authenticated probes using test accounts created on the live site (see Cleanup Notes). All probes were non-destructive or self-cleaned.

---

## Findings

### HIGH

#### H1. `/admin` is publicly reachable without authentication
- **Endpoint:** `GET https://rosettamaps.com/admin`
- **Evidence:** Returns `200 OK` with the full 13,794-byte admin SPA shell (no login redirect, no 401/403). The page even contains the comment `GA4 intentionally disabled on authenticated tool pages`. `robots.txt` additionally discloses `/admin` and `/api` to crawlers.
- **Impact:** The admin tool's existence and full client-side surface (admin API routes, payloads) are exposed to anyone; combined with H2, an unauthenticated visitor can map the entire admin attack surface.
- **Fix:** Return 302 → `/login` (or 401/403) for unauthenticated requests to `/admin`; remove `/admin` and `/api` from `robots.txt`.

#### H2. User/affiliate ID enumeration + framework leak — authz check runs *after* model lookup on admin endpoints
- **Endpoints:** `POST /api/admin/users/{id}/ban`, `POST /api/admin/users/{id}/set-role`, `POST /api/admin/affiliates/{id}/record-payout`
- **Evidence (authenticated as plain user 568):**
  - Existing IDs → `403 {"message":"Moderator access required."}` (e.g. user 568, affiliates 1 and 2)
  - Nonexistent ID → `404 {"message":"No query results for model [App\\Models\\User] 999999"}` (same pattern for `[App\\Models\\Affiliate]`)
  - Contrast: `POST /api/admin/users/promote` checks authorization first (403 in both cases) — proving the pattern is inconsistent, not intended.
- **Impact:** Any authenticated non-moderator can linearly enumerate valid user and affiliate IDs and learn the Eloquent model class names (`App\Models\User`, `App\Models\Affiliate`) — a framework/fingerprinting leak that eases targeted attacks.
- **Fix:** Run the role/authorization gate before the model lookup; return the same status/message for missing and forbidden IDs.

#### H3. Any authenticated user can create real live Stripe checkout sessions
- **Endpoint:** `POST /api/billing/checkout` (body `{}`)
- **Evidence:** `200` with `{"checkout_url":"https://checkout.stripe.com/g/pay/cs_live_agyDoMXdTzWHbCY5JtN2l0nnbeNIsMCBx3EoNUPyCH0kjlMLviFqQk5Bs9..."}` — a **live** (`cs_live_...`) Stripe session was created on the site's own Stripe account.
- **Impact:** Any logged-in account (even the audit bot with a used-up Pro trial) can spam the operator's Stripe dashboard / create unlimited sessions; no coupon, price, or eligibility validation is applied before session creation.
- **Fix:** Validate price/product server-side and throttle per user (and per IP); return an error instead of creating a session when there is nothing valid to buy.

---

### MEDIUM

#### M1. No HTTP→HTTPS redirect on any vhost (cleartext credential risk)
- **Evidence:** `http://rosettamaps.com`, `http://www.…`, `http://mail.…`, `http://webmail.…`, `http://cpanel.…` all return `200` on plain HTTP with no redirect; `webdisk` returns `401` on plain HTTP.
- **Impact:** Users typing the URL (or followed from a link) can be served the site, login, mail, or cPanel over cleartext — credentials in transit are exposed to network attackers.
- **Fix:** 301 all HTTP traffic to HTTPS on every vhost.

#### M2. Missing security headers on the site
- **Evidence:** Homepage response has **no** `Strict-Transport-Security`, `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, or `Referrer-Policy`.
- **Impact:** No HSTS pinning (enables SSL-stripping once M1 is exploited), no clickjacking protection on authenticated pages, MIME-sniffing exposure.
- **Fix:** Send `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`/CSP `frame-ancestors`, and a strict `Referrer-Policy`.

#### M3. cPanel hostnames publicly exposed (and over HTTP)
- **Evidence:**
  - `mail`, `webmail`, `cpanel` → `200` on plain **and** HTTPS (login forms in the clear)
  - `webdisk` → `401` both (WebKitDAV login page)
  - `autodiscover` over HTTP → `302 https://cpanelemaildiscovery.cpanel.net/autodiscover/autodiscover.xml` (reveals cPanel vendor)
  - The Let's Encrypt certificate SANs list `cpanel/mail/webmail/autodiscover/webdisk.rosettamaps.com` **plus `temporary.site`** (the cPanel management domain).
- **Impact:** Full hosting-stack disclosure (cPanel shared hosting); credential-guessing against webmail/cPanel/webdisk is trivially discoverable and possible in cleartext.
- **Fix:** Block or redirect non-443 on mail/cpanel/webdisk vhosts; hide cpanel/webdisk at DNS level if not needed publicly; consider a wildcard cert that doesn't expose `temporary.site`.

#### M4. Spot API returns internal fields; spot IDs are enumerable end-to-end
- **Evidence:**
  - `GET /api/spots` rows include `contributor_id`, `share_token`, `is_private`, `seo_quality_score`, `flagged_for_review`, `removal_reason`, etc. (no field whitelist).
  - Sitemap lists 8,268 sequential `/spots/{num}` URLs; bare `/spots/{num}` 301-reveals the canonical slug; `GET /api/spots/{id}` for arbitrary IDs returns the full detail object (e.g. 305030 → contributor 213 "Jmisco", 302484 → contributor 12 "DewieJones", with photo URLs).
  - Nonexistent IDs → `404 {"message":"No query results for model [App\\Models\\Spot] 99999999"}` (framework + input echo on every ID).
  - Total corpus is small: **32,922 spots** — fully enumerable.
- **Impact:** Complete enumeration of every spot including private ones and internal review/metadata fields; contributor-identity mapping (spot → user ID) is public.
- **Fix:** Serializer field whitelist per audience; generic 404 message; consider per-spot rate limiting on detail fetches.

#### M5. CORS is fixed `Access-Control-Allow-Origin: *`
- **Evidence:** API responses carry `Access-Control-Allow-Origin: *` regardless of origin.
- **Impact:** Any website can read API responses from a browser (lowered today because auth is bearer-token, not cookie — but any future cookie/auth change silently widens cross-site reads).
- **Fix:** Allow-list the app's origins (or reflect only known ones) and send `Vary: Origin`.

#### M6. Rate limiting is per-IP only, shared across endpoints
- **Evidence:**
  - Login: 5 × `401 Invalid credentials.`, then `429 Too Many Attempts.` — **and the 11th attempt with the correct password also returned 429** (throttle threshold ≈ 5, lockout applies even to valid logins).
  - `POST /api/email/resend-verification`: `200 200 429 429 429 429` (throttled after 2 sends).
  - A flood on one endpoint 429'd *other* endpoints (register/contact) from the same IP — the limiter is per-IP and appears shared.
- **Impact:** A single IP can be locked out of *everything* (availability); one endpoint can be brute-forced without locking others; distributed (multi-IP) brute force is unmitigated.
- **Fix:** Per-account + per-IP limits on login; dedicated limiters per endpoint; longer/adaptive windows.

---

### LOW / INFORMATIONAL

| # | Finding | Evidence |
|---|---------|----------|
| L1 | Eloquent model-class names leak in 404s on every `/api/spots/{id}` and admin ID route | `"No query results for model [App\\Models\\Spot] 99999999"` |
| L2 | `robots.txt` discloses `/admin` and `/api` | `Disallow: /admin`, `Disallow: /api` |
| L3 | Empty-body POST to `/api/*` returns Apache `406 Not Acceptable!` (Mod_S content negotiation) — server-stack fingerprint | `POST /api/logout` with no body |
| L4 | `GET /api/web/layers/mvum-v2/segments/1` → `404 {"message":""}` (empty error body) | as tested |
| L5 | 403 on `DELETE /api/spots/302484/photos/197` precedes the message with the caller's full subscription/pro-trial JSON (`pro_trial.active:true, started_at 2026-11-20T22:02:26Z`) — state leak on error path | as tested |
| L6 | GA4 ID `G-6JCY726CY5` exposed on pages (also disabled on admin pages by design, per HTML comment) | page source |
| L7 | `POST /api/contact` requires an undisclosed valid `inquiry_type` (`"general"` → `The selected inquiry type is invalid.`) — forces response-fuzzing to discover values | as tested |
| L8 | Sitemap contains 8,277 URLs (8,268 sequential spot slugs) — amplifies M4 enumeration | `/sitemap.xml` |

---

## Positive findings (working as intended)

- **No CSRF vector:** the API is **bearer-token only** (token from login response, stored in `localStorage.rm_token`). Cookie-only `GET /api/me` → `401`; `POST /api/login` without the XSRF header → `401` (not 419). Session cookie never changes on login.
- **No user enumeration on forgot-password:** real and fake emails both return identical `200 {"message":"If an account exists for that email, a password reset link has been sent."}`
- **Password-reset token handling:** random token → generic `422 "This password reset token is invalid."` (no oracle).
- **Registration integrity:** server-side email deliverability check; username+email uniqueness enforced (`422 "already been taken"`).
- **Spot ownership enforced:** `PATCH /api/spots/302484` (owner 12) as user 568 → `403 "You can only edit listings that you added."`; `PATCH /api/spots/305030` → `403 "Moderator access required."` (distinct, both correct); photo DELETE on a foreign spot → `403`.
- **Admin role gate:** every admin GET/POST as a plain user → `403 "Moderator access required."` (except the H2 ordering bug — no broken access control found).
- **Feature gates work:** `POST /api/account/pro-trial` → `422 "This account is not eligible for a Pro trial."` (trial already used); `POST /api/routes/preview` → `403 "This feature is currently available to beta testers."`
- **Parameter-injection robust:** `per_page=abc/-1/501`, `page=999999`, `id[0]=1`, `lat=abc`, `lng[0]=1`, bogus `type`/`sort`/`search`/`name` → all `200`, implicitly clamped, no 500s.
- **`APP_DEBUG` off:** no stack traces anywhere; clean JSON error bodies.
- **Login brute-force throttling present** (5 attempts — see M6 for scoping).
- **Self-service lifecycle sound:** own-spot create → rename (200) → delete (200 "Listing deleted.") → `404 "This spot has been removed."`; favorites toggle cleanly.

---

## Test-account cleanup notes (actions needed by the operator)

Three accounts were created on the **live** site during the audit:

| Email | Username | State | Password |
|---|---|---|---|
| `audit.test.bot+01@proton.me` | `audit_test_bot` | unverified | `AuditTest!2026x` |
| `rosetta10263@euploading.com` | `audit_bot_10263` | unverified | unknown (candidate passwords rejected; account may have been registered via an email link) |
| `rosetta82729@euploading.com` | `audit_bot_82729` | **verified, user ID 568**, Pro trial active/used (started 2026-11-20T22:02:26Z) | `AuditBot!2026x` |

**Durable artifacts left on the live site:**
- **1 live Stripe checkout session** `cs_live_agyDoMXdTzWHbCY5JtN2l0nnbeNIsMCBx3EoNUPyCH0kjlMLviFqQk5Bs9` (created via H3) — will auto-expire; may show in the Stripe dashboard as an unused session.
- **Login throttle self-inflicted:** the last probe used 10 failed logins against `rosetta82729`, so that account is throttled for ~1 minute (auto-clears).
- No other durable writes: spot 308674 was created **and deleted**; favorite on 305030 toggled on **and back off**; no reports, payouts, bans, or role changes succeeded (all 403/404/422).

**Recommended operator actions:** ban/delete the three accounts above (or at minimum 568, which has a consumed Pro trial), and check the Stripe dashboard for the stray checkout session.

---

## Out of scope (for the record)

Findings from the **local RosettaCMS codebase** (seeded passwords, CVE-2026-48019 on Laravel 11.57.1, `MediaController` path traversal, `SanitizeContent` XSS, seeded `javascript:` menu URL) belong to the RosettaCMS project and are tracked in `SECURITY_AUDIT_REPORT.md` — they are deliberately **not** included in this live-site report.
