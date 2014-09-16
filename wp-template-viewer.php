<?php
/**  
 * Plugin Name: WP Template Viewer
 * Plugin URI: https://keesiemeijer.wordpress.com/wp-template-viewer
 * Description: Display the content of theme template files in use for the current page by clicking a link in the toolbar.
 * Author: keesiemijer
 * Author URI:
 * License: GPL v2
 * Author URI:
 * Version: 1.0-beta1
 * Text Domain: wp-template-viewer
 * Domain Path: languages
 *
 * WP Template Viewer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP Template Viewer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Template Viewer. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WP Template Viewer
 * @category Core
 * @author Kees Meijer
 * @version 1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Template_Viewer' ) ) :

	/**
	 * Main WP_Template_Viewer Class
	 *
	 * @since 1.0
	 */
	final class WP_Template_Viewer {
	/** Singleton *************************************************************/

	/**
	 * @var WP_Template_Viewer The one true WP_Template_Viewer
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * WP_Template_Viewer User Object
	 *
	 * @var object
	 * @since 1.0
	 */
	public $user;

	/**
	 * WP_Template_Viewer Files Object
	 *
	 * @var object
	 * @since 1.0
	 */
	public $files;


	/**
	 * Main WP_Template_Viewer Instance
	 *
	 * Insures that only one instance of WP_Template_Viewer exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @uses WP_Template_Viewer::setup_constants() Setup the constants needed
	 * @uses WP_Template_Viewer::includes() Include the required files
	 * @uses WP_Template_Viewer::load_textdomain() load the language files
	 * @see wp_template_viewer()
	 * @return The one true WP_Template_Viewer
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Template_Viewer ) ) {
			self::$instance = new WP_Template_Viewer;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->load_textdomain();
			if ( !is_admin() ) {
				self::$instance->user  = new WP_TV_User();
			}
			self::$instance->files = new WP_TV_Files();
		}

		return self::$instance;
	}


	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-template-viewer' ), '1.6' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-template-viewer' ), '1.6' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version
		if ( ! defined( 'WP_TV_VERSION' ) ) {
			define( 'WP_TV_VERSION', '1.0' );
		}

		// Plugin Folder Path
		if ( ! defined( 'WP_TV_PLUGIN_DIR' ) ) {
			define( 'WP_TV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'WP_TV_PLUGIN_URL' ) ) {
			define( 'WP_TV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'WP_TV_PLUGIN_FILE' ) ) {
			define( 'WP_TV_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		if ( !is_admin() ) {
			// required files for the public-facing site

			require_once WP_TV_PLUGIN_DIR . 'public/class-wp-tv-user.php';
			require_once WP_TV_PLUGIN_DIR . 'public/class-wp_tv_view.php';
			require_once WP_TV_PLUGIN_DIR . 'includes/scripts.php';
		}

		require_once WP_TV_PLUGIN_DIR . 'public/class-wp-tv-files.php';
		require_once WP_TV_PLUGIN_DIR . 'includes/ajax-functions.php';

		require_once WP_TV_PLUGIN_DIR . 'includes/install.php';
	}


	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {
		$dir = dirname( plugin_basename( WP_TV_PLUGIN_FILE ) ) . '/languages/';
		load_plugin_textdomain( 'wp-template-viewer', '', $dir );
	}

}

endif; // End if class_exists check


/**
 * The main function responsible for returning the one true WP_Template_Viewer
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $wp_template_viewer = WP_TV(); ?>
 *
 * @since 1.4
 * @return object The one true WP_Template_Viewer Instance
 */
function WP_TV() {
	return WP_Template_Viewer::instance();
}

// Get WP Template Viewer Running
WP_TV();
