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

use BrianHenryIE\WP_Plugins_Page\API\API;
use BrianHenryIE\WP_Plugins_Page\API\Settings;

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
	 * Constructor.
	 *
	 * @param Settings $settings The plugin settings.
	 */
	public function __construct(
		protected Settings $settings
	) {
	}

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

		/** @var string $plugin_basename */
		$plugin_basename = defined( 'BH_WP_PLUGINS_PAGE_BASENAME' ) && is_string( constant( 'BH_WP_PLUGINS_PAGE_BASENAME' ) )
			? constant( 'BH_WP_PLUGINS_PAGE_BASENAME' )
			: 'bh-wp-plugins-page/bh-wp-plugins-page.php';
		$js_url          = plugins_url( 'assets/bh-wp-plugins-page-admin.js', $plugin_basename );
		$css_url         = plugins_url( 'assets/bh-wp-plugins-page-admin.css', $plugin_basename );
		$version         = (string) ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : $this->settings->get_plugin_version() );

		wp_enqueue_script( 'bh-wp-plugins-page', $js_url, array( 'jquery' ), $version, true );
		$ajax_data      = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( AJAX::class ),
		);
		$ajax_data_json = wp_json_encode( $ajax_data, JSON_PRETTY_PRINT );

		$changes = get_option( API::PLUGINS_PAGE_CHANGES_OPTION_NAME, array() );

		$bh_wp_plugins_page_changes = wp_json_encode( $changes );

		$script = <<<EOD
var bh_wp_plugins_page_ajax_data = $ajax_data_json;
var bh_wp_plugins_page_changes = $bh_wp_plugins_page_changes;
EOD;

		wp_add_inline_script(
			'bh-wp-plugins-page',
			$script,
			'before'
		);

		wp_enqueue_style( 'bh-wp-plugins-page', $css_url, array(), $version );
	}
}
