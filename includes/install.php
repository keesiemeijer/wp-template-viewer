<?php
/**
 * Install Functions
 *
 * @package     Related Posts by Taxonomy
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
 * The main function responsible for displaying files and file content
 * in the public-facing site's footer and admin bar.
 *
 * @since 1.0
 * @return void
 */
function wp_tv_install() {
	if ( !is_admin() ) {
		WP_TV_View::init();
	}
}

wp_tv_install();


/**
 * Fired during plugin activation.
 *
 * Adds the capability 'view_wp_template_viewer' to the 'administrator' role
 *
 * @since 1.0
 * @return void
 */
function wp_tv_activate() {
	$role = get_role( 'administrator' );
	if ( !empty( $role ) ) {
		$role->add_cap( 'view_wp_template_viewer' );
	}

}

register_activation_hook( WP_TV_PLUGIN_FILE, 'wp_tv_activate' );


/**
 * Fired during plugin deactivation.
 *
 * Removes the capability 'view_wp_template_viewer' from all roles
 *
 * @since 1.0
 * @return void
 */
function wp_tv_deactivate() {
	global $wp_roles;
	foreach ( array_keys( $wp_roles->roles ) as $role ) {
		$wp_roles->remove_cap( $role, 'view_wp_template_viewer' );
	}
}

register_deactivation_hook( WP_TV_PLUGIN_FILE, 'wp_tv_deactivate' );