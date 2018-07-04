<?php
/**
 Plugin Name: LezWatch.TV News & Information
 Plugin URI: https://lezwatchtv.com/about/resources/
 Description: Display information on queer female and trans representation on TV. Brought to you by LezWatch.TV.
 Version: 1.4.0
 Author: LezWatch.TV
 Author URI: https://lezwatchtv.com/
 License: GPLv2 (or Later)

	Copyright 2017-18 LezWatch.TV (email: webmaster@lezwatchtv.com)

	This file is part of LezWatch.TV, a plugin for WordPress.

	LezWatch.TV is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	LezWatch.TV is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * class LezWatch.TV
 *
 * Main class for plugin
 *
 * @since 1.0
 */
class LezWatch_TV {

	protected static $version;
	public static $apiurl;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		self::$version = '1.4.0';
		self::$apiurl  = 'https://lezwatchtv.com/wp-json/lwtv/v1';

		if ( WP_DEBUG ) self::$apiurl  = home_url() . '/wp-json/lwtv/v1';
	}

	/**
	 * Init
	 */
	public function init() {
		add_shortcode( 'lez-watch', array( $this, 'shortcode' ) );
		add_filter( 'plugin_row_meta', array( $this, 'donate_link' ), 10, 2 );
	}

	/**
	 * Admin Scripts
	 */
	public function admin_enqueue_scripts($hook) {
		if( $hook !== 'widgets.php' ) return;
		wp_enqueue_script( 'lwtv-onthisday', plugins_url( 'js/otd-datepicker.js', __FILE__ ), array( 'jquery-ui-datepicker' ), self::$version, true );
		wp_enqueue_style( 'jquery-ui', plugins_url( 'css/jquery-ui.css', __FILE__ ), array(), self::$version );
	}

	/**
	 * Shortcode
	 */
	public function shortcode( $atts ) {
		$attributes = shortcode_atts([
			'data'        => 'of-the-day',
			'date-format' => 'today',
			'stat-type'   => 'all',
			'otd-type'    => 'character',
		], $atts);

		$this_day = sanitize_text_field( $attributes[ 'date-format' ] );
		$stat_fmt = sanitize_text_field( $attributes[ 'stat-type' ] );
		$otd_type = sanitize_text_field( $attributes[ 'otd-type' ] );

		switch ( $attributes[ 'data' ] ) {
			case 'last-death':
				$return = $this->last_death();
				break;
			case 'of-the-day':
				$return = $this->of_the_day( $otd_type );
				break;
			case 'on-this-day':
			case 'died-on-this-day':
				$return = $this->died_on_this_day( $this_day );
				break;
			case 'stats':
				$return = $this->statistics( $stat_fmt );
				break;
			case 'this-year':
				$return = $this->this_year( $this_day );
				break;
			default: 
				$return = '';
		}

		return $return;
	}

	/**
	 * Register Widgets
	 */
	public function register_widgets() {
		
		$widgets = array( 'LWTV_Last_Death_Widget', 'LWTV_Of_The_Day_Widget', 'LWTV_On_This_Day_Widget', 'LWTV_Statistics_Widget', 'LWTV_This_Year_Widget' );
		
		foreach ( $widgets as $widget ) {
			$this->widget = new $widget();
			register_widget( $this->widget );
		}
	}

	/**
	 * The Last Death
	 * Code that generates the last death
	 */
	public static function last_death() {
		
		$request  = wp_remote_get( self::$apiurl . '/last-death/' );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) { 
			return __( '<p>LezWatch.TV is temporarily offline, but will return soon.</p>', 'bury-your-queers' ); 
		}
		
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);
		$return   = '<p>' . sprintf( __( 'It has been %s since the last queer female death on television', 'bury-your-queers' ), '<strong>' . human_time_diff( $response['died'], current_time( 'timestamp' ) ) .'</strong> ' );
		$return  .= ': <a href="' . $response['url'] . '">' . $response['name'] . '</a> - ' . date('F j, Y', $response['died'] ) . '</p>';

		return $return;
	}

	/**
	 * Of The Day
	 * Code that generates the Of The Day code
	 */
	public static function of_the_day( $type = 'character' ) {

		// Quick Failsafe
		$valid_types = array( 'character', 'show', 'death', 'birthday' );
		if ( !in_array( $type, $valid_types ) ) {
			$type = 'character';
		}

		$request  = wp_remote_get( self::$apiurl . '/of-the-day/' . $type );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) { 
			return __( '<p>LezWatch.TV is temporarily offline, but will return soon.</p>', 'bury-your-queers' ); 
		}

		$response = wp_remote_retrieve_body( $request );
		$response = json_decode( $response, true );

		switch ( $type ) {
			case 'death':
				$image   = '';
				$content = self::died_on_this_day( 'today' );
				break;
			case 'birthday':
				if ( !empty( $response ) && isset( $response['birthdays'] ) ) {
					$image   = '<img src="' . plugins_url( 'birthday.jpg', __FILE__ ) . '" width="' . get_option( 'medium_size_w' ) .'">';
					$content = $response['birthdays'];
				} else {
					$image   = '';
					$content = __( 'No one is celebrating a birthday today.', 'bury-your-queers' );
				}
				break;
			default:
				$image   = '<a href="' .  $response['url'] . '"><img src="' . $response['image'] . '" width="' . get_option( 'medium_size_w' ) .'"></a><br />';
				$content = '<a href="' .  $response['url'] . '">' . $response['name'] . '</a>';
		}

		$return = '<div class="lwtv-of-the-day lwtv-' . $type . '-of-the-day">' . $image . $content . '</div>';

		return $return;
	}

	/**
	 * On This Day
	 * Code that generates the On This Day death code
	 */
	public static function died_on_this_day( $this_day = 'today' ) {

		$this_day = sanitize_text_field( $this_day );
		if ( $this_day !== 'today' ) {
			$month = substr( $this_day, 0, 2);
			$day = substr( $this_day, 3, 2);
			$this_day = ( checkdate ( $month, $day , date('Y') ) == true )? $this_day : 'today' ;
		}

		$echo_day = ( $this_day == 'today' )? time() : strtotime( date('Y').'-'.$this_day );
		$json_day = ( $this_day == 'today' )? '' : $this_day.'/' ;

		$request  = wp_remote_get( self::$apiurl . '/on-this-day/' . $json_day );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) { 
			return __( '<p>LezWatch.TV is temporarily offline, but will return soon.</p>', 'bury-your-queers' ); 
		}

		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);

		$count = ( key($response) == 'none' )? 0 : count($response) ;
		$how_many = __('no characters died!', 'bury-your-queers');
		$the_dead = '';

		if ( $count > 0 ) {
			$how_many = sprintf( _n( '%s character died:', '%s characters died:', $count, 'bury-your-queers' ), $count );

			$the_dead = '<ul class="byq-otd">';

			foreach ( $response as $dead_character ) {
				$the_dead .= '<li><a href="'.$dead_character['url'].'">'.$dead_character['name'].'</a> - '.$dead_character['died'] .'</li>';
			}
			$the_dead .= '</ul>';
		}

		$onthisday = '<p>'. sprintf( __( 'On %s, %s', 'bury-your-queers'), date('F jS', $echo_day ), $how_many ).'</p>';
		$return = $onthisday.$the_dead;

		return $return;
	}

	/**
	 * Statistics
	 * Code that generates the stats of death code
	 */
	public static function statistics( $format = 'all' ) {
		$format = sanitize_text_field( $format );
		
		// Request Data
		$request  = wp_remote_get( self::$apiurl . '/stats/death/' );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) { 
			return __( '<p>LezWatch.TV is temporarily offline, but will return soon.</p>', 'bury-your-queers' ); 
		}

		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);
		
		// The Math part
		$live_chars    = $response[ 'characters' ][ 'alive' ];
		$dead_chars    = $response[ 'characters' ][ 'dead' ];
		$total_chars   = $live_chars + $dead_chars;
		$percent_chars = number_format( ( $dead_chars / $total_chars ) * 100, 2 );
		
		$live_shows    = $response[ 'shows' ][ 'no-death' ];
		$dead_shows    = $response[ 'shows' ][ 'death' ];
		$total_shows   = $live_shows + $dead_shows;
		$percent_shows = number_format( ( $dead_shows / $total_shows ) * 100, 2 );

		$character_return = sprintf( __( 'There are %s dead characters out of %s.', 'bury-your-queers' ), $live_chars, $total_chars ) ;
		$character_percent_return = sprintf( __( '%s%% of all queer females on TV are dead.', 'bury-your-queers' ), $percent_chars ) ;

		$show_return = sprintf( __( 'There are %s shows with dead characters out of %s.', 'bury-your-queers' ), $dead_shows, $total_shows ) ;
		$show_percent_return = sprintf( __( '%s%% of TV shows with any queer female have at least one dead.', 'bury-your-queers' ), $percent_shows ) ;
		
		switch ( $format ) {
			case 'characters':
				$return = $character_percent_return;
				break;
			
			case 'shows':
				$return = $show_percent_return;
				break;
			
			default: 
				$return = $character_percent_return . ' ' . $show_percent_return;
		}
		return '<p>' . $return . '</p>';
	}


	/**
	 * this_year function.
	 * 
	 * @access public
	 * @param bool $year (default: false)
	 * @return void
	 */
	function this_year( $year = false ) {
		
		// If the year isn't valid, we default to this year
		$year = ( !$year || !preg_match( '/^[0-9]{4}$/', $year ) )? date( 'Y' ) : $year;

		// Get the data
		$request  = wp_remote_get( self::$apiurl . '/what-happened/' . $year );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) { 
			return __( '<p>LezWatch.TV is temporarily offline, but will return soon.</p>', 'bury-your-queers' ); 
		}

		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);

		// If we got an error, bail
		if ( array_key_exists( 'success', $response ) && !$response['success'] ) {
			$fail = sprintf( __( 'There were no queer female or trans characters on TV prior to %s.', 'bury-your-queers' ), $response['data'] );
			return $fail;
		}

		// Posts etc made:
		$characters = ( $response['characters'] == 0 )? __( 'no characters', 'bury-your-queers' ) : sprintf( _n( '%s character', '%s characters', $response['characters'], 'bury-your-queers' ), $response['characters'] );
		$shows      = ( $response['shows'] == 0 )? 'no shows' : sprintf( _n( '%s show', '%s shows', $response['shows'], 'bury-your-queers' ), $response['shows'] );
		$posts      = ( $response['posts'] == 0 )? 'no posts' : sprintf( _n( '%s post', '%s posts', $response['posts'], 'bury-your-queers' ), $response['posts'] );

		// This Year On Air information:
		$on_air  = ( $response['on_air']['current'] == 0 )? __( 'no shows', 'bury-your-queers' ) : sprintf( _n( '%s show', '%s shows', $response['on_air']['current'], 'bury-your-queers' ), $response['on_air']['current'] );
		$started = ( $response['on_air']['started'] == 0 )? __( 'no shows', 'bury-your-queers' ) : sprintf( _n( 'Only %s show', 'A total of %s shows', $response['on_air']['started'], 'bury-your-queers' ), $response['on_air']['started'] );
		$ended   = ( $response['on_air']['ended'] == 0 )? __( 'no shows', 'bury-your-queers' ) : sprintf( _n( 'only %s show', '%s shows', $response['on_air']['ended'], 'bury-your-queers' ), $response['on_air']['ended'] );

		// Death
		$death_this_year = ( $response['dead_year'] == 0 )? __( 'Amazingly no characters died', 'bury-your-queers' ) : sprintf( _n( 'Only %s character died', 'Sadly, %s characters died', $response['dead_year'], 'bury-your-queers' ), $response['dead_year'] );

		// The Output
		$return = sprintf( __( 'In %s, there were %s with queer female or trans characters on the air. %s started and %s ended that year. %s.', 'bury-your-queers' ), $year, $on_air, $started, $ended, $death_this_year );

		return $return;
	}

	// donate link on manage plugin page
	function donate_link( $links, $file ) {
		if ($file == plugin_basename(__FILE__)) {
			$donate_link = '<a href="https://ko-fi.com/A236CEN/">' . __( 'Donate', 'bury-your-queers' ) . '</a>';
			$links[] = $donate_link;
		}
		return $links;
	}

}
new LezWatch_TV();

// Include Widgets
include_once( plugin_dir_path( __FILE__ ) . 'widgets.php' );