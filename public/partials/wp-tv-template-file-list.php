<?php
/**
 * Markup for a list of files.
 *
 * @package     WP Tempate Viewer
 * @subpackage  Public/Partials
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
?>

<ul class="ab-submenu" id="wp_tv_file_list<?php echo $footer ?>" <?php echo $display; ?>>
<?php
foreach ( $files as $key => $file ) :
	$path_attr = $file_obj->get_file_attributes( $file );
?>
	<li class="<?php echo $path_attr['class']; ?>">
		<span class="wp_tv_path ab-item ab-empty-item" data-wp_tv_path="<?php echo esc_attr( $file ); ?>">
			<?php  echo $path_attr['path']; ?>
		</span>
	</li>
<?php endforeach; ?>
</ul>