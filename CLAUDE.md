# Claude Code Instructions — fesutibaru-schedule

## What This Is

A standalone WordPress plugin that connects to the Fesutibaru Public API and renders festival schedules via a `[fesutibaru_schedule]` shortcode. This is a **public repo** — it serves as both distribution point and issue tracker for festivals using WordPress.

## Repository

- **GitHub**: https://github.com/Fesutibaru/fesutibaru-schedule
- **Local**: `/Users/alan/newdev/fesutibaru-schedule`
- **Related**: The Planner app lives at `/Users/alan/newdev/planner` — that's where the Public API (v1) is implemented

## Release Workflow

When asked to release a new version:

1. Bump the version in `fesutibaru-schedule.php` (the `Version:` header) and in the `FESUTIBARU_SCHEDULE_VERSION` constant
2. Update `Stable tag:` in `readme.txt` to match
3. Commit
4. Run `./build-zip.sh` — creates `fesutibaru-schedule-{version}.zip`
5. Push to main
6. Create a GitHub release: `gh release create v{version} ./fesutibaru-schedule-{version}.zip --repo Fesutibaru/fesutibaru-schedule --title "v{version}" --notes "..."`

The zip is the distribution artifact — festivals download it from the Releases page and upload via WordPress Admin > Plugins.

## Key Architecture

- **Pure PHP, no build step** — no npm, no composer, no bundler
- **No external dependencies** — uses WordPress HTTP API (`wp_remote_get`), Transients API, Options API
- **Server-side only** — API key never exposed in front-end HTML or JS
- **Template override pattern** — themes can override by copying templates to `their-theme/fesutibaru-schedule/`

## File Structure

```
fesutibaru-schedule.php          — Entry point, shortcode + asset registration
includes/class-api-client.php    — HTTP client for /api/v1/ endpoints
includes/class-cache.php         — WordPress Transients with stale fallback
includes/class-settings.php      — WP Admin settings page
includes/class-shortcode-renderer.php — Shortcode → API → template
templates/schedule.php           — Day-grouped event layout
templates/event-card.php         — Single event card
templates/no-events.php          — Empty state
assets/css/schedule.css          — Default styles (BEM classes)
assets/js/schedule.js            — Collapsible day sections
uninstall.php                    — Cleanup on plugin deletion
readme.txt                       — WordPress Plugin Directory format
build-zip.sh                     — Creates installable zip
```

## API Dependency

The plugin calls the Planner's Public API v1:
- `GET /api/v1/events`
- `GET /api/v1/venues`
- `GET /api/v1/people`

API key auth (Bearer token), rate limited at 100 req/min. The API code lives in the Planner repo at `src/lib/api/`.

## Conventions

- WordPress coding standards (PHP)
- BEM CSS class naming: `.fesutibaru-schedule__element--modifier`
- All output escaped: `esc_html()`, `esc_attr()`, `esc_url()`
- Errors shown to admins only, never to site visitors
- This is a public repo — no secrets, no internal references in committed code
