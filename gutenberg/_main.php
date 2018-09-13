<?php
/**
 * Plugin File: Gutenberg Blocks
 * @since 1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class LezWatchTV_Gutenblocks {

	protected static $directory;

	public function __construct() {
		self::$directory = dirname( __FILE__ );

		//add_action( 'init', array( $this, 'died_on_this_day' ) );
		add_action( 'init', array( $this, 'of_the_day' ) );
		add_action( 'init', array( $this, 'last_death' ) );

		// Add a block category
		add_filter( 'block_categories', function( $categories, $post ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'lezwatchtv',
						'title' => 'LezWatchTV',
					),
				)
			);
		}, 10, 2 );
	}

	public function last_death() {
		$index_js = 'last-death/index.js';
		wp_register_script(
			'last-death-editor',
			plugins_url( $index_js, __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			filemtime( self::$directory . '/' . $index_js ),
			false
		);

		register_block_type(
			'lezwatchtv/last-death',
			array(
				'attributes'      => array(),
				'editor_script'   => 'last-death-editor',
				'render_callback' => array( 'LezWatchTV', 'last_death' ),
			)
		);
	}

	public function of_the_day() {
		$index_js = 'of-the-day/index.js';
		wp_register_script(
			'of-the-day-editor',
			plugins_url( $index_js, __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			filemtime( self::$directory . '/' . $index_js ),
			false
		);

		register_block_type(
			'lezwatchtv/of-the-day',
			array(
				'attributes'      => array(
					'data' => array(
						'type'    => 'string',
						'default' => 'of-the-day',
					),
					'otd'  => array(
						'type' => 'string',
					),
				),
				'editor_script'   => 'of-the-day-editor',
				'render_callback' => array( 'LezWatchTV', 'shortcode' ),
			)
		);
	}

	public function died_on_this_day() {
		$index_js = 'died-on-this-day/index.js';
		wp_register_script(
			'died-on-this-day-editor',
			plugins_url( $index_js, __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			filemtime( self::$directory . '/' . $index_js ),
			false
		);

		register_block_type(
			'lezwatchtv/died-on-this-day',
			array(
				'attributes'      => array(
					'data' => array(
						'type'    => 'string',
						'default' => 'on-this-day',
					),
					'date' => array(
						'type'    => 'array',
						'default' => time(),
					),
				),
				'editor_script'   => 'died-on-this-day-editor',
				'render_callback' => array( 'LezWatchTV', 'shortcode' ),
			)
		);
	}

}

if ( function_exists( 'register_block_type' ) ) {
	new LezWatchTV_Gutenblocks();
}
