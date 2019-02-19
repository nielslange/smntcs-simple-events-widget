<?php
/**
 * Plugin Name: SMNTCS Simple Events Widget
 * Plugin URI: https://github.com/nielslange/smntcs-simple-events-widget
 * Description: Add meta box to posts and pages, to select event start and end date and show upcoming (and previous) events as sidebar widget
 * Author: Niels Lange
 * Author URI: https://nielslange.com
 * Text Domain: smntcs-simple-events-widget
 * Domain Path: /languages/
 * Version: 1.1
 * Requires at least: 3.4
 * Tested up to: 5.0
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/* Copyright 2014-2016 Niels Lange (email : info@nielslange.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//* Avoid direct plugin access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//* Load text domain
add_action( 'plugins_loaded', 'smntcs_sew_plugins_loaded' );
function smntcs_sew_plugins_loaded() {
	load_plugin_textdomain( 'smntcs-simple-events-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

// Add settings link on plugin page
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'smntcs_sew_plugin_settings_link' );
function smntcs_sew_plugin_settings_link( $links ) {
	$admin_url    = admin_url( 'widgets.php' );
	$settings_url = sprintf( '<a href="%s">%s</a>', $admin_url, __( 'Settings', 'smntcs-simple-events-widget' ) );
	array_unshift( $links, $settings_url );

	return $links;
}

//* Load jQuery datepicker
add_action( 'admin_enqueue_scripts', 'smntcs_sew_admin_enqueue_scripts' );
function smntcs_sew_admin_enqueue_scripts() {
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'smntcs-sew-script', plugin_dir_url( __FILE__ ) . '/js/custom.js', array( 'jquery' ) );
	wp_enqueue_style( 'smntcs-sew-admin-ui-css', plugin_dir_url( __FILE__ ) . '/js/jquery-ui.css' );
}

//* Register meta box(es).
add_action( 'add_meta_boxes', 'smntcs_sew_add_meta_boxes' );
function smntcs_sew_add_meta_boxes() {
	add_meta_box( 'meta-box-id', __( 'Event', 'smntcs-simple-events-widget' ), 'smntcs_sew_display_callback', 'post', 'side' );
	add_meta_box( 'meta-box-id', __( 'Event', 'smntcs-simple-events-widget' ), 'smntcs_sew_display_callback', 'page', 'side' );
}

//* Convert date (01-01-2017) to timestamp (1483228800)
function smntcs_sew_convert_date_to_timestamp( $date ) {
	return DateTime::createFromFormat( 'd-m-Y', $date, new DateTimeZone( 'UTC' ) )->getTimestamp();
}

//* Convert timestamp (1483228800) to date (01-01-2017)
function smntcs_sew_convert_timestamp_to_date( $timestamp ) {
	return date( 'd-m-Y', $timestamp );
}

//* Meta box display callback.
function smntcs_sew_display_callback( $post ) {
	$t_start = get_post_meta( $post->ID, 'datepicker_start', true );
	$v_start = ! empty( $t_start ) ? smntcs_sew_convert_timestamp_to_date( $t_start ) : null;
	$p_start = ! empty( $t_start ) ? smntcs_sew_convert_timestamp_to_date( $t_start ) : 'dd-mm-yyyy';
	$t_end   = get_post_meta( $post->ID, 'datepicker_end', true );
	$v_end   = ! empty( $t_end ) ? smntcs_sew_convert_timestamp_to_date( $t_end ) : null;
	$p_end   = ! empty( $t_end ) ? smntcs_sew_convert_timestamp_to_date( $t_end ) : 'dd-mm-yyyy';
	?>
    <table>
        <tr class="wrap">
            <td><?php echo __( 'Start date', 'smntcs-simple-events-widget' ); ?></td>
            <td><input type="text" class="datepicker" name="datepicker_start" value="<?php echo $v_start; ?>" placeholder="<?php echo $p_start; ?>"></td>
        </tr>
        <tr class="wrap">
            <td><?php echo __( 'End date', 'smntcs-simple-events-widget' ); ?></td>
            <td><input type="text" class="datepicker" name="datepicker_end" value="<?php echo $v_end; ?>" placeholder="<?php echo $p_end; ?>"></td>
        </tr>
    </table>
	<?php
}

//* Save meta box content
add_action( 'save_post', 'smntcs_sew_save_post' );
function smntcs_sew_save_post( $post_id ) {

	//* Return if user doen't have necessary permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	//* Return if start date is bigger then end date
	if ( ! empty( $_POST['datepicker_start'] ) && ! empty( $_POST['datepicker_end'] ) ) {
		if ( smntcs_sew_convert_date_to_timestamp( $_POST['datepicker_start'] ) > smntcs_sew_convert_date_to_timestamp( $_POST['datepicker_end'] ) ) {
			return;
		}
	}

	//* Return if only end date is available
	if ( empty( $_POST['datepicker_start'] ) && ! empty( $_POST['datepicker_end'] ) ) {
		return;
	}

	//* Convert, sanitize and save start date (or empty start date)
	if ( isset( $_POST['datepicker_start'] ) ) {
		if ( ! empty( $_POST['datepicker_start'] ) ) {
			$t_start = smntcs_sew_convert_date_to_timestamp( $_POST['datepicker_start'] );
			update_post_meta( $post_id, 'datepicker_start', sanitize_text_field( $t_start ) );
		} else {
			update_post_meta( $post_id, 'datepicker_start', null );
		}
	}

	//* Convert, sanitize and save end date (or empty end date)
	if ( isset( $_POST['datepicker_end'] ) ) {
		if ( ! empty( $_POST['datepicker_end'] ) ) {
			$t_end = smntcs_sew_convert_date_to_timestamp( $_POST['datepicker_end'] );
			update_post_meta( $post_id, 'datepicker_end', sanitize_text_field( $t_end ) );
		} else {
			update_post_meta( $post_id, 'datepicker_end', null );
		}
	}
}

class SMNTCS_Simple_Events_Widget extends WP_Widget {
	//* Construct widget
	public function __construct() {
		$widget_options = array(
			'classname'   => 'smntcs_simple_events_widget',
			'description' => 'Display Simple Events Widget',
		);
		parent::__construct( 'smntcs_simple_events_widget', 'Simple Events Widget', $widget_options );
	}

	// Create widget
	public function widget( $args, $instance ) {
		$title     = apply_filters( 'widget_title', $instance['title'] );
		$timestamp = strtotime( date( 'd' ) . ' ' . date( 'F' ) . ' ' . date( 'Y' ) );
		$temp      = $args;

		print( $args['before_widget'] . $args['before_title'] . $title . $args['after_title'] );

		$args = array(
			'post_type' => 'post',
			'meta_key'  => 'datepicker_start',
			'order'     => 'asc',
			'orderby'   => 'datepicker_start',
		);

		if ( $instance['display_events'] == 'upcoming-events' ) {
			$args['meta_value']   = $timestamp;
			$args['meta_compare'] = '>';
		} elseif ( $instance['display_events'] == 'previous-events' ) {
			$args['meta_value']   = $timestamp;
			$args['meta_compare'] = '<';
		}

		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			print( '<ul>' );
			foreach ( $the_query->get_posts() as $event ) {
				$start_date = date( get_option( 'date_format' ), get_post_meta( $event->ID, 'datepicker_start', true ) );
				$end_date   = date( get_option( 'date_format' ), get_post_meta( $event->ID, 'datepicker_end', true ) );
				$link       = get_permalink( $event->ID );
				$title      = $event->post_title;

				if ( $instance['display_dates'] == 'start-and-end-date' ) {
					printf( '<li>%s - %s: <a href="%s">%s</a></li>', $start_date, $end_date, $link, $title );
				} else {
					printf( '<li>%s: <a href="%s">%s</a></li>', $start_date, $link, $title );
				}
			}
			print( '</ul><br>' );
		}

		$args = $temp;
		print( $args['after_widget'] );
	}

	// Create form
	public function form( $instance ) {
		$title          = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$display_events = ! empty( $instance['display_events'] ) ? $instance['display_events'] : '';
		$display_dates  = ! empty( $instance['display_dates'] ) ? $instance['display_dates'] : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'smntcs-simple-events-widget' ) ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" type="text">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'display_events' ); ?>"><?php _e( 'Display events:', 'smntcs-simple-events-widget' ) ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'display_events' ); ?>" name="<?php echo $this->get_field_name( 'display_events' ); ?>">
                <option <?php isset ($instance['display_events']) ? selected( $instance['display_events'], 'upcoming-events' ) : ''; ?> value="upcoming-events"><?php _e( 'Only upcoming events', 'smntcs-simple-events-widget' ) ?></option>
                <option <?php isset ($instance['display_events']) ? selected( $instance['display_events'], 'previous-events' ) : ''; ?> value="previous-events"><?php _e( 'Only previous events', 'smntcs-simple-events-widget' ) ?></option>
                <option <?php isset ($instance['display_events']) ? selected( $instance['display_events'], 'upcoming-and-previous-events' ) : ''; ?>value="upcoming-and-previous-events"><?php _e( 'Upcoming and previous events', 'smntcs-simple-events-widget' ) ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'display_dates' ); ?>"><?php _e( 'Display dates:', 'smntcs-simple-events-widget' ) ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'display_dates' ); ?>" name="<?php echo $this->get_field_name( 'display_dates' ); ?>">
                <option <?php isset ($instance['display_dates']) ? selected( $instance['display_dates'], 'start-date' ) : ''; ?> value="start-date"><?php _e( 'Only start date', 'smntcs-simple-events-widget' ) ?></option>
                <option <?php isset ($instance['display_dates']) ? selected( $instance['display_dates'], 'start-and-end-date' ) : ''; ?>value="start-and-end-date"><?php _e( 'Start and end date', 'smntcs-simple-events-widget' ) ?></option>
            </select>
        </p>
		<?php
	}

	// Update widget
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['display_events'] = strip_tags( $new_instance['display_events'] );
		$instance['display_dates']  = strip_tags( $new_instance['display_dates'] );

		return $instance;
	}
}

//* Register widget
add_action( 'widgets_init', 'smntcs_sew_widgets_init' );
function smntcs_sew_widgets_init() {
	register_widget( 'SMNTCS_Simple_Events_Widget' );
}