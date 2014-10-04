<?php
/**
 * Scripts
 *
 * @package     WP Template Viewer
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register stylesheets for the public-facing side of the site.
 *
 * @since 1.0
 * @return void
 */
function wp_tv_register_public_styles() {
	wp_register_style(
		'wp-template-viewer',
		WP_TV_PLUGIN_URL . 'public/css/wp-template-viewer.css'
	);

	wp_enqueue_style( 'wp-template-viewer' );

	// load style for right to left languages
	if ( is_rtl() ) {
		wp_register_style(
			'wp-template-viewer-rtl',
			WP_TV_PLUGIN_URL . 'public/css/wp-template-viewer-rtl.css'
		);

		wp_enqueue_style( 'wp-template-viewer-rtl' );
	}
}

add_action( 'wp_enqueue_scripts', 'wp_tv_register_public_styles', 99 );



/**
 * Register Javascript for the public-facing side of the site.
 *
 * @since 1.0
 * @return void
 */
function wp_tv_register_public_scripts() {

	wp_register_script( 'wp-template-viewer', WP_TV_PLUGIN_URL . 'public/js/wp-template-viewer.js',  array( 'jquery' ) );
	wp_enqueue_script( 'wp-template-viewer' );

	$js_vars = array(
		'wp_tv_ajaxurl'        => admin_url( 'admin-ajax.php' ),
		'wp_tv_nonce'          => wp_create_nonce( 'wp_template_viewer_nonce' ),
		'wp_tv_hide_in_footer' => __( 'hide files in footer', 'wp-template-viewer' ),
		'wp_tv_show_in_footer' => __( 'show files in footer', 'wp-template-viewer' ),
		'wp_tv_hide'           => __( 'hide files', 'wp-template-viewer' ),
		'wp_tv_show'           => __( 'show files', 'wp-template-viewer' ),
		'wp_tv_close'          => __( 'close template viewer', 'wp-template-viewer' ),
		'wp_tv_lines'          => __( 'line numbers', 'wp-template-viewer' ),
		'wp_tv_hide_lines'     => __( 'hide line numbers', 'wp-template-viewer' ),
	);
	
	wp_localize_script( 'wp-template-viewer', 'wp_tv_ajax', $js_vars );
}

add_action( 'wp_enqueue_scripts', 'wp_tv_register_public_scripts' );