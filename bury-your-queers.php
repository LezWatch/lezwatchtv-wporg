<?php
/**
 Plugin Name: Bury Your Queers
 Plugin URI: http://lezwatchtv.com/cliche/dead/
 Description: Show solidarity with fictional dead female queers.
 Version: 1.0
 Author: Mika Epstein
 Author URI: http://halfelf.org/
*/

/* 
 * class Bury_Your_Queers
 *
 * Main class for plugin
 *
 * @since 1.0
 */
class Bury_Your_Queers {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'last_death_register_widget' ) );
		add_action( 'widgets_init', array( $this, 'on_this_day_register_widget' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init
	 */
	public function init() {
		add_shortcode( 'last-death', array( $this, 'last_death_shortcode') );
		add_shortcode( 'on-this-day', array( $this, 'on_this_day_shortcode') );
	}

	/**
	 * Shortcode Of Last Death
	 */
	public function last_death_shortcode( $atts ) {
		$dead_character = $this->last_death();

		$return = '<p>'.sprintf( __('It has been %s since the last queer female death on television', 'bury-your-queers'), '<strong>'.$dead_character['since'].'</strong>' );
		$return .= ': <a href="'.$dead_character['url'].'">'.$dead_character['name'].'</a> - '.date('F j, Y', $dead_character['died'] ).'</p>';
		
		return $return;
	}

	/**
	 * Shortcode Of On This Day
	 */
	public function on_this_day_shortcode( $atts = [] ) {
		$attributes = shortcode_atts([
			'date' => 'today',
		], $atts);
 
		$onthisday = $this->on_this_day( $attributes['date'] );	
			
		return $onthisday;
	}

	/**
	 * Widget Of Last Death
	 */
	public function last_death_register_widget() {
		$this->widget = new BYQ_Last_Death_Widget();
		register_widget( $this->widget );
	}

	/**
	 * Widget Of On This Day
	 */
	public function on_this_day_register_widget() {
		$this->widget = new BYQ_On_This_Day_Widget();
		register_widget( $this->widget );
	}

	/**
	 * The code that generates the last death
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

	/**
	 * The code that generates the On This Day code
	 */
	public static function on_this_day( $this_day = '03-03' ) {
		
		$this_day = ( $this_day == 'today' )? '' : $this_day.'/' ;
		
		$request  = wp_remote_get( 'https://lezwatchtv.com/wp-json/lwtv/v1/on-this-day/'.$this_day );
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);

		$count = ( key($response) == 'none' )? 0 : count($response) ;
		$how_many = __('no characters died!', 'bury-your-queers');
		$the_dead = '';
		
		if ( $count > 0 ) {
			$how_many = sprintf( _n( '%s character died:', '%s queer female characters died:', $count, 'bury-your-queers' ), $count );
			
			$the_dead = '<ul class="byq-otd">';
			
			foreach ( $response as $dead_character ) {
				$the_dead .= '<li><a href="'.$dead_character['url'].'">'.$dead_character['name'].'</a> - '.$dead_character['died'] .'</li>';
			}
			$the_dead .= '</ul>';
		}

		$onthisday = '<p>'. sprintf( __( 'On %s, %s', 'bury-your-queers'), date('M jS'), $how_many ).'</p>';
		$return = $onthisday.$the_dead;

		return $return;
	}

}
new Bury_Your_Queers();

/* 
 * class BYQ_Last_Death_Widget
 *
 * Widget to display last queer death
 *
 * @since 1.0
 */
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
			'title'		=> __( 'Last Queer Death', 'bury-your-queers' ),
		);

		$widget_ops = array(
			'classname'   => 'dead-character deadwidget',
			'description' => __( 'Displays time since the last WLW death', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-dead-char',
		);

		parent::__construct( 'byq-dead-char', __( 'Bury Your Queers - Last Death', 'bury-your-queers' ), $widget_ops, $control_ops );
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

/* 
 * class BYQ_On_This_Day_Widget
 *
 * Widget to display On This Day...
 *
 * @since 1.0
 */
class BYQ_On_This_Day_Widget extends WP_Widget {

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
			'title'		=> __( 'Bury Your Queers - On This Day', 'bury-your-queers' ),
		);

		$widget_ops = array(
			'classname'   => 'dead-on-this-day deadwidget',
			'description' => __( 'Displays any queers who died on this day in years past.', 'bury-your-queers' ),
		);

		$control_ops = array(
			'id_base' => 'byq-dead-char',
		);

		parent::__construct( 'byq-dead-char', __( 'Bury Your Queers - On This Day', 'bury-your-queers' ), $widget_ops, $control_ops );
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
		
		echo Bury_Your_Queers::on_this_day();

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