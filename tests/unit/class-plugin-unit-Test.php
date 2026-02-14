<?php
/**
 * Tests for the root plugin file.
 *
 * @package brianhenryie/bh-wp-plugins-page
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\ColorLogger\ColorLogger;
use BrianHenryIE\WP_Plugins_Page\BrianHenryIE\WP_Logger\Logger;
use Psr\Log\LoggerInterface;

class Plugin_Unit_Test extends Unit_Testcase {

	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include(): void {

		// Prevents code-coverage counting, and removes the need to define the WordPress functions that are used in that class.
		\Patchwork\redefine(
			array( BH_WP_Plugins_Page::class, '__construct' ),
			function () {}
		);

		\Patchwork\redefine(
			array( Logger::class, 'instance' ),
			fn(): LoggerInterface => new ColorLogger()
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
