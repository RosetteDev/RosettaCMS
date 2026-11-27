# Rosetta CMS — Roadmap to a WordPress-class Contender

This document lists the feature enhancements and new features that would
make Rosetta CMS a credible alternative to WordPress. It is organized from
highest impact to lower, and each item notes what exists today so the list
stays honest. Nothing here is implemented yet — it is the prioritized work
list.

## What Rosetta CMS already does well

Baseline (do not re-solve):

- Multi-site support with per-site domains, settings and content scoping
- Posts & pages with drafts/published states, revisions, scheduled fields
- Category/tag taxonomies, comments with pending moderation
- Media library with folders, bulk actions, metadata, images
- Block-based page builder (text, heading, image, gallery, callout, grid,
  quote, divider, html, columns, button, video, spacer) with drag-and-drop
- Menus with drag-and-drop ordering
- Custom widgets
- Theme engine (rosetta theme) with theme options and live theme preview
- WordPress compatibility: WXR export/import, DB migration tool, a
  `wp-json/wp/v2`-style compatibility API (posts/pages/categories/tags)
- SEO plugin (title, meta, canonical, robots, Open Graph, Twitter cards)
- Frontend search, RSS feed, sitemap.xml, robots.txt
- Inline editing and theme preview for editors
- Dashboard with widgets, install wizard, image optimizer, CDN helper,
  cache helper
- Auth (login/register/reset), user roles, profile management
- Languages/content tables wired up: admin language management, frontend
  switcher, translated posts/pages, RTL (see below)

## High-impact enhancements

### 1. A real public REST API (not just the wp-json subset) — ✅ mostly done
The wp-json compatibility layer proves the content model works, but it is
a compatibility shim, not a developer platform. This is WordPress's
biggest ecosystem advantage and Rosetta's biggest gap.

**Done (2026-11):** versioned `/api/v1/...` endpoints (read + write for
posts and pages), API key management (hashed, scoped, expiring, revocable)
with an admin UI at `/admin/api-keys`, bearer/X-API-Key/query-param auth
via `api.key` middleware, per-key rate limiting via the existing global
throttle, and scope enforcement (`posts:write` vs `pages:write`).

**Done (2026-11):** OpenAPI 3.0 document at `/api/v1/openapi.json` with an
in-app docs page at `/api/v1/docs` (every endpoint, parameters, auth, and
webhook signing — self-contained HTML, no CDN dependency). Webhooks on
content events (`post.published`, `post.updated`, `page.published`,
`page.updated`, `comment.created`, `media.created`) — admin UI at
`/admin/webhooks`, signed HMAC-SHA256 delivery, one-time signing secret,
per-site scoping, test-send button. See docs/WEBHOOKS.md.

**Still open:**
- Public API endpoints for media and menus (the spec documents only what
  exists — posts, pages, categories, tags, search)
- Swagger UI proper (the in-app docs page is a self-contained HTML
  reference, not a Swagger UI instance)

### 2. E-commerce foundation — ✅ done (foundation, 2026-11)
WordPress's biggest commercial driver is WooCommerce. The foundation
ships a scoped, honest version of the same: a product catalog, cart,
checkout, orders, coupons and tax settings — plus a clean seam for a
paid module to add live payment capture later.

**Done (2026-11):**

- Products with pricing, sale price, stock, SKU, categories and
  draft/active status (no variants yet)
- Session cart + checkout flow (subtotal, coupon, site tax rate, total)
- Order management dashboard with status transitions and item snapshots
- Coupons (percent/fixed, minimum subtotal, max uses, expiry)
- Tax settings (site-wide rate in Site Settings)
- Self-contained storefront (`/products`, `/cart`, `/checkout`) with no
  theme coupling; theme-integrated storefront is a follow-up
- Payment seam: `PaymentGateway` contract + `payments.default` config —
  the foundation ships a manual/invoice gateway; a paid module implements
  the contract and binds via `PAYMENTS_DEFAULT` without touching checkout

**Deliberately deferred:** variants/options, inventory beyond the stock
counter, shipping calculation, and live payment processing (Stripe/PayPal)
— these belong to the paid module.

See `docs/ECOMMERCE.md`.

### 3. A Gutenberg-class block editor — ✅ done (2026-11)
Rosetta has a block builder. To match WordPress it now has:

- Undo/redo, reusable blocks, and a pattern library
- Nested columns (drag blocks into a columns container, child rows with
  move/delete, compact inline editing) and drag-reorder with drop indicators
- Global styles (theme.json equivalent): color, typography, spacing tokens
- Live preview in the editor and on the frontend (replays the unsaved editor
  state from the session)
- Inline editing polish (dirty state, disabled save until changed, spinner,
  status text)

**Done (2026-11):**
- Undo/redo, drag-reorder with drop indicators, live editor preview
- Global styles: color + typography/spacing tokens from `theme.json`, merged
  with per-site overrides and emitted as CSS custom properties
- Reusable blocks (single-level root templates, saved from the editor)
- Pattern library (curated starter templates inserted from the Add Block modal)
- Nested columns: drag top-level blocks into a columns container, child rows
  with type label / summary / move / delete, compact inline editing for
  heading/button/image/text, Add button inside the container, and patterns /
  reusable blocks inserted into a container
- Frontend live preview: the editor pushes its live state (including unsaved
  nested children) to the JSON preview endpoint, which stores it in the
  session; `/preview/{post}` replays it before rendering
- Inline-editing polish: wired controls, dirty state, disabled save until a
  change, spinner + status text
- Editor-side nesting: two-phase save flow (create top-level blocks → create
  children with `parent_id` → update top-level blocks and children (with
  `parent_id`) → delete removed blocks (404-tolerant) → reorder top-level and
  children); single-level nesting enforced (columns cannot nest inside
  containers)

### 4. Full localization (i18n) — ✅ done
The `languages` and `language_content` tables are now wired up.

**Done (2026-11):**
- Language management (admin CRUD at `/admin/languages`, default language
  cannot be deleted, enabling/disabling per language)
- Frontend language switcher (`/language/{code}` sets the session; flag
  dropdown in the layout, shown when more than one language is enabled)
- Per-post/page translations in the editor (title, excerpt, content per
  enabled language; empty fields fall back to the default language)
- Frontend rendering applies translated title/excerpt/content in the active
  language; translated slugs are applied to public URLs (route fallback
  resolves both the default and the translated slug)
- RTL support via `dir`/`lang` on `<html>` for RTL language codes
  (ar/he/fa/ur/ps/yi/dv/ckb)
- Locale is set from the active language on every request

**Still open:**
- Nothing in this unit — all four items shipped (2026-11):
  - Translated navigation menus and site settings (menu-item labels and
    site title/description stored per language via `language_content`,
    rendered via `resolved_title` / `resolved_site_title`)
  - Translated slugs applied to public URLs (route fallback resolves
    translated slugs; rendered links/canonicals point at the translated slug)
  - Translation memory / reusable string management (`translation_strings`
    table, admin CRUD at `/admin/translations`, `reusable_string()` helper)
  - Per-site language default (nullable `sites.default_language_id`, falls
    back to the global default)

### 5. Scheduled posts, expiry and automation — ✅ done (2026-11)
**Done (2026-11):**
- Future publish dates force `status=pending` in both the admin form and
  the API write endpoint
- `posts:publish-due` artisan command publishes pending posts whose date
  has arrived; runs every minute via `withSchedule`
- Admin posts/pages lists show a "Scheduled" badge; publish form explains
  the behavior
- Public site hides scheduled posts (they stay pending until due)
- Expiry dates: `posts.expires_at`; `posts:expire` unpublishes expired posts
  to draft (never back to pending — that would be re-published instantly);
  runs every minute via `withSchedule`
- Recurring/repeat posts (daily/weekly/monthly): `posts.repeat_frequency` /
  `repeat_until`; a recurring post re-arms to pending with its next occurrence
  after publishing; the recurrence window is honoured
- Scheduled digest email: `posts:digest` emails the site admin a digest of
  recently published posts (default 24h) via a proper `PostDigest` mailable;
  runs daily at 08:00
- Cron-driven maintenance: `posts:purge` permanently deletes trashed posts
  older than 30 days (`--days` to override); runs daily at 04:00

### 6. Security and admin trust — ✅ done (2026-11)

**Done (2026-11):**

- **2FA/TOTP for admin accounts** — RFC 6238 TOTP (`spomky-labs/otphp`)
  with self-service setup in the profile page (secret shown once with an
  `otpauth://` provisioning URI, ±25 s drift tolerance), a login-time
  challenge (`/login/2fa`) and a password-gated disable path. `auth.login` /
  `auth.two_factor_failed` / `auth.two_factor_enabled` / `auth.two_factor_disabled`
  are all written to the activity log.
- **Brute-force rate limiting and login lockout** — IP-keyed rate limiting
  (5 attempts) plus per-account lockout: 5 failed attempts lock the account
  for 15 minutes (even with the correct password); a successful login resets
  the counter; the lock expires automatically.
- **Activity log / audit trail** — every mutating admin request is recorded
  (`activity_logs`: user, action, subject, description, IP, user agent,
  timestamp) via a `log.activity` middleware on the admin group, plus explicit
  `auth.*` entries from the login flow. Browsable at `/admin/activity` with
  user and action filters.
- **IP allowlist/blocklist per site** — `site_ip_rules` (block always wins;
  allow-list applies only when at least one allow rule exists; login,
  registration, password reset, logout, install and the health check stay
  reachable). Managed at `/admin/ip-rules` (validated CIDR/IP).
- **Security headers and CSP** (already in place) + a site-health report at
  `/admin/health` (database connectivity, storage writable, mail configured,
  latest backup present, security headers registered, HTTPS APP_URL, PHP
  version) with a pass/fail summary.

### 7. Backups, restore and migration
- [x] Scheduled backups (database + media) with offsite storage (S3/B2)
      — `backups:create` runs daily at 03:00 and writes to `BACKUP_DISK`
      (`local` by default; `s3` + `AWS_*` for offsite). Managed at
      `/admin/backups` (admin only).
- [x] One-click restore — restore from `/admin/backups` (modal confirmation)
      replaces the database and media library. JSON/CSV content export
      (posts/pages/categories/tags) is also on that page.
- [x] One-click migration between installs — export a backup zip from the
      source (`/admin/backups` or `backups:create`), upload it on the target
      (`/admin/backups` → Import backup), restore with the confirmation
      modal or `php artisan backups:restore {id} --force`. See
      `docs/MIGRATION_AND_STAGING.md`.
- [x] Content import from JSON/CSV — upload a JSON export (full posts/pages/
      categories/tags) or a CSV export (posts/pages skeletons) from
      `/admin/backups`. Duplicate slugs are skipped.
- [x] Site snapshot / staging copy for testing theme changes —
      `php artisan backups:snapshot "label"` creates a restorable snapshot;
      restore onto a staging database with `backups:restore {id} --force`.
      See `docs/MIGRATION_AND_STAGING.md`.

### 8. Performance and scale
- Full-page caching with smart invalidation (object cache exists; extend)
- Image pipeline: automatic resizing, WebP/AVIF conversion, lazy loading,
  responsive srcsets (ImageOptimizer exists — finish it)
- CDN integration with edge caching (CDNHelper exists — finish it)
- HTTP caching headers, gzip/brotli, minified assets
- Database query profiling in the dashboard (N+1 detection)

## Medium-impact enhancements

### 9. Media library depth
- Batch actions beyond delete (rename, alt text, folder move, replace)
- Video/audio/document attachments (currently images only)
- Gallery/carousel block improvements
- Automatic alt-text generation and image description editing

### 10. Forms and contacts
- Contact forms with validation, spam filtering (Akismet-lite), and a
  submissions inbox
- Mail notifications on new submissions
- Custom fields for form data

### 11. SEO optimization pass — ✅ done (2026-11)
A full audit-and-fix pass across every template, with per-site defaults,
structured data, i18n alternates, redirects and health checks.

- Head audit & fix: every page renders a complete `<head>` via
  `themes/_shared/seo` — title template, description, keywords, robots,
  canonical, hreflang alternates (+ `x-default`), OG/Twitter tags
  (incl. `og:locale` per active language), favicon (as a plain URL),
  verification meta tags (Google/Bing/Pinterest/Yandex), feed/sitemap
  links — no empty or duplicate tags. Preview rendering still emits
  `noindex, nofollow`.
- Per-site SEO defaults in the settings UI: title template with `%title%`/
  `%site%` tokens, default description, default OG image (falls back to the
  site logo), Twitter handle, robots allow/disallow rules, search-engine
  verification meta, sitemap on/off — all persisted per site.
- Structured data for content types: Website (homepage), Article/WebPage
  (posts/pages), BreadcrumbList (posts/pages), FAQPage (from post meta
  `faq`), LocalBusiness (contact/about) — JSON-LD emitted as separate
  `application/ld+json` script tags and validated in tests.
- i18n SEO: hreflang alternates in the `<head>` and per-language sitemap
  entries (translations only emit links that actually exist).
- Redirect manager: 301/302 map, admin UI (`/admin/redirects`), per-site
  scope, active/inactive toggle, hit counter, middleware applies matches
  before routing.
- Sitemap index + split sitemaps (`/sitemap.xml` → posts/pages/categories/
  tags), per-site control, excludes drafts and `noindex` posts, image
  entries and per-language alternates.
- robots.txt rules per site (default `/admin`, `/login`, `/search`
  disallowed plus per-site allow/disallow lines, sitemap reference).
- Broken-link checker for internal links in `/admin/seo-health`.
- 404/410 error pages with proper `noindex` (410 rendered for 410
  HttpExceptions).
- Media SEO: alt text enforced for image uploads (admin form + Dropzone
  AJAX → 422), per-language `og:locale`.
- SEO health report in `/admin/seo-health` (sitemap, favicon, OG image,
  description, Twitter handle, verification, alt text, broken links) with
  one-click fixes (favicon/OG image from logo, enable sitemap).
- Tests: head tags, JSON-LD, sitemap index/split sitemaps, robots.txt,
  redirects (301/302/inactive/per-site/hit count), settings persistence,
  SEO health one-click fixes, media alt enforcement, 410 page — 27 new
  tests.

### 12. Widgets and blocks for the frontend
- Widget areas in the theme (currently custom widgets exist — surface them
  in the frontend layout)
- Reusable frontend patterns (hero, feature grid, CTA, testimonial)

### 13. Analytics dashboard
- Pageviews, top posts/pages, referrers, device/browser breakdown
- Per-site stats (currently the dashboard shows site overview, not traffic)
- Export to CSV

### 14. Plugin and theme ecosystem
WordPress's moat is plugins and themes. Rosetta has a plugin system
(visual editor, SEO, multi-site, WordPress) but it is in-house only.

- Plugin manifest + directory/marketplace integration
- One-click theme install/upload/activate with live preview
- Theme demo content (starter sites)
- Plugin auto-updates and security scanning
- Sandboxing / permission model for third-party plugins

### 15. Content authoring polish
- Reusable content blocks and pattern library
- Custom fields per post type (already scaffolded via `meta` — finish it)
- Inline style/typography in the editor
- Revisions UI with side-by-side diff (exists — polish it)
- Bulk actions across posts (publish/unpublish/delete/assign)

### 16. Admin UX and accessibility
- WCAG 2.1 AA compliance across the admin
- Keyboard navigation and focus management in the block editor
- Notifications center in the admin (new comments, pending moderation)
- Dark mode polish (toggle exists — finish it)
- Search within the admin (posts, pages, media, users)
- Dashboard widgets with drag-and-drop layout

## Lower-impact / nice-to-have

- Custom roles with per-post-type and per-field permissions (currently
  admin/editor/author)
- Custom post types with dedicated UIs (portfolio, testimonial, event)
- Multi-language menus
- E-mail templates and transactional email (welcome, password reset,
  notification)
- Calendar / events module
- Bookings / appointments
- Social login (Google/GitHub) for the frontend
- In-app chat/support widget
- Real-time collaboration in the editor (rare, but differentiating)
- Public API documentation as a first-class screen (Swagger UI in-app)

## Recommended first three (highest ROI)

1. **Public REST API + API key management** — turns Rosetta into a
   developer platform, not just a CMS. Everything else (mobile apps,
   headless frontends, third-party tools) unlocks from here.
2. **Scheduled posts + full i18n** — closes the two biggest
   "WordPress-feel" gaps for real users (scheduling and multilingual
   sites).
3. **Backups + one-click restore** — the single trust feature every
   serious site owner checks first; cheap to build on the existing
   media/files model.

## Explicit non-goals (do not chase)

- Becoming the WordPress *plugin directory* — own the ecosystem via
  first-party plugins and a curated marketplace instead.
- Matching WooCommerce 1:1 without first proving the content and
  developer-platform story. E-commerce is a large project; sequence it
  after the API.
- Reimplementing every WordPress plugin — pick the capabilities that
  differentiate (speed, multi-site, modern UI) and ship them well.
