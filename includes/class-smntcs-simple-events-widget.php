<?php
/**
 * SMNTCS Simple Events Widget - Widget Class
 *
 * Contains the widget class for the Simple Events functionality.
 *
 * @package SMNTCS_Simple_Events_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget class.
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
			print( '</ul>' );
		} else {
			print( '<p>' . esc_html__( 'No events found.', 'smntcs-simple-events-widget' ) . '</p>' );
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

new SMNTCS_Simple_Events_Widget();
