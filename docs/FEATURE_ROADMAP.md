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
- Languages/content tables exist but are unused (see below)

## High-impact enhancements

### 1. A real public REST API (not just the wp-json subset)
The wp-json compatibility layer proves the content model works, but it is
a compatibility shim, not a developer platform. This is WordPress's
biggest ecosystem advantage and Rosetta's biggest gap.

- Authenticated API with token/OAuth auth and rate limiting
- Documented schema (OpenAPI/Swagger UI) for posts, pages, media, menus,
  categories, tags
- API key management UI (create/revoke/scope keys)
- Versioned endpoints (`/api/v1/...`)
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

### 4. Full localization (i18n)
The `languages` and `language_content` tables exist but are unused.
WordPress's multilingual story (WPML/Polylang) is a major reason it is
dominant.

- Language switcher in admin and frontend
- Per-site language with translated content
- Translated navigation menus and site settings
- RTL support
- Translation memory / reusable string management

### 5. Scheduled posts, expiry and automation
- Schedule publishing and expiry dates (partially present via
  `published_at` — wire it up)
- Recurring/repeat posts (daily/weekly/monthly)
- Scheduled newsletters and digest emails
- Cron-driven maintenance tasks (publish, expire, purge)

### 6. Security and admin trust
- 2FA/TOTP for admin accounts (WordPress needs plugins for this; core
  support would differentiate)
- Brute-force rate limiting and login lockout
- Activity log / audit trail of admin actions (who changed what, when)
- IP allowlist/blocklist per site
- Security headers and CSP (already in place) + a site-health report

### 7. Backups, restore and migration
- Scheduled backups (database + media) with offsite storage (S3/B2)
- One-click restore and migration between installs
- Content export in JSON/CSV (not just WXR) and import
- Site snapshot / staging copy for testing theme changes

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

### 11. SEO depth
The SEO plugin exists but is config-driven, not UI-driven.

- Per-post SEO meta editor (title, description, OG image)
- Per-site SEO defaults (title template, description, Twitter card)
- Schema.org markup for posts, products, FAQs
- Redirect manager (301s) and broken-link checker
- Sitemap split by content type with per-site control

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
