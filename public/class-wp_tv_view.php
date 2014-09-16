<?php
/**
 * Files
 *
 * @package     WP Tempate Viewer
 * @subpackage  Classes/View
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * WP_TV_View Class
 *
 * This class displays files and file content in the toolbar and footer.
 *
 * @since 1.0
 */
class WP_TV_View {
	/** Singleton *************************************************************/

	/**
	 * Class instance.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      object
	 */
	private static $instance = null;

	/**
	 * WP_Template_Viewer object.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      object
	 */
	private $viewer;

	/**
	 * Array with file paths.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      array
	 */
	private $files;

	/**
	 * Display files in the footer.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      bool
	 */
	private $footer_display = false;


	/**
	 * Acces the WP_TV_View instance.
	 *
	 * @access public
	 * @since 1.0
	 * @return object
	 */
	public static function get_instance() {
		// create a new object if it doesn't exist.
		is_null( self::$instance ) && self::$instance = new self;
		return self::$instance;
	}


	/**
	 * Initialize class when the wp_loaded action is run.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_loaded', array( self::get_instance(), 'setup' ), 20 );
	}

	/**
	 * Get things going
	 *
	 * @since 1.0
	 */
	public function __construct() {}


	/**
	 * Sets up template viewer for authorized users.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function setup() {

		$this->viewer = WP_TV();
		$this->footer_display = $this->viewer->user->is_footer_display();

		if ( $this->viewer->user->is_authorized_user() ) {
			add_action( 'wp_footer', array( $this, 'footer' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'before_admin_bar_render' ) );
		}
	}


	/**
	 * Returns included files for the current page.
	 *
	 * @access public
	 * @since 1.0
	 * @return array Array with file paths.
	 */
	public function get_files() {
		if ( empty( $this->files ) ) {
			$this->files = $this->viewer->files->get_theme_files();
		}
		return $this->files;
	}


	/**
	 * Displays files and file content in the footer.
	 * inludes partial wp-tv-template-footer.php
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function footer() {

		// get included files
		$files = $this->get_files();

		// display header
		$display = !$this->footer_display ? ' style="display:none;"' : '';

		// list of files
		$file_list = $this->file_list( $files, true );

		// add notice if no files were found
		$fail = '';
		if ( empty( $files ) ) {
			$fail = ': ' . __( 'No files found', 'wp-template-viewer' );
		}

		// Include html footer template.
		include_once  'partials/wp-tv-template-footer.php';
	}


	/**
	 * Returns html list with files.
	 * Inludes partial wp-tv-template-file-list.php
	 *
	 * @access public
	 * @since 1.0
	 * @param array $templates Array with file paths.
	 * @param boolean $footer Show list in footer or not.
	 * @return string Html list with file paths.
	 */
	function file_list( $files, $footer = false ) {

		if ( empty( $files ) ) {
			return;
		}

		$file_obj = $this->viewer->files;
		$display  = ( !$this->footer_display && $footer ) ? ' style="display:none;"' : '';
		$footer   = $footer ? '_footer' : '';

		ob_start();
		include 'partials/wp-tv-template-file-list.php';

		return ob_get_clean();
	}


	/**
	 * Displays files in the toolbar (admin bar).
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function before_admin_bar_render() {
		global $wp_admin_bar, $template;

		if ( !is_admin_bar_showing() || $this->footer_display ) {
			return;
		}

		// get included files
		$files = $this->get_files();

		$theme_template = !empty( $template ) ? basename( $template ) : __( 'Not Found', 'wp-template-viewer' );

		$args = array(
			'id'    => 'wp_template_viewer_plugin',
			'title' => sprintf( __( 'Template: %s', 'wp-template-viewer' ), $theme_template ),
		);

		// Fo sho, that's a top level toolbar node. Top level yo!
		$wp_admin_bar->add_node( $args );

		$args['parent'] = 'wp_template_viewer_plugin';
		$args['id'] = 'wp_tv_current_theme_group';
		$wp_admin_bar->add_group( $args );

		if ( !empty( $files ) ) {

			$args['id']   = 'wp_tv_template_files_group';
			$args['meta']['class'] = 'ab-sub-secondary';
			$wp_admin_bar->add_group( $args );
			unset( $args['meta'] );

			$args['parent'] = 'wp_tv_current_theme_group';
			$args['id']     = 'wp_tv_current_theme';
			$args['title']  = sprintf( __( 'Current Theme: %s', 'wp-template-viewer' ), $this->viewer->files->directories['theme'] );
			$wp_admin_bar->add_node( $args );

			$args['id']     = 'wp_tv_footer_toggle';
			$args['title']  = '<span class="wp_tv_toggle">' .__( 'show files in footer', 'wp-template-viewer' ) . '</span>';
			$args['meta']['class'] = 'wp_tv_no_js'; // changed to wp_tv_js by Javascript
			$wp_admin_bar->add_node( $args );

			$args['parent']        = 'wp_tv_template_files_group';
			$args['id']            = 'wp_tv_template_files';
			$args['title']         = __( 'Included Files:', 'wp-template-viewer' );
			$args['meta']['class'] = 'wp_tv_no_js'; // changed to wp_tv_js by Javascript
			$args['meta']['html']  = $this->file_list( $files );
			$wp_admin_bar->add_node( $args );

		} else {

			$args['parent'] = 'wp_tv_current_theme_group';
			$args['id']     = 'wp_tv_current_theme';
			$args['title']  =  __( 'No files found', 'wp-template-viewer' );
			$wp_admin_bar->add_node( $args );
		}
	}

}