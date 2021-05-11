<?php
/*
 * Plugin File: Widget -- Of The Day
 * Widget to display ... Of The Day
 * @since 1.0
 */

class LezWatchTV_Of_The_Day_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 */
	protected $defaults;
	protected $valid_types;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	public function __construct() {

		$this->defaults    = array(
			'title' => __( 'Of The Day', 'lezwatchtv' ),
			'type'  => 'character',
		);
		$this->valid_types = array( 'character', 'show', 'death' );

		$widget_ops = array(
			'classname'   => 'widget-lwtv-of-the-day',
			'description' => __( 'Displays the character, show, or death of the day.', 'lezwatchtv' ),
		);

		$control_ops = array(
			'id_base' => 'lwtv-of-the-day',
		);

		parent::__construct( 'lwtv-of-the-day', __( 'LWTV - Of The Day', 'lezwatchtv' ), $widget_ops, $control_ops );
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

		$type = ( ! empty( $instance['type'] ) ) ? $instance['type'] : 'character';

		echo '<center>' . wp_kses_post( LezWatchTV::of_the_day( $type ) ) . '</center>';

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

		if ( ! in_array( $new_instance['type'], $this->valid_types ) ) {
			$new_instance['type'] = 'character';
		}
		$new_instance['type'] = sanitize_html_class( $new_instance['type'], 'character' );

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
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Type', 'lezwatchtv' ); ?>: </label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>" class="widefat">
				<?php
				foreach ( $this->valid_types as $type ) {
					echo '<option ' . selected( $instance['type'], $type ) . 'value="' . esc_attr( $type ) . '">' . esc_html( ucfirst( $type ) ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}
}
