<?php
/**
 * Markup for the file content.
 *
 * @package     WP Tempate Viewer
 * @subpackage  Public/Partials
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
?>
<div id="wp_tv_template_viewer" class="wp_tv_no_js">

	<div class="wp_tv_files" <?php echo $display;?>>
		<p>
			<span class="wp_tv_close" aria-label="Close">&times;</span>
			<strong><?php echo __( 'WP Template Viewer', 'wp-template-viewer' ) . $fail; ?></strong>
		</p>
		<p>
<?php
			if ( !$this->footer_display ) {
				$show_files = '<span class="wp_tv_toggle">' . __( 'show files', 'wp-template-viewer' ) . '</span>';
				printf( __( 'Current Theme: %1$s - %2$s', 'wp-template-viewer' ), $this->viewer->files->directories['theme'], $show_files );
			} else {
				printf( __( 'Current Theme: %1$s', 'wp-template-viewer' ), $this->viewer->files->directories['theme'] );
			}
?>
		</p>
		<?php echo $file_list; ?>
	</div>
</div>