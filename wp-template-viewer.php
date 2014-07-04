<?php
/*
Plugin Name: WP Template Viewer
Version: 0.1
Description: Display the content of theme template files in use for the current page by clicking a link in the toolbar.
Author: keesiemijer
Author URI:
License: GPL v2
Domain Path: /lang

WP Template Viewer
Copyright 2013  Kees Meijer  (email : keesie.meijer@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version. You may NOT assume that you can use any other version of the GPL.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( !class_exists( 'WP_TV_Template_Viewer' ) ) {

	class WP_TV_Template_Viewer {

		/**
		 * Array of included file paths.
		 *
		 * @since 0.1
		 * @var array
		 */
		public $included_files = array();

		/**
		 * Show files in footer.
		 *
		 * @since 0.1
		 * @var bool
		 */
		private $in_footer = false;

		/**
		 * User can view files.
		 *
		 * @since 0.1
		 * @var bool
		 */
		private $valid_user = false;

		/**
		 * Arguments.
		 *
		 * @since 0.1
		 * @var array
		 */
		public $args;

		/**
		 * Class instance.
		 *
		 * @since 0.1
		 * @see get_instance()
		 * @var object
		 */
		private static $instance = null;


		/**
		 * Access this plugin's working instance.
		 *
		 * @since 0.1
		 *
		 * @return object
		 */
		private static function get_instance() {
			// Create a new object if it doesn't exist.
			is_null( self::$instance ) && self::$instance = new self;
			return self::$instance;
		}

		/**
		 * Get class object on action hook wp_loaded.
		 *
		 * @since 0.1
		 */
		public static function init() {
			add_action( 'wp_loaded', array( self::get_instance(), 'setup' ) );
		}


		/**
		 * Setup properties, load text domain, add actions and filters
		 *
		 * @since 0.1
		 */
		function setup() {

			$this->args = array(
				'theme'          => wp_get_theme(),
				'stylesheet_dir' => get_stylesheet_directory(),
				'template_dir'   => get_template_directory(),
				'theme_root_dir' => get_theme_root(),
				'plugins_dir'    => defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : '',
			);

			// Actions also needed in admin.
			add_action( 'wp_ajax_nopriv_wp_tv_display_template_file', array(  $this, 'ajax_display_template_file' ) );
			add_action( 'wp_ajax_wp_tv_display_template_file',        array(  $this, 'ajax_display_template_file' ) );

			if ( is_admin() ) {
				return;
			}

			load_plugin_textdomain( 'wp-template-viewer', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

			$this->set_user_capabilities();

			if ( $this->valid_user ) {

				if ( !is_user_logged_in() ) {

					// Toolbar is not available, use action wp_footer to add the files.
					add_action( 'wp_footer', array( $this, 'get_included_files' ) );
					add_action( 'wp_footer', array( $this, 'footer_display' ) );

				} else {

					// Add the files to the toolbar just before it's rendered.
					add_action( 'wp_before_admin_bar_render', array( $this, 'get_included_files' ) );
					add_action( 'wp_before_admin_bar_render', array( $this, 'toolbar_display' ) );
					add_action( 'wp_before_admin_bar_render', array( $this, 'footer_display' ) );
				}

				// Enqueue scripts for valid users.
				add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			}
		}


		/**
		 * Loads Javascript and stylesheet.
		 *
		 * @since 0.1
		 */
		public function wp_enqueue_scripts() {

			wp_register_script( 'wp_template_viewer', plugins_url( '/wp-template-viewer.js', __FILE__ ),  array( 'jquery' ) );
			wp_enqueue_script( 'wp_template_viewer' );

			$js_vars = array(
				'wp_tv_ajaxurl' => admin_url( 'admin-ajax.php' ),
				'wp_tv_nonce'   => wp_create_nonce( 'wp_template_viewer_nonce' ),
				'wp_tv_title'   => __( 'WP Template Viewer', 'wp-template-viewer' ),
			);
			wp_localize_script( 'wp_template_viewer', 'wp_tv_ajax', $js_vars );

			wp_register_style( 'wp_template_viewer', plugins_url( '/wp-template-viewer.css', __FILE__ ) );
			wp_enqueue_style( 'wp_template_viewer' );
		}


		/**
		 * Sets the capabilities of the current user.
		 *
		 * @since 0.1
		 */
		public function set_user_capabilities() {

			$valid_user        = false;
			$in_footer         = false;
			$logged_in         = false;
			$logged_in_user_id = 0;

			if ( is_user_logged_in() ) {
				$logged_in = true;

				/**
				 * User id to show files to in the toolbar or footer.
				 *
				 * @since 0.1
				 *
				 * @param int     $user_id User ID. Default 0.
				 */
				$user_id = apply_filters( 'wp_template_viewer_user_id', 0 );

				if ( absint( $user_id ) ) {

					// 0 if not found
					$logged_in_user_id = get_current_user_id();

					if ( $user_id === $logged_in_user_id ) {
						// Logged in user with the correct id.
						$valid_user = true;
					}
				}

				if ( is_super_admin() ) {
					$valid_user = true;
				}
			}


			/**
			 * Show files to current user in the toolbar or footer.
			 *
			 * $valid_user == true  if logged in and user is a (super) admin or valid user.
			 * $valid_user == false if logged out
			 *
			 * @since 0.1
			 *
			 * @param bool    $valid_user The current user can see the files if true. Default none.
			 */
			$this->valid_user = (bool) apply_filters( 'wp_template_viewer_current_user', $valid_user, $logged_in_user_id );

			if ( !$logged_in && $this->valid_user ) {
				$in_footer = true;
			}

			/**
			 * Display files in footer.
			 *
			 * @since 0.1
			 *
			 * @param bool    $in_footer Show files in footer or not. Default none.
			 */
			$this->in_footer  = (bool) apply_filters( 'wp_template_viewer_in_footer', $in_footer );
		}


		/**
		 * Get all included file paths for the current page.
		 *
		 * @since 0.1
		 *
		 * @return void
		 */
		function get_included_files() {

			$files = array();

			// todo: is this stupid? check mime type? why not?
			$allowed_file_types = array( 'php', 'html', 'htm', 'js', 'css' );

			/**
			 * Allowed file types.
			 *
			 * @param array   $allowed_file_types Array with lower case file extensions. Empty array for any file type.
			 */
			$allowed = (array) apply_filters( 'wp_template_viewer_allowed_file_types', $allowed_file_types );

			foreach ( (array) get_included_files() as $file ) {
				$extension =  pathinfo( $file );
				$extension =  strtolower( (string) $extension['extension'] );
				if ( empty( $allowed_file_types ) || in_array( $extension, $allowed ) ) {
					$files[] = $file;
				}
			}

			$this->included_files = $files;
		}


		/**
		 * Returns all the included theme file paths for the current page.
		 * Allows for adding other file paths with a filter.
		 *
		 * @since 0.1
		 *
		 * @return array Array with included theme template paths.
		 */
		function get_theme_template_files() {

			$templates = array();

			foreach ( (array) $this->included_files as $template ) {

				// child and parent theme
				if ( 0 === strpos( $template, $this->args['stylesheet_dir'] ) ) {
					$templates[] = $template;
				}

				// parent theme
				if ( 0 === strpos( $template, $this->args['template_dir'] ) ) {
					$templates[] = $template;
				}

				/**
				 * Include files outside the current theme's directory.
				 *
				 * @param bool    $include Include file or not. Default false.
				 */
				$include = apply_filters( 'wp_template_viewer_include_file', false, $template ) ;

				if ( (bool) $include ) {
					$templates[] = $template;
				}
			}

			return array_values( array_unique( $templates ) );
		}


		/**
		 * Displays included template file paths for the current page in the footer
		 * Also add
		 *
		 * @since 0.1
		 */
		function footer_display() {

			// Well would you believe it, surrounded by five stars.
			echo "\n<!-- ***** WP Template Viewer Plugin ***** -->\n";

			// Class .wp_tv_no_js will be changed to .wp_tv_js by Javascript.
			echo '<div id="wp_tv_template_viewer" class="wp_tv_no_js">' . "\n";

			$templates = $this->get_theme_template_files();

			$display = !$this->in_footer ? ' style="display:none;"' : '';

			// Display of plugin title.
			echo "\t" . '<div class="wp_tv_file_list"' . $display . '>' . "\n\t";
			echo '<p class="wp_tv_file_list_title"><strong>' . __( 'WP Template Viewer', 'wp-template-viewer' );

			if ( empty( $templates ) ) {
				echo ': ' . __( 'No files found', 'wp-template-viewer' );
				echo "</strong></p>\n\t</div>\n</div>\n";

				return;
			} else {
				echo "</strong></p>\n\t";
			}


			// display files
			if ( $this->in_footer && $this->valid_user ) {
				echo '<p>';
				printf( __( 'Current Theme: %s', 'wp-template-viewer' ), $this->args['theme'] );
				echo "</p>\n\t";

				echo $this->file_list( $templates, true );
			}

			echo "\n\t</div>\n</div>\n";
		}


		/**
		 * Displays included template files for the current page in the toolbar.
		 *
		 * @since 0.1
		 */
		function toolbar_display() {

			global $wp_admin_bar;

			if ( $this->in_footer || !$this->valid_user ) {
				return;
			}

			$templates = $this->get_theme_template_files();

			$args = array(
				'id'    => 'wp_template_viewer_plugin',
				'title' => __( 'Templates', 'wp-template-viewer' ),
			);

			// Fo sho, that's a top level toolbar node. Top level yo!
			$wp_admin_bar->add_node( $args );

			$args['parent'] = 'wp_template_viewer_plugin';
			$args['id'] = 'wp_tv_current_theme_group';
			$wp_admin_bar->add_group( $args );

			if ( !empty( $templates ) ) {

				$args['id']   = 'wp_tv_template_files_group';
				$args['meta']['class'] = 'ab-sub-secondary';
				$wp_admin_bar->add_group( $args );
				unset( $args['meta'] );

				$args['parent'] = 'wp_tv_current_theme_group';
				$args['id']     = 'wp_tv_current_theme';
				$args['title']  = sprintf( __( 'Current Theme: %s', 'wp-template-viewer' ), $this->args['theme'] );
				$wp_admin_bar->add_node( $args );

				$args['parent']        = 'wp_tv_template_files_group';
				$args['id']            = 'wp_tv_template_files';
				$args['title']         = __( 'Included Files:', 'wp-template-viewer' );
				$args['meta']['class'] = 'wp_tv_no_js'; // changed to wp_tv_js by Javascript
				$args['meta']['html']  = $this->file_list( $templates ) ;
				$wp_admin_bar->add_node( $args );

			} else {

				$args['parent'] = 'wp_tv_current_theme_group';
				$args['id']     = 'wp_tv_current_theme';
				$args['title']  =  __( 'No files found', 'wp-template-viewer' );
				$wp_admin_bar->add_node( $args );
			}
		}


		/**
		 * Returns html list with paths
		 *
		 * @since 0.1
		 *
		 * @param array   $templates Array with paths
		 * @param boolean $footer    Adds _footer to id name.
		 * @return string            Html list with file paths.
		 */
		function file_list( $templates, $footer = false ) {

			$footer = $footer ? '_footer' : '';
			$html = '<ul id="wp_tv_files' . $footer . '" class="ab-submenu">'."\n\t\t";

			foreach ( $templates as $key => $template ) {
				$path_attr = $this->get_path_attributes( $template );
				$html .= '<li class="' . $path_attr['class'] . '">';
				$html .= '<span class="wp_tv_path ab-item ab-empty-item" data-wp_tv_path="' . esc_attr( $template ) . '">';
				$html .= $path_attr['path_excerpt'] . '</span></li>' . "\n\t\t";
			}

			return $html . "</ul>";
		}


		/**
		 * Returns attributes depending on the path.
		 *
		 * @since 0.1
		 *
		 * @param string  $path Path.
		 * @return array        Array with class and excerpt for the path.
		 */
		function get_path_attributes( $path ) {
			global $template;

			// default attributes
			$attr = array(
				'class'        => '',
				'path_excerpt' => '',
			);

			// Check if path starts with themes directory.
			if ( 0 === strpos( $path, $this->args['theme_root_dir'] ) ) {
				$theme_path = str_replace( dirname(  $this->args['theme_root_dir']  ), '', $path );
				$attr['path_excerpt'] = '/' . trim( esc_attr( $theme_path ), '/ ' );
				$attr['class'] .= 'wp_tv_theme';
			}

			// Check if path starts with plugins directory.
			if ( 0 === strpos( $path, $this->args['plugins_dir'] ) ) {
				$plugin_path = str_replace( dirname( $this->args['plugins_dir'] ), '', $path );
				$attr['path_excerpt'] = '/' . trim( esc_attr( $plugin_path ), '/ ' );
				$attr['class'] .= ( !empty( $attr['class'] ) ? ' ' : '' ) . 'wp_tv_plugin';
			}

			// Check if path is current theme template path
			if ( $path === $template ) {
				$attr['class'] .= ( !empty( $attr['class'] ) ? ' ' : '' ) . 'wp_tv_current';
			}

			$attr['path_excerpt'] = !empty( $attr['path_excerpt'] ) ? $attr['path_excerpt'] : esc_attr( $path );
			$attr['class']        = !empty( $attr['class'] ) ? $attr['class'] : 'wp_tv_external';

			return $attr;
		}


		/**
		 * Ajax handler to display the code from an included file.
		 *
		 * @since 0.1
		 */
		public function ajax_display_template_file() {

			$nonce = isset( $_POST['wp_tv_nonce'] ) ? $_POST['wp_tv_nonce'] : '';

			// check the nonce
			if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'wp_template_viewer_nonce' ) ) {
				die( 'not allowed' );
			}

			$response = array( 'file' => '', 'success' => false );

			if ( isset( $_POST['wp_tv_file'] ) && $_POST['wp_tv_file'] ) {

				// check if file exists and is readable
				if ( is_readable( $_POST['wp_tv_file'] ) ) {

					// get shorter version of path 
					$attr = $this->get_path_attributes( $_POST['wp_tv_file'] );

					// file not in plugins or themes directory
					if( 'wp_tv_external' === $attr['class']) {
						// use file name only
						$filename = (string)  basename( $_POST['wp_tv_file'] );
					} else {
						// part of path or full path
						$filename = $attr['path_excerpt'];

						// path name same as full path
						$filename =( $filename  === $_POST['wp_tv_file'] ) ? basename( $filename ) : $filename;
					}

					// get the file content
					// !important: use htmlspecialchars()
					$response['file'] = (string) htmlspecialchars( file_get_contents( $_POST['wp_tv_file'] ) );

					if ( !empty( $response['file'] ) ) {
						$response['success'] = true;
						$msg = sprintf( __( 'File: %s', 'wp-template-viewer' ), $filename );
					} else {
						$msg = sprintf( __( 'Could not get contents of file: %s', 'wp-template-viewer' ), $filename );
					}

				} else {
					$msg = sprintf( __( 'Could not read file: %s', 'wp-template-viewer' ), $filename );
				}
			} else {
				$msg = __( 'No file found', 'wp-template-viewer' );
			}

			$msg = '<div id ="wp_tv_code_title"><p><strong>' . $msg .'</strong></p></div>';

			if ( $response['success'] ) {
				$response['file']   = $msg . '<pre>' . $response['file'] . '</pre>';
			} else {
				$response['file'] = $msg;
			}

			$response = json_encode( $response );
			echo $response;

			exit;
		}

	} // class

	// instantiate class
	WP_TV_Template_Viewer::init();

} // class exists