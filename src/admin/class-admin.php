<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/brianhenryie/bh-wp-plugins-page
 * @since      1.0.0
 *
 * @package    BH_WP_Plugins_Page
 * @subpackage BH_WP_Plugins_Page/admin
 */

namespace BH_WP_Plugins_Page\admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    BH_WP_Plugins_Page
 * @subpackage BH_WP_Plugins_Page/admin
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class Admin {

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $pagenow;
		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		wp_enqueue_script( 'bh-wp-plugins-page', plugin_dir_url( __FILE__ ) . 'js/bh-wp-plugins-page-admin.js', array( 'jquery' ), '1.0.0', false );

	}

}
