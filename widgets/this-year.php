<?php
/*
 * Plugin File: Widgets -- This Year
 * Widget to display This Year data
 * @since 1.3.0
 */

class LWTV_This_Year_Widget extends WP_Widget {

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
			'title' => __( 'In This Year', 'bury-your-queers' ),
			'year'  => date( 'Y' ),
		);

		$widget_ops = array(
			'classname'   => 'in-this-year thisyearwidget',
			'description' => __( 'Displays a review of queer female and trans representation on TV for a given year.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-in-this-year',
		);

		parent::__construct( 'byq-in-this-year', __( 'LWTV - In This Year', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$year = ( ! empty( $instance['year'] ) )? $instance['year'] : date( 'Y' ) ;

		echo LezWatch_TV::this_year( $year );

		echo $args['after_widget'];
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
		$new_instance['year'] = ( preg_match( '/^[0-9]{4}$/', $new_instance['year'] ) )? $new_instance['year'] : date( 'Y' );
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
		$request  = wp_remote_get( LezWatch_TV::$apiurl . '/stats/first-year/' );
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>"><?php _e( 'Year', 'bury-your-queers' ); ?>: </label>
			<select id="<?php echo $this->get_field_id( 'year' ); ?>" name="<?php echo $this->get_field_name( 'year' ); ?>" class="widefat" style="width:100%;">
				<?php for( $year = $response['first']; $year <= date( 'Y' ); ++$year ) {
					echo '<option ' . selected( $instance[ 'year' ], $year ) .' value="' . $year . '">' . $year . '</option>';
				} ?>
			</select>
		</p>
		<?php
	}
}
