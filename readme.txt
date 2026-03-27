=== Fesutibaru Schedule ===
Contributors: fesutibaru
Tags: festival, schedule, events, shortcode, fesutibaru
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.1.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your festival schedule from the Fesutibaru platform using a simple shortcode.

== Description ==

Fesutibaru Schedule connects your WordPress site to the [Fesutibaru](https://fesutibaru.com) festival management platform. It pulls your event, speaker, and venue data via the Fesutibaru Public API and renders a schedule on any page or post using a shortcode.

**Features:**

* Simple `[fesutibaru_schedule]` shortcode
* List and grid views
* Filter by venue, date, or search term
* Automatic caching with configurable TTL
* Graceful fallback to stale cache when the API is unreachable
* Overridable templates for full theme control
* No external PHP dependencies
* Works with Classic Editor and Gutenberg

== Installation ==

1. Download the plugin zip from [GitHub](https://github.com/Fesutibaru/fesutibaru-schedule/releases)
2. In WordPress Admin, go to Plugins > Add New > Upload Plugin
3. Upload the zip file and activate the plugin
4. Go to Settings > Fesutibaru Schedule
5. Enter your API Base URL and API Key (found in Planner > Settings > API Keys)
6. Add `[fesutibaru_schedule]` to any page or post

== Shortcode Parameters ==

* `view` — `list` or `grid` (default: list)
* `days` — Limit to next N days
* `venue` — Filter by venue name
* `search` — Search term
* `limit` — Max events to display (default: 50)
* `show_speakers` — `yes` or `no` (default: yes)
* `show_venues` — `yes` or `no` (default: yes)
* `date` — Show events for a specific date (YYYY-MM-DD)
* `class` — Additional CSS class on wrapper

Example: `[fesutibaru_schedule days="3" view="grid" venue="Main Theatre"]`

== Template Overrides ==

Copy templates from the plugin's `templates/` directory to your theme:

`your-theme/fesutibaru-schedule/schedule.php`
`your-theme/fesutibaru-schedule/event-card.php`
`your-theme/fesutibaru-schedule/no-events.php`

== Frequently Asked Questions ==

= Where do I get an API key? =

Log into your Fesutibaru Planner instance, go to Settings > API Keys, and create a new key with read access to events, people, and venues.

= Is my API key secure? =

Yes. The API key is stored in the WordPress database and all API calls are made server-side. The key is never exposed in front-end HTML or JavaScript.

= Can I use multiple shortcodes on one page? =

Yes. Each shortcode with different parameters will fetch and cache its data independently.

== Changelog ==

= 0.1.1 =
* Fix: Map API response field names (camelCase) to match plugin expectations
* Fix: Events now grouped by date instead of showing "Date TBC"
* Fix: Event times, types, and speaker names now display correctly

= 0.1.0 =
* Initial release
* Shortcode with list and grid views
* Settings page for API configuration
* Transient-based caching with stale fallback
* Overridable templates
