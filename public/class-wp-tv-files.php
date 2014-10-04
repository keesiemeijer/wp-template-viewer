<?php
/**
 * Files
 *
 * @package     WP Tempate Viewer
 * @subpackage  Classes/Files
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * WP_TV_User Class
 *
 * This class retrieves included files for the current page.
 *
 * @since 1.0
 */
class WP_TV_Files {

	/**
	 * File paths.
	 *
	 * @access   public
	 * @since    1.0
	 * @var      array
	 */
	public $files;

	/**
	 * Theme Paths.
	 *
	 * @access   public
	 * @since    1.0
	 * @var      array
	 */
	public $theme_files;

	/**
	 * WordPress theme and and plugin directories.
	 *
	 * @access   public
	 * @since    1.0
	 * @var      array
	 */
	public $directories;

	/**
	 * Allowed file types for included files.
	 *
	 * @access   public
	 * @since    1.0
	 * @var      array
	 */
	public $file_types;


	/**
	 * Get things going
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'setup_files' ) );
	}


	/**
	 * Sets private class properties.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function setup_files() {

		// Allowed file types.
		$this->file_types  = $this->get_allowed_file_types();

		// Plugin and theme directories.
		$this->directories = $this->get_directories();

	}


	/**
	 * Returns allowed file types.
	 *
	 * @access private
	 * @since 1.0
	 * @return array Array with allowed file types.
	 */
	private function get_allowed_file_types() {

		/**
		 * Filter allowed file types.
		 * file extension => language attribute
		 *
		 * @param array   $file_types Array with lower case file extensions. Empty array for any file type.
		 */
		return (array) apply_filters( 'wp_template_viewer_file_types',
			array(
				'php'  => 'php',
				'js'   => 'js',
				'css'  => 'css',
				'html' => 'html',
				'htm'  => 'html',
			) );
	}


	/**
	 * Returns an array with WordPress theme and plugin directories.
	 *
	 * @access private
	 * @since 1.0
	 * @return array Array with WordPress theme and plugin directiories
	 */
	private function get_directories() {

		return array(
			'theme'          => wp_get_theme(),
			'stylesheet_dir' => trailingslashit( get_stylesheet_directory() ),
			'template_dir'   => trailingslashit( get_template_directory() ),
			'theme_root_dir' => trailingslashit( get_theme_root() ),
			'plugins_dir'    => defined( 'WP_PLUGIN_DIR' ) ? trailingslashit( WP_PLUGIN_DIR ) : '',
		);

	}


	/**
	 * Gets all included file paths for the current page.
	 *
	 * @access public
	 * @since 1.0
	 * @return array Array with included file paths.
	 */
	public function get_files() {

		$files = array();
		foreach ( (array) get_included_files() as $file ) {

			$file_type = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

			if ( empty( $this->file_types ) || in_array( $file_type, $this->file_types ) ) {
				$files[] = $file;
			}
		}

		return $files;
	}


	/**
	 * Returns all the included theme file paths for the current page.
	 * Allows for adding other file paths with a filter.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array with included theme template paths.
	 */
	public function get_theme_files() {

		$files = $this->get_files();

		$templates = array();

		foreach ( (array) $files as $file ) {
			$included = false;

			// child and parent theme
			if ( 0 === strpos( $file, trailingslashit( $this->directories['stylesheet_dir'] ) ) ) {
				$templates[] = $file;
				$included    = true;
			}

			// parent theme
			if ( 0 === strpos( $file, trailingslashit( $this->directories['template_dir'] ) ) ) {
				$templates[] = $file;
				$included    = true;
			}

			/**
			 * This filter alows you to include files outside the current theme's directory.
			 *
			 * @param bool    $included File was included or not.
			 */
			$include = apply_filters( 'wp_template_viewer_include_file', $included, $file ) ;

			if ( (bool) $include ) {
				$templates[] = $file;
			}
		}

		return array_values( array_unique( $templates ) );
	}


	/**
	 * Returns css classes based upon the file path.
	 *
	 * @access public
	 * @since 1.0
	 * @param string  $file File path;
	 * @return string Classes.
	 */
	public function get_file_class( $file ) {

		global $template;
		$class = '';

		// Check if path starts with themes directory.
		if ( 0 === strpos( $file, $this->directories['theme_root_dir']  ) ) {
			$class .= ' wp_tv_theme';
		}

		// Check if current file is a child theme file
		if ( 0 === strpos( $file, $this->directories['stylesheet_dir']  ) ) {
			$class .= is_child_theme() ? ' wp_tv_child' : '';
		}

		// Check if path is current theme template path
		if ( $file === $template ) {
			$class .= ' wp_tv_current';
		}

		// Check if path starts with plugins directory.
		if ( 0 === strpos( $file, $this->directories['plugins_dir']  ) ) {
			$class .= ' wp_tv_plugin';
		}

		return !empty( $class ) ? trim(  $class ) : 'wp_tv_external';
	}


	/**
	 * Returns a trimmed path for theme and plugin files.
	 *
	 * @access public
	 * @since 1.0
	 * @param string  $file File path;
	 * @return string Trimmed file path.
	 */
	public function get_trimmed_file_path( $file ) {

		$excerpt = '';

		// Check if the file path starts with the themes directory.
		if ( 0 === strpos( $file, $this->directories['theme_root_dir'] ) ) {
			$theme_path = str_replace( dirname(  $this->directories['theme_root_dir']  ), '', $file );
			$excerpt    =  '/' . trim( esc_attr( $theme_path ), '/ ' );
		}

		// Check if the file path starts with the plugins directory.
		if ( 0 === strpos( $file, $this->directories['plugins_dir'] ) ) {
			$plugin_path = str_replace( dirname( $this->directories['plugins_dir'] ), '', $file );
			$excerpt     = '/' . trim( esc_attr( $plugin_path ), '/ ' );
		}

		return $excerpt = !empty( $excerpt ) ? $excerpt : esc_attr( $file );
	}


	/**
	 * Returns attributes based upon the file path.
	 *
	 * @access public
	 * @since 1.0
	 * @param string  $path Path.
	 * @return array Array with class and trimmed path.
	 */
	function get_file_attributes( $file ) {
		return array(
			'class' => $this->get_file_class( $file ),
			'path'  => $this->get_trimmed_file_path( $file ),
		);
	}

}