<?php

namespace BrianHenryIE\WP_Plugins_Page;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class API {
	use LoggerAwareTrait;

	const SETTINGS_OPTION_NAME = 'bh_wp_plugins_page_changes';

	public function __construct( LoggerInterface $logger ) {
		$this->setLogger( $logger );
	}

	public function set_plugin_name( string $plugin_basename, string $new_title ): array {

		$plugins = get_plugins();

		if ( ! isset( $plugins[ $plugin_basename ] ) ) {
			return array( 'error' => 'no plugin found' );
		}

		$settings = get_option( self::SETTINGS_OPTION_NAME, array() );

		if ( ! isset( $settings[ $plugin_basename ] ) ) {
			$settings[ $plugin_basename ] = array();
		}

		$settings[ $plugin_basename ]['Name']          = $new_title;
		$settings[ $plugin_basename ]['Original_Name'] = $plugins[ $plugin_basename ]['Name'];

		$updated = update_option( self::SETTINGS_OPTION_NAME, $settings );

		return array(
			'updated' => $updated,
		);
	}

}
