<?php
/**
 * Tests for the root plugin file.
 *
 * @package brianhenryie/bh-wp-plugins-page
 * @author  Your Name <email@example.com>
 */

namespace BrianHenryIE\WP_Plugins_Page\WP_Includes;

use BrianHenryIE\WP_Plugins_Page\Unit_Testcase;
use Override;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversDefaultClass \BrianHenryIE\WP_Plugins_Page\WP_Includes\I18n
 */
class I18n_Unit_Test extends Unit_Testcase {

	#[Override]
	protected function setup(): void {
		\WP_Mock::setUp();
	}

	#[Override]
	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Verify load_plugin_textdomain is correctly called.
	 *
	 * @covers ::load_plugin_textdomain
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
