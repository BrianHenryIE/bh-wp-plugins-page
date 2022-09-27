<?php
/**
 * Tests for the root plugin file.
 *
 * @package brianhenryie/bh-wp-plugins-page
 * @author  Your Name <email@example.com>
 */

namespace BrianHenryIE\WP_Plugins_Page\i18n;

use BrianHenryIE\WP_Plugins_Page\WP_Includes\BH_WP_Plugins_Page;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversNothing
 */
class I18n_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Verify load_plugin_textdomain is correctly called.
	 *
	 * @covers \BH_WP_Plugins_Page\WP_Includes\I18n::load_plugin_textdomain
	 */
	public function test_load_plugin_textdomain() {

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'load_plugin_textdomain',
			array(
				'args' => array(
					'plugin-slug',
					false,
					$plugin_root_dir . '/languages/',
				),
			)
		);
	}
}
