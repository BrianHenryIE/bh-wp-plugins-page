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
	 * @return array{updated:bool,plugin_basename:string,before:array,after:array,Name:string}
	 * @throws Exception When the plugin whose title is being updated is not found in WordPress's plugin list.
	 */
	public function set_plugin_name( string $plugin_basename, string $new_name ): array {

		$plugins = get_plugins();

		if ( ! isset( $plugins[ $plugin_basename ] ) ) {
			throw new Exception( "Plugin {$plugin_basename} not found in WordPress plugins array." );
		}

		/**
		 * The existing saved changes.
		 *
		 * @var array<string, array{Name:string,Original_Name:string}> $saved_changes
		 */
		$saved_changes = get_option( self::PLUGINS_PAGE_CHANGES_OPTION_NAME, array() );

		if ( ! isset( $saved_changes[ $plugin_basename ] ) ) {
			$saved_changes[ $plugin_basename ] = array();
		}

		$before = $saved_changes[ $plugin_basename ];

		if ( empty( $new_name ) ) {

			unset( $saved_changes[ $plugin_basename ]['Name'] );
			unset( $saved_changes[ $plugin_basename ]['Original_Name'] );
			if ( empty( $saved_changes[ $plugin_basename ] ) ) {
				unset( $saved_changes[ $plugin_basename ] );
				$after = null;
			} else {
				$after = $saved_changes[ $plugin_basename ];
			}

			$name = $plugins[ $plugin_basename ]['Name'];
		} else {

			$saved_changes[ $plugin_basename ]['Name']          = $new_name;
			$saved_changes[ $plugin_basename ]['Original_Name'] = $plugins[ $plugin_basename ]['Name'];

			$after = $saved_changes[ $plugin_basename ];

			$name = $new_name;
		}

		$updated = update_option( self::PLUGINS_PAGE_CHANGES_OPTION_NAME, $saved_changes );

		return array(
			'updated'         => $updated,
			'plugin_basename' => $plugin_basename,
			'before'          => $before,
			'after'           => $after,
			'Name'            => $name,
		);
	}
}
