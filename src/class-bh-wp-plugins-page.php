<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that WP_Includes attributes and functions used across both the
 * frontend-facing side of the site and the admin area.
 *
 * @link       https://github.com/brianhenryie/bh-wp-plugins-page
 * @since      1.0.0
 *
 * @package    brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\WP_Plugins_Page\Admin\Admin_Assets;
use BrianHenryIE\WP_Plugins_Page\Admin\AJAX;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_List_Table;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_Page;
use BrianHenryIE\WP_Plugins_Page\Admin\Updates;
use BrianHenryIE\WP_Plugins_Page\API\API;
use BrianHenryIE\WP_Plugins_Page\API\Settings;
use BrianHenryIE\WP_Plugins_Page\WP_Includes\I18n;
use Psr\Log\LoggerInterface;

/**
 * Hooks the plugin's classes to WordPress's actions and filters.
 */
class BH_WP_Plugins_Page {

	/**
	 * Wire up actions and filters for the plugin.
	 *
	 * @param Settings        $settings The plugin settings.
	 * @param API             $api Some main plugin functions.
	 * @param LoggerInterface $logger A PSR logger.
	 */
	public function __construct(
		protected Settings $settings,
		protected API $api,
		protected LoggerInterface $logger
	) {
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_plugins_list_table_hooks();
		$this->define_plugins_list_table_zip_download_hooks();
		$this->define_plugins_page_hooks();
		$this->define_ajax_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	protected function set_locale(): void {

		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected function define_admin_hooks(): void {

		$plugin_admin = new Admin_Assets( $this->settings );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ), 9999 );
	}

	/**
	 * Hooks for the plugins list table.
	 * Adds the generic plugin_action_links and plugin_row_meta actions and a specific action for each active plugin.
	 *
	 * @since    1.0.4
	 */
	protected function define_plugins_list_table_hooks(): void {

		$plugins_list_table = new Plugins_List_Table();
		$active_plugins     = (array) get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin_basename ) {
			add_filter(
				"plugin_action_links_{$plugin_basename}",
				array( $plugins_list_table, 'plugin_specific_action_links' ),
				9999,
				4
			);
		}
		add_filter( 'plugin_row_meta', array( $plugins_list_table, 'row_meta' ), 9999, 4 );

		add_filter( 'all_plugins', array( $plugins_list_table, 'edit_plugins_array' ) );
	}

	/**
	 * Define hooks for adding download links to plugins.php list table.
	 */
	protected function define_plugins_list_table_zip_download_hooks(): void {

		$updates        = new Updates();
		$active_plugins = (array) get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin_basename ) {
			add_action(
				"in_plugin_update_message-{$plugin_basename}",
				array( $updates, 'add_zip_download_link' ),
				10,
				2
			);
		}
	}

	/**
	 * Add hooks to prevent unwanted redirects when plugins are installed.
	 */
	protected function define_plugins_page_hooks(): void {

		$plugins_page = new Plugins_Page();

		add_filter( 'wp_redirect', array( $plugins_page, 'prevent_redirect' ), 1, 2 );

		$active_plugins = (array) get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin_basename ) {
			[$plugin_slug] = explode( '/', $plugin_basename );
			add_filter( "fs_redirect_on_activation_{$plugin_slug}", '__return_false' );
		}
	}

	/**
	 * Add hook to handle AJAX requests.
	 */
	protected function define_ajax_hooks(): void {

		$ajax = new AJAX( $this->api, $this->logger );

		add_action( 'wp_ajax_bh_wp_plugins_page_set_plugin_name', array( $ajax, 'set_plugin_name' ) );
	}
}
