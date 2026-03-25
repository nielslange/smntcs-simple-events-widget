# SMNTCS Simple Events Widget

![Support Level](https://img.shields.io/badge/support-active-green.svg)
![Build Status](https://github.com/nielslange/smntcs-simple-events-widget/actions/workflows/test.yml/badge.svg)
![GPLv2 License](https://img.shields.io/github/license/nielslange/smntcs-simple-events-widget.svg)
![Compatible to WordPress version](https://plugintests.com/plugins/smntcs-simple-events-widget/wp-badge.svg)
![Compatible to PHP version](https://plugintests.com/plugins/smntcs-simple-events-widget/php-badge.svg)
![Downloads](https://img.shields.io/wordpress/plugin/dt/smntcs-simple-events-widget.svg)
![Plugin Version](https://img.shields.io/wordpress/plugin/v/smntcs-simple-events-widget.svg)
![Tag Version](https://img.shields.io/github/tag/nielslange/smntcs-simple-events-widget.svg)

Sidebar widget to show (upcoming and previous) events.

## Installation

1. Upload `smntcs-simple-events-widget` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Go to post or page and provide a start date of the event.
4. If wanted, also provide an end date of the event.
5. Go to `Customize » Widgets`, add the `Simple Events Widget` to the required sidebar.
6. Adjust the widget settings according to you needs.

## Plugin page

You can find the plugin on [Wordpress.org](https://wordpress.org/plugins/smntcs-simple-events-widget/).

## Changelog

### 2.5 (2025.03.25)

- Tested up to WordPress 6.9
- Add widget options for event sort order and line break between date and title
- Use date_i18n for localized date output
- Include WooCommerce products when querying events
- Update Composer dev dependencies; replace phpcs.dist.xml with phpcs.xml; refresh GitHub Actions

### 2.4 (2025.03.23)

- Tested up to WordPress 6.8

### 2.3 (2024.04.07)

- Tested up to WordPress 6.5

### 2.2 (2023.11.12)

- Fix datepicker issue

### 2.1 (2023.11.12)

- Convert to OOP

### 2.0 (2023.10.28)

- Fix i18n issue

### 1.9 (2023.10.21)

- Tested up to WordPress 6.4

### 1.8 (2023.05.28)

- Fix warning when regarding undefined array key

### 1.7 (2023.05.07)

- Only execute plugin on posts and pages
- Remove line break after event list

### 1.6 (2023.05.07)

- Show message when no events are available.
- Tested up to WordPress 6.2

### 1.5 (2022.12.03)

- Tested up to WordPress 6.1

### 1.4 (2022.05.29)

- Tested up to WordPress 6.0

### 1.3 (2022.01.01)

- Tested up to WordPress 5.9

### 1.2 (2019.12.31)

- Tested up to WordPress 5.8

### 1.1 (2019.02.19)

- Tested up to WordPress 5.0

### 1.0 (2018.04.09)

- Initial release
