<?php
/**
 * Plugin Name: LezWatch.TV News & Information
 * Plugin URI: https://lezwatchtv.com/about/resources/
 * Description: Display information on queer female and trans representation on TV. Brought to you by LezWatch.TV.
 * Version: 2.0
 * Author: LezWatch.TV
 * Author URI: https://lezwatchtv.com/
 * License: GPLv2 (or Later)
 *
 * Copyright 2017-2021 LezWatch.TV (email: webmaster@lezwatchtv.com)
 *
 * This file is part of LezWatch.TV News & Information, a plugin for WordPress.
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * class LezWatch.TV
 *
 * Main class for plugin
 *
 * @since 1.0
 */
class LezWatchTV {

	protected static $version;
	public static $apiurl;
	public static $unavailable;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		self::$version     = '1.2.1';
		self::$apiurl      = 'https://lezwatchtv.com/wp-json/lwtv/v1';
		self::$unavailable = __( 'LezWatch.TV\'s API is temporarily offline, but will return soon.', 'lezwatchtv' );

		// This should only apply to LWTV Dev sites.
		if ( WP_DEBUG && defined( 'LWTV_DEV_SITE_API' ) ) {
			self::$apiurl = LWTV_DEV_SITE_API . '/wp-json/lwtv/v1';
		}
	}

	/**
	 * Admin Init
	 */
	public function admin_init() {
		// Deactivate the old plugin
		if ( is_plugin_active( 'bury-your-queers/bury-your-queers.php' ) ) {
			deactivate_plugins( 'bury-your-queers/bury-your-queers.php' );
		}

		// If the old plugin is still installed, show an error.
		if ( file_exists( plugin_dir_path( __DIR__ ) . 'bury-your-queers/bury-your-queers.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_byq' ) );
		}

		add_filter( 'plugin_row_meta', array( $this, 'donate_link' ), 10, 2 );
	}

	/**
	 * Init
	 */
	public function init() {
		add_shortcode( 'lwtv', array( $this, 'shortcode' ) );
	}

	/**
	 * Admin Notice: Please uninstall BYQ
	 * @return string
	 * @since 1.1.0
	 */
	public function admin_notice_byq() {
		// Generate uninstall URL
		$uninstall = wp_nonce_url( 'plugins.php?action=delete-selected&verify-delete=1&checked[]=bury-your-queers/bury-your-queers.php', 'bulk-plugins' );

		// translators: %s is the uninstall URL
		$message = sprintf( __( 'You have activated LezWatch.TV, however the old plugin (formerly called Bury Your Queers) is still installed. Please <strong><a href=%s>uninstall</a></strong> the plugin as it will no longer work.', 'lezwatchtv' ), esc_url( $uninstall ) );

		// translators: %s is the message translated above
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Admin Scripts
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'widgets.php' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'lwtv-onthisday', plugins_url( 'assets/js/otd-datepicker.js', __FILE__ ), array( 'jquery-ui-datepicker' ), self::$version, true );
		wp_enqueue_style( 'jquery-ui', plugins_url( 'assets/css/jquery-ui.css', __FILE__ ), array(), self::$version );
	}

	/**
	 * Shortcode
	 */
	public static function shortcode( $atts ) {
		$attributes = shortcode_atts(
			array(
				'data' => 'of-the-day',
				'date' => 'today',
				'stat' => 'all',
				'otd'  => 'character',
			),
			$atts
		);

		$this_day = sanitize_text_field( $attributes['date'] );
		$stat_fmt = sanitize_text_field( $attributes['stat'] );
		$otd_type = sanitize_text_field( $attributes['otd'] );

		switch ( $attributes['data'] ) {
			case 'last-death':
				$return = self::last_death();
				break;
			case 'of-the-day':
				$return = self::of_the_day( $otd_type );
				break;
			case 'on-this-day':
			case 'died-on-this-day':
				$return = self::died_on_this_day( $this_day );
				break;
			case 'stats':
				$return = self::statistics( $stat_fmt );
				break;
			case 'this-year':
				$return = self::this_year( $this_day );
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

		$widgets = array(
			'LezWatchTV_Last_Death_Widget',
			'LezWatchTV_Of_The_Day_Widget',
			'LezWatchTV_On_This_Day_Widget',
			'LezWatchTV_Statistics_Widget',
			'LezWatchTV_This_Year_Widget',
		);

		foreach ( $widgets as $widget ) {
			if ( class_exists( $widget ) ) {
				$this->widget = new $widget();
				register_widget( $this->widget );
			}
		}
	}

	/**
	 * The Last Death
	 * Code that generates the last death
	 */
	public static function last_death() {

		$request = wp_remote_get( self::$apiurl . '/last-death/' );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
			$message = self::$unavailable;
			if ( WP_DEBUG ) {
				$message .= ' - ' . wp_remote_retrieve_response_code( $request );
			}
			$return = '<p>' . $message . '</p>';
		} else {
			$response = wp_remote_retrieve_body( $request );
			$response = json_decode( $response, true );
			// translators: %s is the amount of time since a queer death (1 day, 2 days, 1 month, etc)
			$return  = '<p>' . sprintf( __( 'It has been %s since the last queer female, non-binary, or transgender death on television', 'lezwatchtv' ), '<strong>' . human_time_diff( $response['died'], current_time( 'timestamp' ) ) . '</strong> ' );
			$return .= ': <span class="lwtv-recently-dead"><a href="' . $response['url'] . '">' . $response['name'] . '</a> - ' . gmdate( 'F j, Y', $response['died'] ) . '</span></p>';
		}

		$return = '<div class="lezwatchtv last-death">' . $return . '</div>';

		return $return;
	}

	/**
	 * Of The Day
	 * Code that generates the Of The Day code
	 */
	public static function of_the_day( $type = 'character' ) {

		// Quick Failsafe
		$valid_types = array( 'character', 'show', 'death', 'birthday' );
		if ( ! in_array( $type, $valid_types, true ) ) {
			$type = 'character';
		}

		$request = wp_remote_get( self::$apiurl . '/of-the-day/' . $type );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
			$message = self::$unavailable;
			if ( WP_DEBUG ) {
				$message .= ' - ' . wp_remote_retrieve_response_code( $request );
			}
			$return = '<p>' . $message . '</p>';
		} else {
			$response = wp_remote_retrieve_body( $request );
			$response = json_decode( $response, true );

			switch ( $type ) {
				case 'death':
					$image   = '';
					$title   = '';
					$content = self::died_on_this_day( 'today' );
					break;
				case 'birthday':
					if ( ! empty( $response ) && isset( $response['birthdays'] ) ) {
						$image   = '<img src="' . plugins_url( 'assets/birthday.jpg', __FILE__ ) . '" width="' . get_option( 'medium_size_w' ) . '">';
						$content = $response['birthdays'];
					} else {
						$image   = '';
						$title   = '';
						$content = __( 'No one is celebrating a birthday today.', 'lezwatchtv' );
					}
					break;
				default:
					$image   = '<a href="' . $response['url'] . '"><img src="' . $response['image'] . '" width="' . get_option( 'medium_size_w' ) . '"></a><br />';
					$content = '<a href="' . $response['url'] . '">' . $response['name'] . '</a>';
			}

			$return = '<div class="lezwatchtv of-the-day ' . $type . '-of-the-day">' . $image . $content . '</div>';
		}

		return $return;
	}

	/**
	 * On This Day
	 * Code that generates the On This Day death code
	 */
	public static function died_on_this_day( $this_day = 'today' ) {

		$this_day = sanitize_text_field( $this_day );
		if ( 'today' !== $this_day ) {
			$month    = substr( $this_day, 0, 2 );
			$day      = substr( $this_day, 3, 2 );
			$this_day = ( true === checkdate( $month, $day, gmdate( 'Y' ) ) ) ? $this_day : 'today';
		}

		$echo_day = ( 'today' === $this_day ) ? time() : strtotime( gmdate( 'Y' ) . '-' . $this_day );
		$json_day = ( 'today' === $this_day ) ? '' : $this_day . '/';
		$request  = wp_remote_get( self::$apiurl . '/on-this-day/' . $json_day );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
			$message = self::$unavailable;
			if ( WP_DEBUG ) {
				$message .= ' - ' . wp_remote_retrieve_response_code( $request );
			}
			$return = '<p>' . $message . '</p>';
		} else {
			$response = wp_remote_retrieve_body( $request );
			$response = json_decode( $response, true );

			$count    = ( 'none' === key( $response ) ) ? 0 : count( $response );
			$how_many = __( 'no characters died!', 'lezwatchtv' );
			$the_dead = '';

			if ( $count > 0 ) {
				// translators: %s is the number of dead characters.
				$how_many = sprintf( _n( '%s character died:', '%s characters died:', $count, 'lezwatchtv' ), $count );

				$the_dead = '<ul class="byq-otd">';

				foreach ( $response as $dead_character ) {
					$the_dead .= '<li><a href="' . $dead_character['url'] . '">' . $dead_character['name'] . '</a> - ' . $dead_character['died'] . '</li>';
				}
				$the_dead .= '</ul>';
			}

			// translators: %1$s is the date; %2$s is the number of dead
			$onthisday = '<p>' . sprintf( __( 'On %1$s, %2$s', 'lezwatchtv' ), gmdate( 'F jS', $echo_day ), $how_many ) . '</p>';
			$return    = $onthisday . $the_dead;
		}

		return $return;
	}

	/**
	 * Statistics
	 * Code that generates the stats of death code
	 */
	public static function statistics( $format = 'all' ) {
		$format = sanitize_text_field( $format );

		// Request Data
		$request = wp_remote_get( self::$apiurl . '/stats/death/' );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
			$message = self::$unavailable;
			if ( WP_DEBUG ) {
				$message .= ' - ' . wp_remote_retrieve_response_code( $request );
			}
			$return = '<p>' . $message . '</p>';
		} else {
			$response = wp_remote_retrieve_body( $request );
			$response = json_decode( $response, true );

			// The Math part
			$live_chars    = $response['characters']['alive'];
			$dead_chars    = $response['characters']['dead'];
			$total_chars   = $live_chars + $dead_chars;
			$percent_chars = number_format( ( $dead_chars / $total_chars ) * 100, 2 );

			$live_shows    = $response['shows']['no-death'];
			$dead_shows    = $response['shows']['death'];
			$total_shows   = $live_shows + $dead_shows;
			$percent_shows = number_format( ( $dead_shows / $total_shows ) * 100, 2 );

			// translators: %1$s is the number of dead characters out of %2$s total characters.
			$character_return = sprintf( __( 'There are %1$s dead characters out of %2$s.', 'lezwatchtv' ), $live_chars, $total_chars );
			// translators: %s is the percentage of dead.
			$character_percent_return = sprintf( __( '%s%% of all queer females on TV are dead.', 'lezwatchtv' ), $percent_chars );

			// translators: %1$s is the number of shows with dead characters out of %2$s total shows.
			$show_return = sprintf( __( 'There are %1$s shows with dead characters out of %2$s.', 'lezwatchtv' ), $dead_shows, $total_shows );
			// translators: %s is the percentage of shows with dead.
			$show_percent_return = sprintf( __( '%s%% of TV shows with any queer female have at least one dead.', 'lezwatchtv' ), $percent_shows );

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
	public function this_year( $year = false ) {

		// If the year isn't valid, we default to this year
		$year = ( ! $year || ! preg_match( '/^[0-9]{4}$/', $year ) ) ? gmdate( 'Y' ) : $year;

		// Get the data
		$request = wp_remote_get( self::$apiurl . '/what-happened/' . $year );

		// Make sure it's running before we do anything...
		if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
			$message = self::$unavailable;
			if ( WP_DEBUG ) {
				$message .= ' - ' . wp_remote_retrieve_response_code( $request );
			}
			$return = '<p>' . $message . '</p>';
		} else {
			$response = wp_remote_retrieve_body( $request );
			$response = json_decode( $response, true );

			// If we got an error, bail
			if ( array_key_exists( 'success', $response ) && ! $response['success'] ) {
				// translators: %s is a year. Probably 1961.
				$fail = sprintf( __( 'There were no queer female or trans characters on TV prior to %s.', 'lezwatchtv' ), $response['data'] );
				return $fail;
			}

			// Posts etc made.
			// translators: %s is the number of characters
			$characters = ( 0 === $response['characters'] ) ? __( 'no characters', 'lezwatchtv' ) : sprintf( _n( '%s character', '%s characters', $response['characters'], 'bury-your-queers' ), $response['characters'] );
			// translators: %s is the number of shows
			$shows = ( 0 === $response['shows'] ) ? 'no shows' : sprintf( _n( '%s show', '%s shows', $response['shows'], 'bury-your-queers' ), $response['shows'] );
			// translators: %s is the number of posts
			$posts = ( 0 === $response['posts'] ) ? 'no posts' : sprintf( _n( '%s post', '%s posts', $response['posts'], 'bury-your-queers' ), $response['posts'] );

			// This Year On Air information:
			// translators: %s is the number of shows on air in that year
			$on_air = ( 0 === $response['on_air']['current'] ) ? __( 'no shows', 'lezwatchtv' ) : sprintf( _n( '%s show', '%s shows', $response['on_air']['current'], 'bury-your-queers' ), $response['on_air']['current'] );
			// translators: %s is the number of shows that started in that year
			$started = ( 0 === $response['on_air']['started'] ) ? __( 'no shows', 'lezwatchtv' ) : sprintf( _n( 'Only %s show', 'A total of %s shows', $response['on_air']['started'], 'bury-your-queers' ), $response['on_air']['started'] );
			// translators: %s is the number of shows that ended in that year
			$ended = ( 0 === $response['on_air']['ended'] ) ? __( 'no shows', 'lezwatchtv' ) : sprintf( _n( 'only %s show', '%s shows', $response['on_air']['ended'], 'bury-your-queers' ), $response['on_air']['ended'] );

			// Death
			// translators: %s is the number of characters that died in that year
			$death_this_year = ( 0 === $response['dead_year'] ) ? __( 'Amazingly no characters died', 'lezwatchtv' ) : sprintf( _n( 'Only %s character died', 'Sadly, %s characters died', $response['dead_year'], 'bury-your-queers' ), $response['dead_year'] );

			// The Output
			// translators: %1$s is the year; %2$s is the number of characters on TV that Year; %3$s is the number of shows that begun that year; %4$s is the number of shows that ended that year; %5$s is the all the stuff about dead that year
			$return = sprintf( __( 'In %1$s, there were %2$s with queer female or trans characters on the air. %3$s started and %4$s ended that year. %5$s.', 'lezwatchtv' ), $year, $on_air, $started, $ended, $death_this_year );
		}

		return $return;
	}

	// Render About a Show
	public function about_show( $showname ) {

		$default = __( 'Looking for more inforamtion on queer TV? Check out <a href="https://lezwatchtv.com">LezWatch.TV</a>, the greatest database of queer female, transgender, and non-binary tv representation.' );


		// right now nothing
		// This needs to check if there's a show matching the name as best it can
		// and then return details
		// Looking for more info on XXX?
		// Next Airing, etc
		// if it can't find the show, it should be an ad for LezWatch.

		$return = '<div class="lezwatchtv about-show">' . $message . '</div>';

		return $return;

	}

	// donate link on manage plugin page
	public function donate_link( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$donate_link = '<a href="https://ko-fi.com/A236CEN/">' . __( 'Donate', 'lezwatchtv' ) . '</a>';
			$links[]     = $donate_link;
		}
		return $links;
	}

}
new LezWatchTV();

// Include Widgets
require_once 'widgets/_main.php';

// Include Blocks
require_once 'blocks/_main.php';
