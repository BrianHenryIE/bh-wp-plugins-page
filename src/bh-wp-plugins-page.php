<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/brianhenryie/bh-wp-plugins-page
 * @since             1.0.0
 * @package           BH_WP_Plugins_Page
 *
 * @wordpress-plugin
 * Plugin Name:       Plugins Page Cleanup
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wp-plugins-page/
 * Description:       Removes formatting and up-sells, and moves Settings links to the beginning and Deactivate links to the end of plugins.php action links. Disables plugin deactivation surveys.
 * Version:           1.0.3
 * Author:            BrianHenryIE
 * Author URI:        https://BrianHenry.ie
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wp-plugins-page
 * Domain Path:       /languages
 */

namespace BH_WP_Plugins_Page;

use BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WP_PLUGINS_PAGE_VERSION', '1.0.3' );

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

	new BH_WP_Plugins_Page();

}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
instantiate_bh_wp_plugins_page();

