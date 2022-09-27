<?php
/**
 * Tests for the root plugin file.
 *
 * @package brianhenryie/bh-wp-plugins-page
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_Plugins_Page;

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

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
				'times'  => 1,
			)
		);

		\WP_Mock::userFunction(
			'plugin_basename',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => "$plugin_root_dir/$plugin_root_dir.php",
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

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		ob_start();

		include $plugin_root_dir . '/bh-wp-plugins-page.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

	}

}
