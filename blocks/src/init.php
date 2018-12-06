<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 * Registers block types for ServerSideRender
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LezWatchTV_Multi_Blocks {

	public function __construct() {
		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets' ) );

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
					'data' => array(
						'type'    => 'string',
						'default' => 'of-the-day',
					),
					'otd'  => array(
						'type' => 'string',
					),
				),
				'editor_script'   => 'of-the-day-editor',
				'style'           => 'of-the-day-style',
				'render_callback' => array( 'LezWatchTV', 'shortcode' ),
			)
		);
	}

	public function block_assets() {
		// Styles.
		wp_enqueue_style(
			'lezwatchtv_multi_blocks-cgb-style-css', // Handle.
			plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
			array( 'wp-editor' ),
			filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
		);
	}

	public function block_editor_assets() {
		// Scripts.
		wp_enqueue_script(
			'lezwatchtv_multi_blocks-cgb-block-js', // Handle.
			plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
			array( 'wp-editor', 'wp-i18n', 'wp-element' ),
			filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
			true
		);

		// Styles.
		wp_enqueue_style(
			'lezwatchtv_multi_blocks-cgb-block-editor-css',
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
			array(),
			filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
		);
	}
}

new LezWatchTV_Multi_Blocks();
