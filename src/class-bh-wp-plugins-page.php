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
 *
 */

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\WP_Plugins_Page\Admin\Admin_Assets;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_List_Table;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_Page;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    brianhenryie/bh-wp-plugins-page
 *
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class BH_WP_Plugins_Page {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_plugins_list_table_hooks();
		$this->define_plugins_page_hooks();

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

		$plugin_admin = new Admin_Assets();
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ), PHP_INT_MAX );
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
			add_action(
				"plugin_action_links_{$plugin_basename}",
				array( $plugins_list_table, 'plugin_specific_action_links' ),
				PHP_INT_MAX,
				4
			);
		}
		add_action( 'plugin_row_meta', array( $plugins_list_table, 'row_meta' ), PHP_INT_MAX, 4 );
	}

	protected function define_plugins_page_hooks(): void {

		$plugins_page = new Plugins_Page();

		add_filter( 'wp_redirect', array( $plugins_page, 'prevent_redirect' ), 1, 2 );

		add_action( 'admin_init', array( $plugins_page, 'add_hook_for_freemius_redirect' ) );

	}
}
