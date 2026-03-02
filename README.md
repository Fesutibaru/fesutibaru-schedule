# Fesutibaru Schedule

A WordPress plugin that displays your festival schedule from the [Fesutibaru](https://fesutibaru.com) platform.

Connect your WordPress site to the Fesutibaru Public API and render a fully styled, responsive schedule on any page using a simple shortcode.

## Installation

1. Download the latest zip from [Releases](https://github.com/Fesutibaru/fesutibaru-schedule/releases)
2. In WordPress Admin, go to **Plugins > Add New > Upload Plugin**
3. Upload the zip and click **Activate**
4. Go to **Settings > Fesutibaru Schedule**
5. Enter your **API Base URL** and **API Key**

> **Where do I get an API key?** Log into your Fesutibaru Planner instance, go to Settings > API Keys, and create a key with read access to events, people, and venues.

## Quick Start

Add this shortcode to any page or post:

```
[fesutibaru_schedule]
```

That's it — your full festival schedule will appear.

## Shortcode Parameters

Customise the output with parameters:

```
[fesutibaru_schedule days="3" view="grid" venue="Main Theatre" show_speakers="yes"]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `view` | `list` \| `grid` | `list` | Display layout |
| `days` | integer | all | Limit to next N days |
| `venue` | string | all | Filter by venue name |
| `search` | string | — | Search events by keyword |
| `limit` | integer | 50 | Max events to display |
| `show_speakers` | `yes` \| `no` | `yes` | Show speaker/author names |
| `show_venues` | `yes` \| `no` | `yes` | Show venue info |
| `date` | `YYYY-MM-DD` | — | Events for a specific date |
| `class` | string | — | Additional CSS class on wrapper |

You can use multiple shortcodes on the same page with different parameters:

```
[fesutibaru_schedule venue="Main Stage" view="grid"]
[fesutibaru_schedule venue="Workshop Tent" view="list"]
```

## Settings

**Settings > Fesutibaru Schedule** in WP Admin:

| Setting | Description |
|---------|-------------|
| **API Base URL** | Your Planner URL, e.g. `https://yourfestival.fesutibaru.com` |
| **API Key** | Bearer token from Planner > Settings > API Keys |
| **Cache Duration** | Minutes to cache API responses (default: 5) |
| **Default View** | `list` or `grid` when not specified in shortcode |

There's also a **Clear Cache** button to force-refresh data immediately.

## How It Works

```
Page load → Shortcode parsed → Check cache
  → Cache hit: render immediately
  → Cache miss: fetch from Fesutibaru API (server-side)
    → Success: cache + render
    → Failure: serve stale cache if available, otherwise show nothing to visitors
```

- All API calls are **server-side** — your API key is never exposed in page source or JavaScript
- Caching uses WordPress Transients with a 24-hour stale backup
- Errors are shown to logged-in admins only, never to site visitors

## Customising the Look

### CSS

The plugin uses BEM-style class names that are easy to override in your theme:

```css
.fesutibaru-schedule { }
.fesutibaru-schedule--list { }
.fesutibaru-schedule--grid { }
.fesutibaru-schedule__day { }
.fesutibaru-schedule__date { }
.fesutibaru-schedule__event { }
.fesutibaru-schedule__time { }
.fesutibaru-schedule__title { }
.fesutibaru-schedule__speakers { }
.fesutibaru-schedule__venue { }
.fesutibaru-schedule__type { }
```

Or add a custom wrapper class via the shortcode: `[fesutibaru_schedule class="my-custom-schedule"]`

### Template Overrides

For full control, copy any template from the plugin's `templates/` directory into your theme:

```
your-theme/fesutibaru-schedule/schedule.php
your-theme/fesutibaru-schedule/event-card.php
your-theme/fesutibaru-schedule/no-events.php
```

The plugin checks your theme directory first, falling back to the bundled templates.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- No external PHP dependencies

## Security

- API key stored in `wp_options`, never in front-end output
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- Settings page protected with nonces and `manage_options` capability
- `uninstall.php` removes all plugin data on deletion

## Building the Zip

To create an installable zip:

```bash
./build-zip.sh
```

This creates `fesutibaru-schedule-0.1.0.zip` ready for upload to WordPress.

## License

GPL-2.0-or-later
