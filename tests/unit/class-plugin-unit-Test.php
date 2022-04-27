<?php
/**
 * Tests for the root plugin file.
 *
 * @package BH_WP_Plugins_Page
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BH_WP_Plugins_Page;

use BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page;

class Plugin_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		\WP_Mock::tearDown();
		\Patchwork\restoreAll();
	}

	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include(): void {

		// Prevents code-coverage counting, and removes the need to define the WordPress functions that are used in that class.
		\Patchwork\redefine(
			array( BH_WP_Plugins_Page::class, '__construct' ),
			function() {}
		);

		$plugin_root_dir = dirname( __DIR__, 2 ) . '/src';

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
				'times'  => 1,
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook',
			array(
				'times' => 0,
			)
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook',
			array(
				'times' => 0,
			)
		);

		ob_start();

		include $plugin_root_dir . '/bh-wp-plugins-page.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

	}

}
