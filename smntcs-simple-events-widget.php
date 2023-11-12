<?php
/**
 * Plugin Name:           SMNTCS Simple Events Widget
 * Plugin URI:            https://github.com/nielslange/smntcs-simple-events-widget
 * Description:           Add meta box to posts and pages, to select event start and end date and show upcoming and previous events as sidebar widget.
 * Author:                Niels Lange
 * Author URI:            https://nielslange.de
 * Text Domain:           smntcs-simple-events-widget
 * Version:               2.1
 * Requires PHP:          5.6
 * Requires at least:     3.4
 * License:               GPL v2 or later
 * License URI:           https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package SMNTCS_Simple_Events_Widget
 */

defined( 'ABSPATH' ) || exit;

// Include the main class file.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-smntcs-simple-events.php';

// Include the widget class file.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-smntcs-simple-events-widget.php';
