<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/brianhenryie/bh-wp-plugins-page
 * @since      1.0.0
 *
 * @package    brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    brianhenryie/bh-wp-plugins-page
 *
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class Admin_Assets {

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {

		global $pagenow;
		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		$plugin_dir_url = defined( 'BH_WP_PLUGINS_PAGE_BASENAME' ) ? BH_WP_PLUGINS_PAGE_BASENAME : 'bh-wp-plugins-page/bh-wp-plugins-page.php';
		$js_url         = plugin_dir_url( $plugin_dir_url ) . 'js/bh-wp-plugins-page-admin.js';
		$version        = defined( 'BH_WP_PLUGINS_PAGE_VERSION' ) ? BH_WP_PLUGINS_PAGE_VERSION : '1.0.9';

		wp_enqueue_script( 'bh-wp-plugins-page', $js_url, array( 'jquery' ), $version, false );

	}

}
