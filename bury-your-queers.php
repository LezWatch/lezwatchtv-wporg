<?php
/**

Plugin Name: Bury Your Queers
Plugin URI: http://lezwatchtv.com/cliche/dead/
Description: Show the last dead queer female from LezWatchTV in a widget.
Version: 1.0
Author: Mika Epstein
Author URI: http://halfelf.org/
*/

class Bury_Your_Queers {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'last_death_register_widget' ) );
	}

	/**
	 * Init
	 */
	public function init() {
		add_shortcode( 'last-death', array( $this, 'last_death_shortcode') );
	}

	/**
	 * Shortcode Of Last Death
	 */
	public function last_death_shortcode() {
		echo $this->last_death();
	}

	/**
	 * Shortcode Of Last Death
	 */
	public function last_death_register_widget() {
		$this->widget = new BYQ_Last_Death_Widget();
		register_widget( $this->widget );
	}

	/**
	 * The code that genrates the last death
	 */
	public static function last_death() {
		$request  = wp_remote_get( 'https://lezwatchtv.com/wp-json/lwtv/v1/last-death/' );
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);

		$diff = $response['since'];

		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

		$since = '';
		if ( $years != 0 ) $since .= sprintf( _n( '%s year, ', '%s years, ', $years, 'bury-your-queers' ), $years );
		if ( $months != 0 ) $since .= sprintf( _n( '%s month', '%s months', $months, 'bury-your-queers' ), $months );
		$since .= ( $years != 0 )? ', ' : ' ';
		$since .= ( $months != 0 )? __('and ', 'bury-your-queers') : '';
		if ( $days != 0 ) $since .= sprintf( _n( '%s day', '%s days', $days, 'bury-your-queers' ), $days );

		$response['since'] = $since;

		return $response;
	}
}
new Bury_Your_Queers();

class BYQ_Last_Death_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title'		=> __( 'The Most Recent Death', 'bury-your-queers' ),
		);

		$widget_ops = array(
			'classname'   => 'dead-character deadwidget',
			'description' => __( 'Displays time since the last WLW death', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'lezwatch-dead-char',
		);

		parent::__construct( 'lezwatch-dead-char', __( 'The Latest Dead', 'bury-your-queers' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$dead_character = Bury_Your_Queers::last_death();

		echo sprintf( __('It has been %s since the last queer female death on television', 'bury-your-queers'), '<strong>'.$dead_character['since'].'</strong>' );
		echo ': <a href="'.$dead_character['url'].'">'.$dead_character['name'].'</a> - '.date('F j, Y', $dead_character['died'] );

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'bury-your-queers' ); ?>: </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<?php

	}
}