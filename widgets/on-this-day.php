<?php
/*
 * Plugin File: Widget -- On This Day
 * Widget to display On This Day
 * @since 1.0
 */

class LezWatchTV_On_This_Day_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	public function __construct() {

		$this->defaults = array(
			'title' => __( 'On This Day', 'lezwatchtv' ),
			'date'  => '',
		);

		$widget_ops = array(
			'classname'   => 'dead-on-this-day deadwidget',
			'description' => __( 'Displays any queer female or trans TV character who died on this day in years past.', 'lezwatchtv' ),
		);

		$control_ops = array(
			'id_base' => 'byq-on-this-day',
		);

		parent::__construct( 'byq-on-this-day', __( 'LWTV - On This Day', 'lezwatchtv' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		$date = ( ! empty( $instance['date'] ) ) ? $instance['date'] : 'today';

		echo wp_kses_post( LezWatchTV::died_on_this_day( $date ) );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {
		$new_instance['title'] = wp_strip_all_tags( $new_instance['title'] );

		$new_instance['date'] = substr( $new_instance['date'], 0, 5 );
		$month                = substr( $new_instance['date'], 0, 2 );
		$day                  = substr( $new_instance['date'], 3, 2 );
		if ( checkdate( $month, $day, gmdate( 'Y' ) ) === false ) {
			$new_instance['date'] = '';
		}
		$new_instance['date'] = wp_strip_all_tags( $new_instance['date'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'lezwatchtv' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>"><?php esc_html_e( 'Date (Optional)', 'lezwatchtv' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'date' ) ); ?>" class="datepicker" value="<?php echo esc_attr( $instance['date'] ); ?>" class="widefat" />
			<br><em><?php esc_html_e( 'If left blank, the date used will be the current day.', 'lezwatchtv' ); ?></em>
		</p>
		<?php
	}
}
