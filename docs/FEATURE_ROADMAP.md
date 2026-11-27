# Rosetta CMS — Roadmap to a WordPress-class Contender

This document lists the feature enhancements and new features that would
make Rosetta CMS a credible alternative to WordPress. It is organized from
highest impact to lower, and each item notes what exists today so the list
stays honest. All high-impact and medium-impact items below are shipped
(2026-11); the remaining items are called out explicitly as open.

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

**Done (2026-11):** in-app Swagger UI at `/api/v1/swagger` (assets vendored
under `public/vendor/swagger-ui/`), linked from the API docs page and the
admin sidebar.

**Done (2026-11):** public read endpoints for media and menus —
`GET /api/v1/media` (paginated, filterable by folder/type, search),
`GET /api/v1/media/{id}`, `GET /api/v1/menus` and
`GET /api/v1/menus/{slug}` (with the menu's full item tree) — documented in
the OpenAPI spec and rendered by the in-app docs page. Legacy unversioned
routes mirror the versioned namespace.

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
- [x] Full-page caching with smart invalidation — `FullPageCache` middleware
      (content-only public GET routes, per-visitor session cookies stripped
      from cached copies); invalidated via `ContentCache::bump()` on content
      save, settings update and menu changes.
- [x] Image pipeline: automatic resizing, WebP conversion, lazy loading,
      responsive srcsets — `ImageOptimizer` (WebP, 1920/1200/800/400 srcsets)
      + `ThemeHelper::responsiveImage()`; `<img loading="lazy">` across themes.
      AVIF conversion is NOT done yet.
- [x] CDN integration with edge caching — `CDNHelper` registered in the
      provider; Media/Post URLs are CDN-aware; driven by `CDN_ENABLED` and
      `CDN_URL` env vars.
- [x] HTTP caching headers — `HttpCacheHeaders` middleware (Cache-Control,
      ETag, Vary). Assets are minified by the Vite production build.
      gzip/brotli is handled at the server/CDN level, not in-app.
- [x] Database query profiling in the dashboard (N+1 detection) —
      `QueryProfiler` middleware + dashboard card.

## Medium-impact enhancements

### 9. Media library depth — ✅ done (2026-11)
- Bulk rename (with auto-increment suffix to avoid overwriting) and bulk
  set-alt-text (images only) from the bulk toolbar, alongside Move and Delete
- File replace on the media detail page (same record/path, re-optimized)
- Auto alt-text generation from the filename + inline metadata editing
  (alt + caption)
- Video/audio/document attachments (audio previews with a working
  `<audio>` player; video/PDF already worked)
- Gallery/carousel block: grid + carousel layouts, column/gap controls,
  lazy-loaded images, keyboard-focusable track, normalized string/associative
  entries

### 10. Forms and contacts — ✅ done (2026-11)
- Contact form with validation, Akismet-lite spam filtering and a
  submissions inbox (`/admin/forms`: status filter, search, stats cards,
  full detail with spam analysis, status updates)
- Mail notifications on new submissions (incl. custom-field values)
- Custom fields for form data (admin-defined in Settings → Form Fields,
  rendered and validated on the public contact form)
- Contact Form 7-style Form Builder plugin (`/admin/forms-builder`):
  visual field builder (text, email, phone, number, textarea, select,
  radio, checkbox, date), per-form settings (submit label, success message,
  notification email, spam toggle), public pages at `/forms/{slug}` with
  honeypot + heuristic spam filtering, mail notifications and a per-form
  submissions inbox (status filter, search, spam analysis, detail view)

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

### 12. Widgets and blocks for the frontend — ✅ done (2026-11)
- Widget areas surfaced in every theme's frontend layout (sidebar,
  before-footer, header — configurable per widget)
- Reusable frontend patterns (hero, feature grid, CTA, testimonial) wired
  into the business theme and shipped in every example theme

### 13. Analytics dashboard — ✅ done (2026-11)
- Pageview tracking (`PageView` + middleware; staff/bots/admins excluded;
  visitor identity is a salted hash of IP + user agent)
- Dashboard at `/admin/analytics`: 7/30/90-day range, totals, daily bar
  chart, top posts/pages/referrers, device and browser breakdowns
- CSV export of the raw table
- Per-site stats (scoped to the current site)

### 14. Plugin and theme ecosystem — ✅ mostly done (2026-11)
WordPress's moat is plugins and themes. Rosetta has a plugin system
(visual editor, SEO, multi-site, WordPress) but it is in-house only.

- Plugin manifest + discovery: third-party plugins in `plugins/` declare a
  `plugin.json` (name, slug, version, description, author, entry,
  permissions, requires_rosetta); the admin plugins page discovers,
  validates and displays them (invalid manifests show errors, not silent
  failures). `plugins/` is a gitignored drop-zone.
- One-click theme install: the themes page uploads and validates a theme
  ZIP (theme folder + theme.json, kebab-case slug, no path traversal) and
  appears in the list immediately; activate and live preview were already
  available.
- Theme demo content: starter content + admin install button + artisan
  command (shipped in #5).
- Plugin security scanning: static scan of plugin PHP files for eval /
  base64_decode / shell execution / filesystem mutation with severity
  badges and per-file details.
- Sandboxing / permission model: manifest-declared permissions (filesystem,
  network, shell, database, admin) are not granted until approved per-plugin
  in the admin (`plugin_approvals`, revocable); pending permissions are
  flagged in the UI.

**Still open:** plugin auto-updates — there is no marketplace registry to
check versions against, so real auto-updates can't be honestly implemented.
The infrastructure for a future marketplace (manifest + permission model +
security scan) is in place; a registry endpoint would be the missing piece.

### 15. Content authoring polish — ✅ done (2026-11)
- Reusable content blocks and pattern library (shipped with the block
  editor; not rebuilt here)
- Custom fields per post type: repeatable key/value rows in the editor
  (`meta.custom_fields`, snake_case keys, validated)
- Inline style/typography in the editor: Quill toolbar exposes font
  family, font size and text color
- Revisions UI with side-by-side diff: two-column from/to layout with
  added/removed rows highlighted, Compare form (pick two revisions) and a
  fixed 'Diff vs latest' link
- Bulk actions across posts: publish/unpublish/trash/restore/delete were
  already there; added author assignment

### 16. Admin UX and accessibility — ✅ done (2026-11)
- WCAG 2.1 AA pass: skip-to-content link, `:focus-visible` outlines,
  `aria-current=page` on the active sidebar link, labelled search input,
  landmark structure
- Keyboard navigation + focus management in the block editor: cards are
  focusable and labelled, ArrowUp/ArrowDown moves selection, Enter focuses
  the editable field, Delete/Backspace removes the selection, Escape
  deselects, icon-only buttons got aria-labels
- Notifications center at `/admin/notifications`: pending comments,
  pending posts, scheduled posts in 7 days, recent activity feed, unread
  badge on the bell icon
- Dark mode polish: real `<button>` with aria-pressed/aria-label,
  pre-paint theme script (no flash), proper dark stylesheet, per-user
  persistence via POST /admin/preferences/theme
- Admin global search: header search box (posts, pages, media, users) with
  results dropdown, keyboard navigation and no-results state
- Dashboard widgets with drag-and-drop layout: sections reorder via HTML5
  drag & drop or keyboard buttons, persisted per user (`dashboard_layout`)

### 17. Proper, simple installer — ✅ done (2026-11)
A one-command CLI installer with a shared preflight/install service used by
both the CLI and the (simplified) web installer.

- `php artisan install`: runs preflight checks, asks for site name / app URL /
  admin email / admin password, writes `.env`, runs migrations and seeds the
  site + settings + admin role + admin user, then locks itself off
  (`INSTALL_ENABLED=false`, `APP_INSTALLED=true`).
- SQLite by default (zero external services required); MySQL/PostgreSQL/SQL
  Server via `--db-connection`.
- Preflight report (PHP version, required extensions, writable storage/,
  dependencies, database connectivity) with plain-language failure messages —
  never raw stack traces.
- Idempotent: refuses to re-install over an existing configured site unless
  `--force` is passed; `--dry-run` previews without writing anything.
- `App\Services\InstallService` is the single source of truth for checks and
  install — the web installer (`/install`) delegates to it, so both paths
  produce the same result.
- Tests: command registration, dry-run isolation, idempotency guard, input
  validation, web check/form rendering, install-enabled gate — 13 new tests.

## Lower-impact / nice-to-have — ✅ all done (2026-11, #17)

- Custom roles with per-post-type and per-field permissions (admin/editor/
  author seeded with full/restricted defaults; enforcement in post and
  custom-post-type controllers; editor locks uneditable fields)
- Custom post types with dedicated UIs (portfolio, testimonial, event —
  own admin list/create/edit/delete flows with a shared type-aware editor)
- Multi-language menus (menu-item labels translatable via `language_content`)
- E-mail templates and transactional email (welcome on registration, custom
  password-reset notification, comment notification to the post author,
  booking notification to the admin)
- Calendar / events module (admin month-grid at `/admin/event/calendar`,
  public `/events` page)
- Bookings / appointments (public `/book` form with honeypot + Akismet-lite
  spam filter; admin inbox at `/admin/bookings`)
- Social login (Google/GitHub via laravel/socialite, per-provider toggles,
  frontend login buttons when enabled)
- In-app chat/support widget (theme-agnostic floating widget, `/chat/messages`
  + poll endpoint, admin inbox at `/admin/chat`, unread badge)
- Real-time collaboration in the editor (lightweight presence/locking with
  heartbeat + 120s stale window; no websockets)
- Public API documentation as a first-class screen (in-app Swagger UI at
  `/api/v1/swagger`, linked from the API docs page and the sidebar)

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
