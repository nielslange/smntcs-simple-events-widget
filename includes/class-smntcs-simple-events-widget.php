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
		$widget_options = [
			'classname'   => 'smntcs_simple_events_widget',
			'description' => 'Display Simple Events Widget',
		];
		parent::__construct( 'smntcs_simple_events_widget', 'Simple Events Widget', $widget_options );
	}

	/**
	 * Create widget.
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		// Event meta is stored as UTC Unix timestamps; compare using the current instant.
		$timestamp = time();

		$sort_order = isset( $instance['sort_order'] ) && 'DESC' === $instance['sort_order'] ? 'DESC' : 'ASC';

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . esc_html( $title ) . $args['after_title'] );
		}

		$query_args = [
			'post_type'      => [ 'post', 'page', 'product' ],
			'meta_key'       => 'datepicker_start',
			'orderby'        => 'meta_value',
			'order'          => $sort_order,
			'meta_type'      => 'NUMERIC',
			'posts_per_page' => -1,
		];

		if ( 'upcoming-events' === $instance['display_events'] ) {
			$query_args['meta_query'] = [
				[
					'key'     => 'datepicker_start',
					'value'   => $timestamp,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				],
			];
		}

		if ( 'previous-events' === $instance['display_events'] ) {
			$query_args['meta_query'] = [
				[
					'key'     => 'datepicker_start',
					'value'   => $timestamp,
					'compare' => '<',
					'type'    => 'NUMERIC',
				],
			];
		}

		$the_query = new WP_Query( $query_args );

		$date_title_separator = ! empty( $instance['date_title_line_break'] ) ? ': <br />' : ': ';

		if ( $the_query->have_posts() ) {
			echo '<ul>';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$start_date_meta = get_post_meta( get_the_ID(), 'datepicker_start', true );
				$start_date      = $start_date_meta ? date_i18n( get_option( 'date_format' ), intval( $start_date_meta ), true ) : __( 'No start date', 'smntcs-simple-events-widget' );
				$end_date_meta   = get_post_meta( get_the_ID(), 'datepicker_end', true );
				$end_date        = $end_date_meta ? date_i18n( get_option( 'date_format' ), intval( $end_date_meta ), true ) : __( 'No end date', 'smntcs-simple-events-widget' );
				$link            = get_permalink();

				if ( 'start-and-end-date' === $instance['display_dates'] ) {
					printf(
						'<li>%s - %s%s<a href="%s">%s</a></li>',
						esc_html( $start_date ),
						esc_html( $end_date ),
						wp_kses_post( $date_title_separator ),
						esc_url( $link ),
						esc_html( get_the_title() )
					);
				} else {
					printf(
						'<li>%s%s<a href="%s">%s</a></li>',
						esc_html( $start_date ),
						wp_kses_post( $date_title_separator ),
						esc_url( $link ),
						esc_html( get_the_title() )
					);
				}
			}
			echo '</ul>';
		} else {
			echo '<p>' . esc_html__( 'No events found.', 'smntcs-simple-events-widget' ) . '</p>';
		}

		wp_reset_postdata();
		echo wp_kses_post( $args['after_widget'] );
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
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sort_order' ) ); ?>"><?php esc_html_e( 'Sort order:', 'smntcs-simple-events-widget' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'sort_order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sort_order' ) ); ?>">
				<option <?php selected( $instance['sort_order'] ?? 'ASC', 'ASC' ); ?> value="ASC"><?php esc_html_e( 'Ascending (earliest first)', 'smntcs-simple-events-widget' ); ?></option>
				<option <?php selected( $instance['sort_order'] ?? 'ASC', 'DESC' ); ?> value="DESC"><?php esc_html_e( 'Descending (latest first)', 'smntcs-simple-events-widget' ); ?></option>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( ! empty( $instance['date_title_line_break'] ) ); ?> id="<?php echo esc_attr( $this->get_field_id( 'date_title_line_break' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'date_title_line_break' ) ); ?>" value="1">
			<label for="<?php echo esc_attr( $this->get_field_id( 'date_title_line_break' ) ); ?>"><?php esc_html_e( 'Line break between date and title', 'smntcs-simple-events-widget' ); ?></label>
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

		$instance['title']          = wp_strip_all_tags( $new_instance['title'] );
		$instance['display_events'] = wp_strip_all_tags( $new_instance['display_events'] );
		$instance['display_dates']  = wp_strip_all_tags( $new_instance['display_dates'] );

		$raw_sort                          = isset( $new_instance['sort_order'] ) ? strtoupper( wp_strip_all_tags( $new_instance['sort_order'] ) ) : 'ASC';
		$instance['sort_order']            = ( 'DESC' === $raw_sort ) ? 'DESC' : 'ASC';
		$instance['date_title_line_break'] = ! empty( $new_instance['date_title_line_break'] ) ? '1' : '';

		return $instance;
	}
}

new SMNTCS_Simple_Events_Widget();
