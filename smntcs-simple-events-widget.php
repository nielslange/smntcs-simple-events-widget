<?php
/**
 * Plugin Name:           SMNTCS Simple Events Widget
 * Plugin URI:            https://github.com/nielslange/smntcs-simple-events-widget
 * Description:           Add meta box to posts and pages, to select event start and end date and show upcoming and previous events as sidebar widget.
 * Author:                Niels Lange
 * Author URI:            https://nielslange.de
 * Text Domain:           smntcs-simple-events-widget
 * Version:               1.5
 * Requires PHP:          5.6
 * Requires at least:     3.4
 * License:               GPL v2 or later
 * License URI:           https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package SMNTCS_Simple_Events_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add settings link on plugin page.
 *
 * @param  array $links The original array with links.
 * @return array The updated array with links.
 */
function smntcs_plugin_settings_link( $links ) {
	$admin_url    = admin_url( 'widgets.php' );
	$settings_url = sprintf( '<a href="%s">%s</a>', $admin_url, __( 'Settings', 'smntcs-simple-events-widget' ) );
	array_unshift( $links, $settings_url );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'smntcs_plugin_settings_link' );

/**
 * Load jQuery datepicker.
 *
 * @return void
 */
function smntcs_admin_enqueue_scripts() {
	$plugin_data = get_plugin_data( __FILE__ );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'smntcs-simple-events-script', plugin_dir_url( __FILE__ ) . '/js/custom.js', array( 'jquery' ), $plugin_data['Version'] );
	wp_enqueue_style( 'smntcs-simple-events-styles', plugin_dir_url( __FILE__ ) . '/js/jquery-ui.css', array(), $plugin_data['Version'] );
}
add_action( 'admin_enqueue_scripts', 'smntcs_admin_enqueue_scripts' );

/**
 * Register meta box.
 *
 * @return void
 */
function smntcs_add_meta_boxes() {
	add_meta_box( 'meta-box-id', __( 'Event', 'smntcs-simple-events-widget' ), 'smntcs_display_callback', 'post', 'side' );
	add_meta_box( 'meta-box-id', __( 'Event', 'smntcs-simple-events-widget' ), 'smntcs_display_callback', 'page', 'side' );
}
add_action( 'add_meta_boxes', 'smntcs_add_meta_boxes' );

/**
 * Convert date to timestamp.
 *
 * @param  string $date The date to convert, e.g. 01-01-2017.
 * @return int The converted timestamp, e.g. 1483228800.
 */
function smntcs_date_to_timestamp( $date ) {
	return DateTime::createFromFormat( 'd-m-Y', $date, new DateTimeZone( 'UTC' ) )->getTimestamp();
}

/**
 * Convert timestamp to date.
 *
 * @param string $timestamp The timestamp to convert, e.g. 1483228800.
 * @return string The converted date, e.g. 01-01-2017.
 */
function smntcs_timestamp_to_date( $timestamp ) {
	return gmdate( 'd-m-Y', $timestamp );
}

/**
 * Meta box display callback.
 *
 * @param  WP_Post $post The original post object.
 * @return void
 */
function smntcs_display_callback( $post ) {

	$start_date             = get_post_meta( $post->ID, 'datepicker_start', true );
	$start_date_value       = ! empty( $start_date ) ? smntcs_timestamp_to_date( $start_date ) : null;
	$start_date_placeholder = ! empty( $start_date ) ? smntcs_timestamp_to_date( $start_date ) : 'dd-mm-yyyy';
	$end_date               = get_post_meta( $post->ID, 'datepicker_end', true );
	$end_date_value         = ! empty( $end_date ) ? smntcs_timestamp_to_date( $end_date ) : null;
	$end_date_placeholder   = ! empty( $end_date ) ? smntcs_timestamp_to_date( $end_date ) : 'dd-mm-yyyy';
	wp_nonce_field( 'smntcs_add_simple_event', 'smntcs_wpnonce' );
	?>
	<table>
		<tr class="wrap">
			<td><?php echo esc_html_e( 'Start date', 'smntcs-simple-events-widget' ); ?></td>
			<td><input type="text" class="datepicker" name="datepicker_start" value="<?php echo esc_html( $start_date_value ); ?>" placeholder="<?php echo esc_html( $start_date_placeholder ); ?>"></td>
		</tr>
		<tr class="wrap">
			<td><?php echo esc_html_e( 'End date', 'smntcs-simple-events-widget' ); ?></td>
			<td><input type="text" class="datepicker" name="datepicker_end" value="<?php echo esc_html( $end_date_value ); ?>" placeholder="<?php echo esc_html( $end_date_placeholder ); ?>"></td>
		</tr>
	</table>
	<?php
}

/**
 * Save meta box content.
 *
 * @param int $post_id The ID of the post to save.
 * @return void
 */
function smntcs_save_post( $post_id ) {

	// Return if user does not have necessary permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Return if nonce is incorrect or not available.
	if ( ! isset( $_POST['smntcs_wpnonce'] ) &&
		 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smntcs_wpnonce'] ) ), 'smntcs_add_simple_event' ) ) {
		return;
	}

	// Return if start date and end date are not available.
	if ( ! isset( $_POST['datepicker_start'] ) && ! isset( $_POST['datepicker_end'] ) ) {
		return;
	}

	// Return if only end date is available.
	if ( isset( $_POST['datepicker_start'] ) && ! isset( $_POST['datepicker_end'] ) ) {
		return;
	}

	// Return if start date is bigger then end date.
	if ( '' !== $_POST['datepicker_start'] && '' !== $_POST['datepicker_end'] ) {
		$start_date = smntcs_date_to_timestamp( sanitize_text_field( wp_unslash( $_POST['datepicker_start'] ) ) );
		$end_date   = smntcs_date_to_timestamp( sanitize_text_field( wp_unslash( $_POST['datepicker_end'] ) ) );

		if ( $start_date > $end_date ) {
			return;
		}
	}

	// Convert, sanitize and save start date, if available, otherwise delete post meta.
	if ( '' !== $_POST['datepicker_start'] ) {
		$start_date = smntcs_date_to_timestamp( sanitize_text_field( wp_unslash( $_POST['datepicker_start'] ) ) );
		update_post_meta( $post_id, 'datepicker_start', sanitize_text_field( $start_date ) );
	} else {
		delete_post_meta( $post_id, 'datepicker_start' );
	}

	// Convert, sanitize and save end date, if available, otherwise delete post meta.
	if ( '' !== $_POST['datepicker_end'] ) {
		$end_date = smntcs_date_to_timestamp( sanitize_text_field( wp_unslash( $_POST['datepicker_end'] ) ) );
		update_post_meta( $post_id, 'datepicker_end', sanitize_text_field( $end_date ) );
	} else {
		delete_post_meta( $post_id, 'datepicker_end' );
	}
}
add_action( 'save_post', 'smntcs_save_post' );

/**
 * Class SMNTCS_Simple_Events_Widget
 *
 * @extends WP_Widget
 */
class SMNTCS_Simple_Events_Widget extends WP_Widget {
	/**
	 * SMNTCS_Simple_Events_Widget constructor.
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'smntcs_simple_events_widget',
			'description' => 'Display Simple Events Widget',
		);
		parent::__construct( 'smntcs_simple_events_widget', 'Simple Events Widget', $widget_options );
	}

	/**
	 * Create widget.
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {

		$title     = apply_filters( 'widget_title', $instance['title'] );
		$timestamp = strtotime( gmdate( 'd' ) . ' ' . gmdate( 'F' ) . ' ' . gmdate( 'Y' ) );
		$temp      = $args;

		printf(
			'%s %s %s %s',
			$args['before_widget'], // phpcs:ignore.
			$args['before_title'],  // phpcs:ignore.
			esc_attr( $title ),
			$args['after_title']    // phpcs:ignore.
		);

		$args = array(
			'post_type' => array( 'post', 'page' ),
			'meta_key'  => 'datepicker_start',
			'order'     => 'asc',
			'orderby'   => 'datepicker_start',
		);

		if ( 'upcoming-events' === $instance['display_events'] ) {
			$args['meta_value']   = $timestamp;
			$args['meta_compare'] = '>';
		}

		if ( 'previous-events' === $instance['display_events'] ) {
			$args['meta_value']   = $timestamp;
			$args['meta_compare'] = '<';
		}

		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			print( '<ul>' );
			foreach ( $the_query->get_posts() as $event ) {
				$start_date = gmdate( get_option( 'date_format' ), get_post_meta( $event->ID, 'datepicker_start', true ) );
				$end_date   = gmdate( get_option( 'date_format' ), get_post_meta( $event->ID, 'datepicker_end', true ) );
				$link       = get_permalink( $event->ID );
				$title      = $event->post_title;

				if ( 'start-and-end-date' === $instance['display_dates'] ) {
					printf(
						'<li>%s - %s: <a href="%s">%s</a></li>',
						esc_attr( $start_date ),
						esc_attr( $end_date ),
						esc_html( $link ),
						esc_attr( $title )
					);
				} else {
					printf(
						'<li>%s: <a href="%s">%s</a></li>',
						esc_attr( $start_date ),
						esc_html( $link ),
						esc_attr( $title )
					);
				}
			}
			print( '</ul><br>' );
		}

		$args = $temp;

		printf(
			'%s',
			$args['after_widget'] // phpcs:ignore.
		);
	}

	/**
	 * Create form.
	 *
	 * @param array $instance The original widget instance.
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$title = $instance['title'] ?? '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'smntcs-simple-events-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" type="text">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_events' ) ); ?>"><?php esc_html_e( 'Display events:', 'smntcs-simple-events-widget' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_events' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_events' ) ); ?>">
				<option <?php isset( $instance['display_events'] ) ? selected( $instance['display_events'], 'upcoming-events' ) : ''; ?> value="upcoming-events"><?php esc_html_e( 'Only upcoming events', 'smntcs-simple-events-widget' ); ?></option>
				<option <?php isset( $instance['display_events'] ) ? selected( $instance['display_events'], 'previous-events' ) : ''; ?> value="previous-events"><?php esc_html_e( 'Only previous events', 'smntcs-simple-events-widget' ); ?></option>
				<option <?php isset( $instance['display_events'] ) ? selected( $instance['display_events'], 'upcoming-and-previous-events' ) : ''; ?>value="upcoming-and-previous-events"><?php esc_html_e( 'Upcoming and previous events', 'smntcs-simple-events-widget' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_dates' ) ); ?>"><?php esc_html_e( 'Display dates:', 'smntcs-simple-events-widget' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_dates' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_dates' ) ); ?>">
				<option <?php isset( $instance['display_dates'] ) ? selected( $instance['display_dates'], 'start-date' ) : ''; ?> value="start-date"><?php esc_html_e( 'Only start date', 'smntcs-simple-events-widget' ); ?></option>
				<option <?php isset( $instance['display_dates'] ) ? selected( $instance['display_dates'], 'start-and-end-date' ) : ''; ?>value="start-and-end-date"><?php esc_html_e( 'Start and end date', 'smntcs-simple-events-widget' ); ?></option>
			</select>
		</p>
		<?php
	}

	/**
	 * Update widget.
	 *
	 * @param array $new_instance The new array of the widget instance.
	 * @param array $old_instance The old array of the widget instance.
	 *
	 * @return array The updated array of the widget instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['display_events'] = strip_tags( $new_instance['display_events'] );
		$instance['display_dates']  = strip_tags( $new_instance['display_dates'] );

		return $instance;
	}
}

/**
 * Register widget.
 *
 * @return void
 */
function smntcs_widgets_init() {
	register_widget( 'SMNTCS_Simple_Events_Widget' );
}
add_action( 'widgets_init', 'smntcs_widgets_init' );
