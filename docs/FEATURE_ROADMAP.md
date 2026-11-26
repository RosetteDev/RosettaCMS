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

**Still open:**
- Documented schema (OpenAPI/Swagger UI) for posts, pages, media, menus,
  categories, tags (an in-app docs screen exists via `/api/v1/` discovery
  document, but no Swagger UI yet)
- Webhooks on content events (publish, update, comment, new media)

### 2. E-commerce foundation (or a deliberately positioned alternative)
WordPress's biggest commercial driver is WooCommerce. Even a minimal
product catalog with cart, checkout, orders, and payment capture
(Stripe) would put Rosetta in the same league for small businesses.

- Products with variants, pricing, inventory, categories
- Cart + checkout flow with shipping/tax calculation
- Order management dashboard
- Stripe (and PayPal) payment capture
- Coupons and tax settings

If e-commerce is out of scope, the honest equivalent is a well-positioned
"content-first" niche (portfolio/agency/blog) with an explicit promise of
speed and simplicity — but it should be a deliberate product decision,
not an accident.

### 3. A Gutenberg-class block editor
Rosetta has a block builder, but it is not yet a visual editor. To match
WordPress it needs:

- Undo/redo, reusable blocks, and a pattern library
- Nested columns and drag-reorder with drop indicators
- Global styles (theme.json equivalent): color, typography, spacing tokens
- Live preview in the editor and on the frontend (currently preview is
  post-save, not live)
- Inline editing polish (already scaffolded — finish it)

### 4. Full localization (i18n) — ✅ done (core)
The `languages` and `language_content` tables are now wired up.

**Done (2026-11):**
- Language management (admin CRUD at `/admin/languages`, default language
  cannot be deleted, enabling/disabling per language)
- Frontend language switcher (`/language/{code}` sets the session; flag
  dropdown in the layout, shown when more than one language is enabled)
- Per-post/page translations in the editor (title, excerpt, content per
  enabled language; empty fields fall back to the default language)
- Frontend rendering applies translated title/excerpt/content in the active
  language (default language unchanged; translated slugs stored but not
  applied yet — the canonical URL stays on the default slug)
- RTL support via `dir`/`lang` on `<html>` for RTL language codes
  (ar/he/fa/ur/ps/yi/dv/ckb)
- Locale is set from the active language on every request

**Still open:**
- Translated navigation menus and site settings
- Translated slugs applied to public URLs (currently stored but not used)
- Translation memory / reusable string management
- Per-site language default (currently site-wide)

### 5. Scheduled posts, expiry and automation — ✅ scheduled publishing done
**Done (2026-11):**
- Future publish dates force `status=pending` in both the admin form and
  the API write endpoint
- `posts:publish-due` artisan command publishes pending posts whose date
  has arrived; runs every minute via `withSchedule`
- Admin posts/pages lists show a "Scheduled" badge; publish form explains
  the behavior
- Public site hides scheduled posts (they stay pending until due)

**Still open:**
- Expiry dates (auto-unpublish after a date)
- Recurring/repeat posts (daily/weekly/monthly)
- Scheduled newsletters and digest emails
- Cron-driven maintenance tasks beyond publish (expire, purge)

### 6. Security and admin trust
- 2FA/TOTP for admin accounts (WordPress needs plugins for this; core
  support would differentiate)
- Brute-force rate limiting and login lockout
- Activity log / audit trail of admin actions (who changed what, when)
- IP allowlist/blocklist per site
- Security headers and CSP (already in place) + a site-health report

### 7. Backups, restore and migration
- [x] Scheduled backups (database + media) with offsite storage (S3/B2)
      — `backups:create` runs daily at 03:00 and writes to `BACKUP_DISK`
      (`local` by default; `s3` + `AWS_*` for offsite). Managed at
      `/admin/backups` (admin only).
- [x] One-click restore — restore from `/admin/backups` (modal confirmation)
      replaces the database and media library. JSON/CSV content export
      (posts/pages/categories/tags) is also on that page.
- [ ] One-click migration between installs (zip export + import flow)
- [ ] Content import from JSON/CSV
- [ ] Site snapshot / staging copy for testing theme changes

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

### 11. SEO optimization pass
The SEO plugin exists and is wired up, but the work that makes a
WordPress-class site actually rank — a complete audit-and-fix pass
across every template, with redirects, structured data and per-site
defaults — is missing. This is one deliberate pass, not five small
tickets.

**Already in place (do not re-solve):**
- Per-post/page SEO fields in the editor: SEO title, meta description,
  focus keyword, robots, canonical URL, OG image — plus a live
  Google-style SERP preview and character counter
- `themes/_shared/seo` head partial renders title, description,
  keywords, robots, canonical, OG and Twitter tags from post meta +
  settings
- Title suffix applied in `seo()` (`seo_title_suffix` setting)
- JSON-LD structured data: Website (homepage), Article (single posts),
  LocalBusiness (contact/about pages)
- XML sitemap (posts, pages, categories, tags; featured-image entries;
  per-site toggle via `enable_xml_sitemap`) and robots.txt (`/admin`,
  `/login`, `/search` disallowed + sitemap reference)
- Preview rendering emits `noindex, nofollow`

**The pass:**
- Audit and fix every page template so each emits a valid, complete
  `<head>`: title template, description, canonical, OG/Twitter, robots,
  favicon — and no empty/duplicate tags
- Per-site SEO defaults in the settings UI: title template with a `%s`
  placeholder, default description, default OG image/site name, Twitter
  handle, robots rule, search-engine verification meta (Google, Bing,
  Yandex), canonical fallback
- Structured data for every content type: Article (exists), BreadcrumbList
  on posts/pages/archives, FAQPage, Product/Event when those post types
  arrive; validate JSON-LD and add tests
- i18n SEO: `hreflang` alternates in the `<head>` and per-language
  sitemap entries (translations are stored but not applied yet)
- Redirect manager: 301/302 map, admin UI, per-site scope, 404 fallback
  rules
- Sitemap index + split sitemaps by content type (posts/pages/categories/
  tags), per-site control, exclude noindex/drafts, image entries for
  pages/categories/tags (currently only posts and pages)
- robots.txt rules per site (disallow patterns, per-site sitemap)
- Broken-link checker for internal links in the dashboard
- 404/410 error pages with proper `noindex`
- Media SEO: alt text enforced in the editor, OG image picker (not just a
  URL field), per-language OG locale
- SEO health report in the admin (missing title/description/canonical,
  no OG image, noindexed pages) with one-click fixes
- Tests: sitemap/robots/JSON-LD/head-tag rendering, redirect behavior,
  verification meta

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
