<?php
/*
 * Plugin File: Widgets -- Statistics
 * Widget to display death statistics
 * @since 1.0
 */

class LezWatchTV_Statistics_Widget extends WP_Widget {

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
			'title' => __( 'Queer Fatality Statistics', 'lezwatchtv' ),
			'type'  => '',
		);

		$widget_ops = array(
			'classname'   => 'dead-stats deadwidget',
			'description' => __( 'Displays the percentage of how many queer female or trans TV characters who have died, and/or how many shows have death.', 'lezwatchtv' ),
		);

		$control_ops = array(
			'id_base' => 'byq-statistics',
		);

		parent::__construct( 'byq-statistics', __( 'LWTV - Statistics', 'lezwatchtv' ), $widget_ops, $control_ops );
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

		$type = ( ! empty( $instance['type'] ) ) ? $instance['type'] : 'all';

		echo wp_kses_post( LezWatchTV::statistics( $type ) );

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
		$new_instance['type']  = wp_strip_all_tags( $new_instance['type'] );
		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$stat_types = array( 'characters', 'shows' );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'lezwatchtv' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Type', 'lezwatchtv' ); ?>: </label>

			<select id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>" class="widefat" style="width:100%;">
				<option value="" selected>All</option>
				<?php
				foreach ( $stat_types as $type ) {
					echo '<option ' . selected( $instance['type'], $type ) . 'value="' . esc_attr( $type ) . '">' . esc_html( ucfirst( $type ) ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}
}
