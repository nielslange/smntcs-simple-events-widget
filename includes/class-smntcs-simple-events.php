<?php
/**
 * SMNTCS Simple Events Plugin - Main Class
 *
 * Contains the main functionality for the Simple Events Plugin.
 *
 * @package SMNTCS_Simple_Events_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class.
 */
class SMNTCS_Simple_Events {

	/**
	 * SMNTCS_Simple_Events constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SMNTCS_SIMPLE_EVENTS_WIDGET_PLUGIN_FILE ), array( $this, 'plugin_settings_link' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'smntcs-simple-events-widget', false, basename( dirname( SMNTCS_SIMPLE_EVENTS_WIDGET_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Add settings link on plugin page.
	 *
	 * @param  array $links The original array with links.
	 * @return array The updated array with links.
	 */
	public function plugin_settings_link( $links ) {
		$admin_url    = admin_url( 'widgets.php' );
		$settings_url = sprintf( '<a href="%s">%s</a>', $admin_url, __( 'Settings', 'smntcs-simple-events-widget' ) );
		array_unshift( $links, $settings_url );
		return $links;
	}

	/**
	 * Load jQuery datepicker.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$plugin_data = get_plugin_data( SMNTCS_SIMPLE_EVENTS_WIDGET_PLUGIN_FILE );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'smntcs-simple-events-script', plugin_dir_url( SMNTCS_SIMPLE_EVENTS_WIDGET_PLUGIN_FILE ) . 'js/custom.js', array( 'jquery' ), $plugin_data['Version'] );
		wp_enqueue_style( 'smntcs-simple-events-styles', plugin_dir_url( SMNTCS_SIMPLE_EVENTS_WIDGET_PLUGIN_FILE ) . '/js/jquery-ui.css', array(), $plugin_data['Version'] );
	}

	/**
	 * Register meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box( 'meta-box-id', __( 'Event', 'smntcs-simple-events-widget' ), array( $this, 'display_callback' ), 'post', 'side' );
		add_meta_box( 'meta-box-id', __( 'Event', 'smntcs-simple-events-widget' ), array( $this, 'display_callback' ), 'page', 'side' );
	}

	/**
	 * Meta box display callback.
	 *
	 * @param  WP_Post $post The original post object.
	 * @return void
	 */
	public function display_callback( $post ) {
		$start_date             = get_post_meta( $post->ID, 'datepicker_start', true );
		$start_date_value       = ! empty( $start_date ) ? self::timestamp_to_date( $start_date ) : null;
		$start_date_placeholder = ! empty( $start_date ) ? self::timestamp_to_date( $start_date ) : 'dd-mm-yyyy';
		$end_date               = get_post_meta( $post->ID, 'datepicker_end', true );
		$end_date_value         = ! empty( $end_date ) ? self::timestamp_to_date( $end_date ) : null;
		$end_date_placeholder   = ! empty( $end_date ) ? self::timestamp_to_date( $end_date ) : 'dd-mm-yyyy';
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
	public function save_post( $post_id ) {
		// Check for nonce.
		if ( ! isset( $_POST['smntcs_wpnonce'] ) ||
			 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smntcs_wpnonce'] ) ), 'smntcs_add_simple_event' ) ) {
			return;
		}

		// Check user permission.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check for autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save or delete start date.
		if ( isset( $_POST['datepicker_start'] ) && ! empty( $_POST['datepicker_start'] ) ) {
			$start_date = self::date_to_timestamp( sanitize_text_field( wp_unslash( $_POST['datepicker_start'] ) ) );
			update_post_meta( $post_id, 'datepicker_start', $start_date );
		} else {
			delete_post_meta( $post_id, 'datepicker_start' );
		}

		// Save or delete end date.
		if ( isset( $_POST['datepicker_end'] ) && ! empty( $_POST['datepicker_end'] ) ) {
			$end_date = self::date_to_timestamp( sanitize_text_field( wp_unslash( $_POST['datepicker_end'] ) ) );
			update_post_meta( $post_id, 'datepicker_end', $end_date );
		} else {
			delete_post_meta( $post_id, 'datepicker_end' );
		}
	}

	/**
	 * Register widget.
	 *
	 * @return void
	 */
	public function widgets_init() {
		register_widget( 'SMNTCS_Simple_Events_Widget' );
	}

	/**
	 * Convert date to timestamp.
	 *
	 * @param  string $date The date to convert, e.g. 01-01-2017.
	 * @return int The converted timestamp, e.g. 1483228800.
	 */
	public static function date_to_timestamp( $date ) {
		return DateTime::createFromFormat( 'd-m-Y', $date, new DateTimeZone( 'UTC' ) )->getTimestamp();
	}

	/**
	 * Convert timestamp to date.
	 *
	 * @param string $timestamp The timestamp to convert, e.g. 1483228800.
	 * @return string The converted date, e.g. 01-01-2017.
	 */
	public static function timestamp_to_date( $timestamp ) {
		return gmdate( 'd-m-Y', $timestamp );
	}
}

new SMNTCS_Simple_Events();
