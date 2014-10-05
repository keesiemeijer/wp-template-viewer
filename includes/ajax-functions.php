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
	$error   = '<p class="wp_tv_file_title wp_tv_error">' . __( 'Error', 'wp-template-viewer' ) . ': ';
	$success = false;

	$data = $file_content = '';

	// check the nonce
	if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'wp_template_viewer_nonce' ) ) {
		die( 'not allowed' );
	}

	// Path data not found (todo: is this even possible? ).
	if ( ! ( isset( $_POST['wp_tv_file'] ) && $_POST['wp_tv_file'] ) ) {
		$data = $error . __( 'No file found', 'wp-template-viewer' ) . '</p>';
		wp_send_json_error ( $data );
	}

	$file = $_POST['wp_tv_file'];

	// check if file exists and is readable
	if ( is_readable( $file ) ) {

		$viewer = WP_TV();

		// get shorter version of the file path if it's a theme or plugin file
		$filename = $viewer->files->get_trimmed_file_path( $file );

		// get the file content
		$file_content = (string) file_get_contents( $file );

		if ( !empty( $file_content ) ) {
			$success = true;

			$data = '<p class="wp_tv_file_title"><strong>' . __( 'File', 'wp-template-viewer' ) . ': ' . $filename . '</strong>';

			// actions
			$data .= ' - <a href="" class="wp_tv_select">' . __( 'select content', 'wp-template-viewer' ) . '</a>';
			$data .= ' - <a href="" class="wp_tv_lines">' . __( 'line numbers', 'wp-template-viewer' ) . '</a>';
			$edit_file_link = $viewer->files->get_file_edit_link( $file );
			$data .= !empty( $edit_file_link ) ? ' - ' . $edit_file_link : '';
			$data .= '</p>';

		} else {

			$data = $error . sprintf( __( 'Could not get contents of file: %s', 'wp-template-viewer' ), $filename ) . '</p>';
		}

	} else {

		$data = $error . sprintf( __( 'Could not read file: %s', 'wp-template-viewer' ), $file ) . '</p>';
	}

	if ( $success ) {

		// important: encode raw file content with htmlspecialchars()
		// content needs to be trimmed for line numbers to work correctly
		$content = rtrim( htmlspecialchars(  $file_content ) );

		$content = '<div id="wp_tv_content"><pre><code>' . $content . '</code></pre></div>';
		$type = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

		/**
		 * File content.
		 *
		 * @param bool    $content Encoded file content.
		 */
		$content = apply_filters( 'wp_template_viewer_file_content', $content, $file_content, $file, $type );
		wp_send_json_success ( $data . $content );
	}

	wp_send_json_error ( $data );
}

add_action( 'wp_ajax_nopriv_wp_tv_display_template_file',  'wp_tv_ajax_display_file_content'  );
add_action( 'wp_ajax_wp_tv_display_template_file',         'wp_tv_ajax_display_file_content'  );