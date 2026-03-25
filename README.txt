=== SMNTCS Simple Events Widget ===

Contributors:       nielslange
Tags:               Simple Events, Event, Widget, Sidebar
Stable tag:         2.5
Tested up to:       6.9
Requires at least:  3.4
Requires PHP:       7.4
License:            GPL v2 or later
License URI:        https://www.gnu.org/licenses/gpl-2.0.html

Add event dates to your content and list upcoming or past events in a classic sidebar widget.

== Description ==

SMNTCS Simple Events Widget is a lightweight way to pin calendar dates to individual posts, pages, or WooCommerce products and show them in the sidebar when visitors browse your site.

You pick a start date and, if you want, an end date, right in the editor. Then you drop the *Simple Events Widget* into a widget area and decide whether to show what is still ahead, what has already passed, or both. You can show only the start date or start and end together, change the sort order, add a line break before the title, and dates respect your site’s language and date format.

* Event fields (with date picker) on posts, pages, and on products when WooCommerce is active
* Widget: upcoming events, previous events, or both
* Display start date only or start and end date
* Sort order and optional line break between date and title
* Classic WordPress widget—place it under **Customize → Widgets** or in your theme’s widget regions

== Installation ==

1. Upload `smntcs-simple-events-widget` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Go to post or page and provide a start date of the event.
4. If wanted, also provide an end date of the event.
5. Go to `Customize » Widgets`, add the `Simple Events Widget` to the required sidebar.
6. Adjust the widget settings according to your needs.

== Screenshots ==

1. Create page or post and specify event date.
2. Add `Simple Events Widget` to the sidebar.
3. See desired events within the sidebar.

== Changelog ==

= 2.5 (2025.03.25) =

- Tested up to WordPress 6.9
- Add widget options for event sort order and line break between date and title
- Use date_i18n for localized date output
- Include WooCommerce products when querying events
- Update Composer dev dependencies; replace phpcs.dist.xml with phpcs.xml; refresh GitHub Actions

= 2.4 (2025.03.23)

- Tested up to WordPress 6.8

= 2.3 (2024.04.07) =

- Tested up to WordPress 6.5

= 2.2 (2023.11.12) =

- Fix datepicker issue

= 2.1 (2023.11.12) =

- Convert to OOP

= 2.0 (2023.10.28) =

- Fix i18n issue

= 1.9 (2023.10.21) =

- Tested up to WordPress 6.4

= 1.8 (2023.05.28) =

- Fix warning when regarding undefined array key

= 1.7 (2023.05.07) =

- Only execute plugin on posts and pages
- Remove line break after event list

= 1.6 (2023.05.07) =

- Show message when no events are available
- Tested up to WordPress 6.2

= 1.5 (2022.12.03) =

- Tested up to WordPress 6.1

= 1.4 (2022.05.29) =

- Tested up to WordPress 6.0

= 1.3 (2022.01.01) =

- Tested up to WordPress 5.9

= 1.2 (2019.12.31) =

- Tested up to WordPress 5.8

= 1.1 (2019.02.19) =

- Tested up to WordPress 5.0

= 1.0 (2018.04.09) =

- Initial release
