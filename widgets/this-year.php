<?php
/*
 * Plugin File: Widgets -- This Year
 * Widget to display This Year data
 * @since 1.0
 */

class LezWatchTV_This_Year_Widget extends WP_Widget {

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
			'title' => __( 'In This Year', 'lezwatchtv' ),
			'year'  => gmdate( 'Y' ),
		);

		$widget_ops = array(
			'classname'   => 'in-this-year thisyearwidget',
			'description' => __( 'Displays a review of queer female and trans representation on TV for a given year.', 'lezwatchtv' ),
		);

		$control_ops = array(
			'id_base' => 'byq-in-this-year',
		);

		parent::__construct( 'byq-in-this-year', __( 'LWTV - In This Year', 'lezwatchtv' ), $widget_ops, $control_ops );
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

		$year = ( ! empty( $instance['year'] ) ) ? $instance['year'] : gmdate( 'Y' );

		echo wp_kses_post( LezWatchTV::this_year( $year ) );

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
		$new_instance['year']  = ( preg_match( '/^[0-9]{4}$/', $new_instance['year'] ) ) ? $new_instance['year'] : gmdate( 'Y' );
		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		// Get the first year
		$request  = wp_remote_get( LezWatchTV::$apiurl . '/stats/first-year/' );
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode( $response, true );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'lezwatchtv' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>"><?php esc_html_e( 'Year', 'lezwatchtv' ); ?>: </label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'year' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'year' ) ); ?>" class="widefat" style="width:100%;">
				<?php
				for ( $year = $response['first']; $year <= gmdate( 'Y' ); ++$year ) {
					echo '<option ' . selected( $instance['year'], $year ) . ' value="' . esc_attr( $year ) . '">' . esc_attr( $year ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}
}
