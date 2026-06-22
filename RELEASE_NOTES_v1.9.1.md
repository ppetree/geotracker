# GeoTracker v1.9.1

GeoTracker 1.9.1 — Joomla 5.x / 6.x compatibility release.

## Changes

- Modernized code: replaced legacy J* globals with namespaced CMS APIs.
- Database: install SQL updated to InnoDB + utf8mb4 and corrected schema (id int(10) unsigned AUTO_INCREMENT).
- Added two migration scripts:
  - ALTER-based (quick): converts table engine/charset and adjusts columns.
  - Conservative atomic-swap (recommended): creates a new table, copies data, and atomically renames tables to preserve a backup.
- WebAssetManager integration for Google Maps script, safer cookie handling, improved JSON/error handling, removal of debug output.
- Manifest bumped to 1.9.1 and targets Joomla 5.x.

## Migration instructions

1. Backup DB and files.
2. On staging, run the conservative migration script `source/sql/updates/mysql/5.0.0-20260622-conservative.sql` then verify counts and engine/charset.
3. After verification, drop the `*_old` table.
4. Alternatively, run the ALTER-based script `source/sql/updates/mysql/5.0.0-20260622.sql` for in-place conversion.

## Verification checklist

- Fresh install on Joomla 5: module installs and table created with InnoDB + utf8mb4.
- Upgrade on existing site: data preserved, map loads, markers displayed.
- Cookies set with samesite flags; visit counter increments correctly.
- No debug outputs in frontend; no Google Maps API console errors.
- Language strings load correctly.

## Rollback notes

- If you used the conservative approach and need to revert:
  - `RENAME TABLE \`#__geotracker_visitors\` TO \`#__geotracker_visitors_new_broken\`, \`#__geotracker_visitors_old\` TO \`#__geotracker_visitors\`;`
- If you used ALTER and need to revert: restore DB from the dump taken before upgrade.

---

Prepared by Copilot — release automation and compatibility updates. Please test on staging before production rollout.