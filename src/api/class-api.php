<?php
/**
 * Function to save the updated plugin title.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\API;

use Exception;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Saves changes in wp_options which are merged with WordPress's `get_plugins()`.
 */
class API {
	use LoggerAwareTrait;

	const PLUGINS_PAGE_CHANGES_OPTION_NAME       = 'bh_wp_plugins_page_changes';
	const PLUGINS_PAGE_UPDATES_DATES_OPTION_NAME = 'bh_wp_plugins_page_plugin_update_available_dates';
	const PLUGINS_PAGE_INSTALL_DATES_OPTION_NAME = 'bh_wp_plugins_page_plugin_install_dates';

	/**
	 * Constructor.
	 *
	 * @param LoggerInterface $logger A PSR logger.
	 */
	public function __construct( LoggerInterface $logger ) {
		$this->setLogger( $logger );
	}

	/**
	 * Save the desired plugin title.
	 *
	 * Also save the original title.
	 *
	 * @param string $plugin_basename The plugin whose title is being updated.
	 * @param string $new_name The name to set, or empty to reset to the original.
	 *
	 * @return array{updated:bool,plugin_basename:string,saved_before:array{Name:string,Original_Name:string}|null,saved_after:array{Name?:string,Original_Name?:string}|null,plugin_name:string}
	 * @throws Exception When the plugin whose title is being updated is not found in WordPress's plugin list.
	 */
	public function set_plugin_name( string $plugin_basename, string $new_name ): array {
		/** @var array<string,array{Name:string}&array<string,string>> $plugins */
		$plugins = get_plugins();

		if ( ! isset( $plugins[ $plugin_basename ] ) ) {
			throw new Exception( "Plugin {$plugin_basename} not found in WordPress plugins array." );
		}

		/** @var string $plugin_name */
		$plugin_name = $plugins[ $plugin_basename ]['Name'];

		/**
		 * The existing saved changes.
		 *
		 * @var ?array<string, array{Name:string,Original_Name:string}> $saved_changes
		 */
		$saved_changes = get_option( self::PLUGINS_PAGE_CHANGES_OPTION_NAME, null );

		$before = $saved_changes[ $plugin_basename ] ?? null;

		if ( empty( $new_name ) ) {

			$after = null;
			unset( $saved_changes[ $plugin_basename ] );

		} else {

			$after = array(
				'Name'          => $new_name,
				'Original_Name' => $plugin_name,
			);

			$saved_changes[ $plugin_basename ] = $after;
		}

		$updated = update_option( self::PLUGINS_PAGE_CHANGES_OPTION_NAME, $saved_changes );

		return array(
			'updated'         => $updated,
			'plugin_basename' => $plugin_basename,
			'plugin_name'     => $plugin_name,
			'saved_before'    => $before,
			'saved_after'     => $after,
		);
	}
}
