<?php
/**
 Plugin Name: Bury Your Queers
 Plugin URI: http://lezwatchtv.com/cliche/dead/
 Description: Show solidarity with fictional dead female queers.
 Version: 1.2.2
 Author: LezWatch TV
 Author URI: https://lezwatchtv.com/
 License: GPLv2 (or Later)

	Copyright 2017 LezWatchTV (email: webmaster@lezwatchtv.com)

	This file is part of Bury Your Queers, a plugin for WordPress.

	Bury Your Queers is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	Bury Your Queers is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * class Bury_Your_Queers
 *
 * Main class for plugin
 *
 * @since 1.0
 */
class Bury_Your_Queers {

	protected static $version;
	protected static $apiurl;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		self::$version = '1.2.2';
		self::$apiurl  = 'https://lezwatchtv.com/wp-json/lwtv/v1';
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
		wp_enqueue_script( 'byq-onthisday', plugins_url( 'js/otd-datepicker.js', __FILE__ ), array( 'jquery-ui-datepicker' ), self::$version, true );
		wp_enqueue_style( 'jquery-ui', plugins_url( 'css/jquery-ui.css', __FILE__ ), array(), self::$version );
	}

	/**
	 * Shortcode
	 */
	public function shortcode( $atts ) {
		$attributes = shortcode_atts([
			'data'        => 'last-death',
			'date-format' => 'today',
			'stat-type'   => 'all',
		], $atts);

		$this_day = sanitize_text_field( $attributes[ 'date-format' ] );
		$stat_fmt = sanitize_text_field( $attributes[ 'stat-type' ] );
		
		switch ( $attributes[ 'data' ] ) {
			case 'last-death':
				$return = $this->last_death();
				break;
			
			case 'on-this-day':
				$return = $this->on_this_day( $this_day );
				break;

			case 'stats':
				$return = $this->statistics( $stat_fmt );
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
		
		$widgets = array( 'BYQ_Last_Death_Widget', 'BYQ_On_This_Day_Widget', 'BYQ_Statistics_Widget' );
		
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
			return __( '<p>Bury Your Queers is temporarily offline, but will return soon.</p>', 'bury-your-queers'); 
		}
		
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode($response, true);

		$diff = $response['since'];

		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

		$since = '';
		if ( $years != 0 ) $since .= sprintf( _n( '%s year, ', '%s years, ', $years, 'bury-your-queers' ), $years );
		if ( $months != 0 ) $since .= sprintf( _n( '%s month', '%s months', $months, 'bury-your-queers' ), $months );
		$since .= ( $years != 0 )? ', ' : '';
		$since .= ( $days != 0 && $months != 0 )? __(' and ', 'bury-your-queers') : '';
		if ( $days != 0 ) $since .= sprintf( _n( '%s day', '%s days', $days, 'bury-your-queers' ), $days );

		$response['since'] = $since;

		$return = '<p>'.sprintf( __('It has been %s since the last queer female death on television', 'bury-your-queers'), '<strong>'.$response['since'].'</strong>' );
		$return .= ': <a href="'.$response['url'].'">'.$response['name'].'</a> - '.date('F j, Y', $response['died'] ).'</p>';

		return $return;
	}

	/**
	 * On This Day
	 * Code that generates the On This Day code
	 */
	public static function on_this_day( $this_day = 'today' ) {

		$this_day = sanitize_text_field( $this_day );
		if ( $this_day !== 'today' ) {
			$month = substr( $this_day, 0, 2);
			$day = substr( $this_day, 3, 2);
			$this_day = ( checkdate ( $month, $day , date('Y') ) == true )? $this_day : 'today' ;
		}

		$echo_day = ( $this_day == 'today' )? time() : strtotime( date('Y').'-'.$this_day );
		$json_day = ( $this_day == 'today' )? '' : $this_day.'/' ;

		$request  = wp_remote_get( self::$apiurl . '/on-this-day/'.$json_day );
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

	// donate link on manage plugin page
	function donate_link( $links, $file ) {
		if ($file == plugin_basename(__FILE__)) {
			$donate_link = '<a href="https://ko-fi.com/A236CENl/">' . __( 'Donate', 'bury-your-queers' ) . '</a>';
			$links[] = $donate_link;
		}
		return $links;
	}

}
new Bury_Your_Queers();

// Include Widgets
include_once( plugin_dir_path( __FILE__ ) . 'widgets.php' );