<?php
/**
 * Uninstall WP Template Viewer
 *
 * @package     WP Template Viewer
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// remove capability 'view_wp_template_viewer' for all users
wp_tv_deactivate();