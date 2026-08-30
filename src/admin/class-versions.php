<?php
/**
 * Keep a record of installed plugin versions' install dates. And the first date updates became available.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

use BrianHenryIE\WP_Plugins_Page\API\API;
use stdClass;

/**
 * Record the available and installed versions of plugins as they are seen, and make that data available to the frontend JS.
 */
class Versions {

	/**
	 * Record each time a plugin version changes.
	 *
	 * Check all installed plugins' versions and record the date if the version has changed.
	 *
	 * Checking all versions on every request would be wasteful. So we have an hourly cron, and also run it when
	 * plugins.php is loaded.
	 */
	public function record_installed_versions(): void {

		/**
		 * For each plugin, get each version change, dated.
		 *
		 * By using array pairs (tuples), we can handle plugin downgrades too.
		 *
		 * @var array<string, array<array<string, string>>> $installed_plugin_versions Pairs of date,version keyed by basename.
		 */
		$installed_plugin_versions = get_option( API::PLUGINS_PAGE_INSTALL_DATES_OPTION_NAME, array() );

		$updated = false;
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_basename => $plugin_data ) {
			if ( ! isset( $installed_plugin_versions[ $plugin_basename ] ) ) {
				$installed_plugin_versions[ $plugin_basename ] = array();
			}
			if ( 0 === count( $installed_plugin_versions[ $plugin_basename ] ) ) {
				continue;
			}

			/**
			 * Get the date and version of the last plugin version change.
			 *
			 * @var array<string, string> $last_change_date_version_pair Pair of date and plugin version.
			 */
			$last_change_date_version_pair = array_pop( $installed_plugin_versions[ $plugin_basename ] );
			$last_change_version           = array_pop( $last_change_date_version_pair );

			if ( $plugin_data['Version'] !== $last_change_version ) {
				$installed_plugin_versions[ $plugin_basename ][] = array( gmdate( 'Y-m-d' ) => $plugin_data['Version'] );
				$updated = true;
			}
		}

		if ( $updated ) {
			update_option( API::PLUGINS_PAGE_INSTALL_DATES_OPTION_NAME, $installed_plugin_versions, false );
		}
	}

	/**
	 * Record each time a plugin update becomes available.
	 *
	 * Run on an hourly cron to record when plugin updates first became available.
	 */
	public function record_available_versions(): void {

		/**
		 * Lists of plugin version changes.
		 *
		 * @var array<string, array<string,string>> $updated_dates Array, keyed by plugin basename, of pairs of <yyyy-mm-dd,version> when the plugin version changed.
		 */
		$updated_dates = get_option( API::PLUGINS_PAGE_UPDATES_DATES_OPTION_NAME, array() );

		/**
		 * The plugin update data (which drives the yellow boxes on plugins.php).
		 *
		 * @see wp_update_plugins()
		 * @see wp-includes/update.php
		 *
		 * @var stdClass{last_checked:string,response:array,translations:array,no_update:array,checked:array<string,string>} $plugin_updates
		 */
		$plugin_updates = get_site_transient( 'update_plugins' );

		$updated = false;

		foreach ( $plugin_updates->response as $plugin_basename => $values ) {

			$version = $values['new_version'];

			if ( ! isset( $updated_dates[ $plugin_basename ] ) ) {
				$updated_dates[ $plugin_basename ] = array();
			}
			if ( ! isset( $updated_dates[ $plugin_basename ][ $version ] ) ) {
				$updated_dates[ $plugin_basename ][ $version ] = gmdate( 'Y-m-d' );
				$updated                                       = true;
			}
		}

		if ( $updated ) {
			update_option( API::PLUGINS_PAGE_UPDATES_DATES_OPTION_NAME, $updated_dates );
		}
	}

	/**
	 * Add data for JS display.
	 *
	 * TODO: Hook late on admin_enqueue_scripts so the handle is already registered.
	 *
	 * @hooked admin_enqueue_scripts
	 */
	public function enqueue_script(): void {
		$plugins_installed_versions = get_option( API::PLUGINS_PAGE_INSTALL_DATES_OPTION_NAME, array() );
		$plugins_updates_dates      = get_option( API::PLUGINS_PAGE_UPDATES_DATES_OPTION_NAME, array() );

		$data = wp_json_encode(
			array(
				'plugins_installed_versions' => $plugins_installed_versions,
				'plugins_updates_dates'      => $plugins_updates_dates,
			)
		) ?: '';

		wp_add_inline_script( 'bh-wp-plugins-page', $data, 'before' );
	}
}
