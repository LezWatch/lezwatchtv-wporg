<?php
/**
 * Name: lezwatchtv-multi-blocks — CGB Gutenberg Block Plugin
 * Description: lezwatchtv-multi-blocks — is a Gutenberg plugin created via create-guten-block.
 *
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

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
