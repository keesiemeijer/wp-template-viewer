=== WP Template Viewer ===
Contributors: keesiemeijer
Tags: template,theme template,plugin template,template files,included files,file content,files,content
Requires at least: 3.9
Tested up to: 3.9
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin displays all theme template file names in use for the current page. Look at the content of a file by clicking its name.   

 == Description ==

Ever wanted to take a quick look at a theme template file without opening a text editor? Or wondered what template files were used to display the current page? 

This plugin displays all theme template file names in use for the current page in a [toolbar menu](http://codex.wordpress.org/Toolbar). The file names are shown in the order they were included. The content of the file will be displayed in the footer of your site by clicking a file name in the toolbar.

By default, only admins and super admins have access to the toolbar menu and file content.

Note: Display of file content only works if the current theme follows the recommended practice of calling the [wp_footer()](http://codex.wordpress.org/Function_Reference/wp_footer) template tag (most theme's do).

By using filters you can:

* Allow specific users to use the plugin.
* Also include plugin files.
* Show the file names in the footer instead of the toolbar.

Filter documentation is comming shortly...

== Installation ==
* Unzip the <code>wp-template-viewer.zip</code> folder.
* Upload the <code>wp-template-viewer</code> folder to your <code>/wp-content/plugins</code> directory.
* Activate *wp-template-viewer*.
* That's it, now you are ready to use the widget and shortcode