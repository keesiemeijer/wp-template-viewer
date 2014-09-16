<?php
/**
 * User
 *
 * @package     WP Tempate Viewer
 * @subpackage  Classes/User
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * WP_TV_User Class
 *
 * This class handles the capabilities for the current user.
 *
 * @since 1.0
 */
class WP_TV_User {

	/**
	 * Is the current user is logged in.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      bool
	 */
	private $user_logged_in = false;

	/**
	 * Does the current user have the custom capability to use this plugin.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      bool
	 */
	private $user_has_capability = false;

	/**
	 * Is the current user a super admin.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      bool
	 */
	private $user_is_super_admin = false;

	/**
	 * Current user id.
	 *
	 * @access   private
	 * @since    1.0
	 * @var      int
	 */
	private $current_user_id = 0;


	/**
	 * Get things going.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'setup_current_user' ) );
	}


	/**
	 * Sets up the current user's private properties.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function setup_current_user() {

		$this->user_logged_in = is_user_logged_in();

		if ( $this->user_logged_in ) {
			$this->current_user_id     = get_current_user_id();
			$this->user_is_super_admin = is_super_admin();
			$this->user_has_capability = current_user_can( 'view_wp_template_viewer' );
		}
	}


	/**
	 * Verifies if the current user is authorized to use this plugin.
	 * Authorized by default: admins, super admins and users with the capability 'view_wp_template_viewer'.
	 *
	 * Specific users can be authorized to use this plugin with the filters:
	 *     wp_template_viewer_authorized_user_id
	 *     wp_template_viewer_is_authorized_user
	 *
	 *
	 * @access public
	 * @since  1.0
	 * @return bool True if the user is authorized to use this plugin.
	 */
	public function is_authorized_user() {

		$authorized  = false;

		if ( $this->user_logged_in ) {

			/**
			 * Authorize a user with a specific id to use this plugin.
			 * By default no user ids are authorized.
			 *
			 * @since 1.0
			 * @param int     $user_id User ID. Default 0.
			 */
			$user_id = apply_filters( 'wp_template_viewer_authorized_user_id', 0 );
			$authorized_user_id = $this->is_current_user_id( $user_id );

			// Grant access to admins, super admins or users with the capability or authorized user id.
			if ( $this->user_is_super_admin || $this->user_has_capability || $authorized_user_id ) {
				$authorized = true;
			}
		}


		/**
		 * Is the current user authorized to use this plugin.
		 *
		 * @since 0.1
		 * @param bool    $authorized True if the current user can use this plugin. Default none.
		 */
		return (bool) apply_filters( 'wp_template_viewer_is_authorized_user', $authorized, $this->current_user_id );
	}


	/**
	 * Verifies if files should be displayed in the footer for the current user.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool True if files should be displayed in footer.
	 */
	public function is_footer_display() {
		$in_footer = false;

		/**
		 * Display files in footer for logged out authorized users (set with a filter).
		 * See is_authorized_user()
		 */
		if ( !$this->user_logged_in && $this->is_authorized_user() ) {
			$in_footer = true;
		}

		/**
		 * Display files in footer.
		 *
		 * @since 0.1
		 * @param bool    $in_footer Show files in footer or not. Default none.
		 */
		return (bool) apply_filters( 'wp_template_viewer_in_footer', $in_footer );
	}


	/**
	 * Verifies the current user id.
	 *
	 * @access public
	 * @since  1.0
	 * @param int     $user_id User id;
	 * @return bool True if the current user has the user id. Default is false.
	 */
	public function is_current_user_id( $user_id = 0 ) {

		// Allow positive integers only for user ids.
		if ( absint( $user_id ) ) {

			// Current user id is 0 if not logged in
			if ( $user_id === $this->current_user_id ) {
				return true;
			}
		}

		return false;
	}
}