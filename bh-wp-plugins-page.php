<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also WP_Includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/brianhenryie/bh-wp-plugins-page
 * @since             1.0.0
 * @package           brianhenryie/bh-wp-plugins-page
 *
 * @wordpress-plugin
 * Plugin Name:       Plugins Page Cleanup
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wp-plugins-page/
 * Description:       Removes formatting and up-sells, and moves Settings links to the beginning and Deactivate links to the end of plugins.php action links. Disables plugin deactivation surveys.
 * Version:           1.1.1
 * Requires PHP:      8.0
 * Author:            BrianHenryIE
 * Author URI:        https://BrianHenry.ie
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wp-plugins-page
 * Domain Path:       /languages
 */

namespace BrianHenryIE\WP_Plugins_Page;

// If this file is called directly, abort.
use BrianHenryIE\WP_Plugins_Page\BrianHenryIE\WP_Logger\Logger;
use BrianHenryIE\WP_Plugins_Page\BrianHenryIE\WP_Logger\Logger_Settings_Interface;
use Psr\Log\LogLevel;

if ( ! defined( 'WPINC' ) ) {
	throw new \Exception( 'WordPress required but not loaded.' );
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WP_PLUGINS_PAGE_VERSION', '1.1.1' );
define( 'BH_WP_PLUGINS_PAGE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wp_plugins_page(): void {

	// If we're not in the Admin UI, we have nothing to do.
	if ( ! is_admin() ) {
		return;
	}

	Logger::instance( new class() implements Logger_Settings_Interface {

		public function get_log_level(): string {
			return get_option( 'bh_wp_plugins_page_log_leve', LogLevel::INFO );
		}

		public function get_plugin_name(): string {
			return 'Plugins Page Cleanup';
		}

		public function get_plugin_slug(): string {
			return 'bh-wp-plugins-page';
		}

		public function get_plugin_basename(): string {
			return 'bh-wp-plugins-page/bh-wp-plugins-page.php';
		}
	});

	new BH_WP_Plugins_Page( $logger );

}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
instantiate_bh_wp_plugins_page();


