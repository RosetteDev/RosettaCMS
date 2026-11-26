# Migration & Staging Guide

This covers the full backup/migration workflow for Rosetta CMS, built on the
backups feature (`/admin/backups`, `backups:create`, `backups:restore`,
`backups:snapshot`).

## Contents of a backup

A backup is a zip archive containing:

- `manifest.json` — name, creation time, driver, table list, media count
- `database.json` — JSON dump of every table **except** ephemeral state
  (`backups`, `cache`, `cache_locks`, `jobs`, `failed_jobs`, `sessions`,
  `password_reset_tokens`)
- `media/` — the media library (`storage/app/public/media/`)

Backups are stored on the disk configured by `BACKUP_DISK`
(`local` by default → `storage/app/private/backups/`; set `BACKUP_DISK=s3`
plus the `AWS_*` env vars for offsite S3/B2 storage).

## Migrating between installs (e.g. production → staging)

1. **Source install** — create a backup:
   - Web: `/admin/backups` → **Create Backup Now** → download the zip, or
   - CLI: `php artisan backups:create`, or
   - CLI with a label: `php artisan backups:snapshot "pre-release"`
2. **Copy the zip** to the target install (SCP, object storage, or the same
   `BACKUP_DISK` if both installs share it).
3. **Target install** — import and restore it:
   - Web: `/admin/backups` → **Import backup (migration)** → upload the zip →
     click **Restore** on the imported backup (confirmation modal), or
   - CLI: `php artisan backups:restore {backup-id} --force` after the zip
     has been imported (or created) on the target.

Restore is destructive: it replaces the target database and media library.
It runs in a single dependency-ordered transaction (parents first on insert,
children first on delete), so foreign keys stay valid. The CLI refuses to
run without `--force`.

## Staging copy for testing theme changes

1. Take a snapshot on the current install:
   `php artisan backups:snapshot "pre-theme-change"`
2. Point the target environment at a fresh database (or an existing staging
   database).
3. Restore: `php artisan backups:restore {backup-id} --force`
4. Test theme changes on staging. To roll back, re-run the restore from the
   snapshot.

## Export / import (content only)

For moving *content* without the full environment:

- **Export**: `/admin/backups` → **Export JSON** (posts, pages, categories,
  tags) or **Export CSV** (one row per post/page).
- **Import**: `/admin/backups` → upload a `.json` export (full content) or a
  `.csv` export (posts/pages skeletons only). Duplicate slugs are skipped;
  categories/tags are created if missing.

Note: content export does **not** include media files, settings, menus or
users. Use a full backup for those.
