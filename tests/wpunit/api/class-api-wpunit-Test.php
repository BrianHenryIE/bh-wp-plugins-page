<?php

namespace BrianHenryIE\WP_Plugins_Page\API;

use BrianHenryIE\ColorLogger\ColorLogger;

/**
 * @coversDefaultClass \BrianHenryIE\WP_Plugins_Page\API\API
 */
class API_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @covers ::set_plugin_name
	 */
	public function test_empty_value_clears_saved(): void {
		$logger = new ColorLogger();

		$sut = new API( $logger );

		$cache_plugins = array(
			'bh-wp-autologin-urls/bh-wp-autologin-urls.php' => array( 'Name' => 'Autologin URLs' ),
		);
		wp_cache_set( 'plugins', $cache_plugins, 'plugins' );

		$result = $sut->set_plugin_name( 'bh-wp-autologin-urls/bh-wp-autologin-urls.php', 'New Title' );

		$saved = get_option( API::PLUGINS_PAGE_CHANGES_OPTION_NAME );

		assert( 'New Title' === $saved['bh-wp-autologin-urls/bh-wp-autologin-urls.php']['Name'] );

		$sut->set_plugin_name( 'bh-wp-autologin-urls/bh-wp-autologin-urls.php', '' );

		$updated = get_option( API::PLUGINS_PAGE_CHANGES_OPTION_NAME );

		$this->assertArrayNotHasKey( 'bh-wp-autologin-urls/bh-wp-autologin-urls.php', $updated );
	}
}
