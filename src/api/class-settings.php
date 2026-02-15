<?php
/**
 * Plain-ish object returning plugin settings.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\API;

use BrianHenryIE\WP_Plugins_Page\BrianHenryIE\WP_Logger\Logger_Settings_Interface;
use BrianHenryIE\WP_Plugins_Page\BrianHenryIE\WP_Logger\Logger_Settings_Trait;
use Psr\Log\LogLevel;

/**
 * Plugin settings for Logger.
 */
class Settings implements Logger_Settings_Interface {
	use Logger_Settings_Trait;

	/**
	 * Detail of logs to record.
	 */
	public function get_log_level(): string {
		$log_level = get_option( 'bh_wp_plugins_page_log_level' );
		return is_string( $log_level ) ? $log_level : LogLevel::INFO;
	}

	/**
	 * Plugin name as displayed on the logs page.
	 */
	public function get_plugin_name(): string {
		return 'Plugins Page Cleanup';
	}

	/**
	 * The plugin's slug.
	 */
	public function get_plugin_slug(): string {
		return 'bh-wp-plugins-page';
	}

	/**
	 * The plugin basename used when determining is a log related to this plugin, and to add the logs link to the plugins page.
	 */
	public function get_plugin_basename(): string {
		return defined( 'BH_WP_PLUGINS_PAGE_BASENAME' ) && is_string( constant( 'BH_WP_PLUGINS_PAGE_BASENAME' ) )
			? (string) constant( 'BH_WP_PLUGINS_PAGE_BASENAME' ) /** @phpstan-ignore cast.string */
			: 'bh-wp-plugins-page/bh-wp-plugins-page.php';
	}

	/**
	 * The plugin version, used for JS and CSS caching.
	 */
	public function get_plugin_version(): string {
		return defined( 'BH_WP_PLUGINS_PAGE_VERSION' ) && is_string( constant( 'BH_WP_PLUGINS_PAGE_VERSION' ) )
			? (string) constant( 'BH_WP_PLUGINS_PAGE_VERSION' ) /** @phpstan-ignore cast.string */
			: '1.3.0';
	}
}
