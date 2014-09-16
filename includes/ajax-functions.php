<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     Related Posts by Taxonomy
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Displays file content from an included file.
 *
 * @since 1.0
 * @return void
 */
function wp_tv_ajax_display_file_content() {

	$nonce   = isset( $_POST['wp_tv_nonce'] ) ? $_POST['wp_tv_nonce'] : '';	
	$error   = '<p class="wp_tv_error">' . __( 'Error', 'wp-template-viewer' ) . ': ';
	$success = false;

	$data = $file_content = '';

	// check the nonce
	if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'wp_template_viewer_nonce' ) ) {
		die( 'not allowed' );
	}

	// Path data not found (todo: is this even possible? ).
	if ( ! ( isset( $_POST['wp_tv_file'] ) && $_POST['wp_tv_file'] ) ) {
		$data = '<div id="wp_tv_code_title">' . $error . __( 'No file found', 'wp-template-viewer' ) . '</p></div>';
		wp_send_json_error ( $data );
	}

	$file = $_POST['wp_tv_file'];

	// check if file exists and is readable
	if ( is_readable( $file ) ) {

		$viewer = WP_TV();

		// get shorter version of path
		$attr = $viewer->files->get_file_attributes( $file );

		// file not in plugins or themes directory
		if ( 'wp_tv_external' === $attr['class'] ) {

		 // use file name only
		 $filename = (string)  basename( $file );
		} else {

		 // part of path or full path
		 $filename = $attr['path'];

		 // path name same as full path
		 $filename =( $filename  === $file ) ? basename( $filename ) : $filename;
		}

		//$filename = basename( $file );

		// get the file content
		$file_content = (string) file_get_contents( $file );

		if ( !empty( $file_content ) ) {
			$success = true;

			$data = '<p>';
			$data .= sprintf( 
				__( '<strong>File: %1$s</strong> - %2$s', 'wp-template-viewer' ),
				$filename,
				'<a href="" class="wp_tv_select">' . __( 'select content', 'wp-template-viewer' ) . '</a>'
			);
			$data .= '</p>';

		} else {

			$data = $error . sprintf( __( 'Could not get contents of file: %s', 'wp-template-viewer' ), $filename ) . '</p>';
		}

	} else {

		$data = $error . sprintf( __( 'Could not read file: %s', 'wp-template-viewer' ), $file ) . '</p>';
	}

	$data = '<div id="wp_tv_code_title">' . $data . '</div>';

	if ( $success ) {

		// add pre tags
		$content = '<pre id="wp_tv_content"><code>' . htmlspecialchars( $file_content ) . '</code></pre>';

		/**
		 * File content.
		 * important: encode raw content with htmlspecialchars()
		 *
		 * @param bool    $content Encoded file content.
		 */
		$content = apply_filters( 'wp_template_viewer_file_content', $content, $file_content, $file );
		wp_send_json_success ( $data . $content );
	}

	wp_send_json_error ( $data );
}

add_action( 'wp_ajax_nopriv_wp_tv_display_template_file',  'wp_tv_ajax_display_file_content'  );
add_action( 'wp_ajax_wp_tv_display_template_file',         'wp_tv_ajax_display_file_content'  );