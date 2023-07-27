<?php
/**
 * Name: lezwatchtv-multi-blocks
 * Description: LezWatchTV News Blocks
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LezWatchTV_Multi_Blocks {

	public function __construct() {
		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets' ) );

		// Add a block category
		add_filter(
			'block_categories_all',
			function( $categories, $post ) {
				return array_merge(
					$categories,
					array(
						array(
							'slug'  => 'lezwatchtv',
							'title' => 'LezWatch.TV Blocks',
						),
					)
				);
			},
			10,
			2
		);

		/**
		 * Register Block Types -- Required for ServerSideRender:
		 *  - last-death
		 *  - of-the-day
		 */

		register_block_type(
			'lezwatchtv/last-death',
			array(
				'attributes'      => array(),
				'render_callback' => array( 'LezWatchTV', 'last_death' ),
			)
		);

		register_block_type(
			'lezwatchtv/of-the-day',
			array(
				'attributes'      => array(
					'otd' => array(
						'type' => 'string',
					),
				),
				'render_callback' => array( $this, 'render_otd' ),
			)
		);

		register_block_type(
			'lezwatchtv/about-show',
			array(
				'attributes'      => array(
					'showname' => array(
						'type' => 'string',
					),
				),
				'render_callback' => array( $this, 'render_about_show' ),
			)
		);

	}

	/**
	 * Render the Of The Day
	 */
	public function render_otd( $atts ) {
		$attributes = shortcode_atts(
			array(
				'otd' => 'character',
			),
			$atts
		);
		$otd_type   = sanitize_text_field( $attributes['otd'] );
		$return     = LezWatchTV::of_the_day( $otd_type );

		return $return;
	}

	/**
	 * Render About the Show
	 */
	public function render_about_show( $atts ) {
		$attributes = shortcode_atts(
			array(
				'showname' => '',
			),
			$atts
		);
		$showname   = sanitize_text_field( $attributes['showname'] );
		$return     = LezWatchTV::about_show( $showname );

		return $return;
	}

	public function block_assets() {
		$build_css = '/build/style-index.css';
		wp_enqueue_style(
			'lezwatchtv-plugin',
			plugins_url( $build_css, __FILE__ ),
			array(),
			filemtime( dirname( __FILE__ ) . $build_css )
		);
	}

	public function block_editor_assets() {
		$build_js = '/build/index.js';
		// Scripts.
		wp_enqueue_script(
			'lezwatchtv-plugin', // Handle.
			plugins_url( $build_js, __FILE__ ),
			array( 'wp-editor', 'wp-i18n', 'wp-element' ),
			filemtime( dirname( __FILE__ ) . $build_js ),
			true
		);

		$build_css = '/build/index.css';
		// Styles.
		wp_enqueue_style(
			'lezwatchtv-plugin',
			plugins_url( $build_css, __FILE__ ),
			array(),
			filemtime( dirname( __FILE__ ) . $build_css )
		);
	}
}

new LezWatchTV_Multi_Blocks();
