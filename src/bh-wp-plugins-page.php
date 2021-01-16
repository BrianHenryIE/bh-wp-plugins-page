<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           BH_WP_Plugins_Page
 *
 * @wordpress-plugin
 * Plugin Name:       BH WP Plugins Page
 * Plugin URI:        http://github.com/username/bh-wp-plugins-page/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            BrianHenryIE
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wp-plugins-page
 * Domain Path:       /languages
 */

namespace BH_WP_Plugins_Page;

use BH_WP_Plugins_Page\includes\Activator;
use BH_WP_Plugins_Page\includes\Deactivator;
use BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page;
use BH_WP_Plugins_Page\BrianHenryIE\WPPB\WPPB_Loader;

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
define( 'BH_WP_PLUGINS_PAGE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function activate_bh_wp_plugins_page() {

	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_bh_wp_plugins_page() {

	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'BH_WP_Plugins_Page\activate_bh_wp_plugins_page' );
register_deactivation_hook( __FILE__, 'BH_WP_Plugins_Page\deactivate_bh_wp_plugins_page' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wp_plugins_page() {

	$loader = new WPPB_Loader();
	$plugin = new BH_WP_Plugins_Page( $loader );

	return $plugin;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
$GLOBALS['bh_wp_plugins_page'] = $bh_wp_plugins_page = instantiate_bh_wp_plugins_page();
$bh_wp_plugins_page->run();
