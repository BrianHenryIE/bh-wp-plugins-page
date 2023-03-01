<?php
/**
 * Tests for Admin.
 *
 * @see Admin_Assets
 *
 * @package brianhenryie/bh-wp-plugins-page
 * @author Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

use SebastianBergmann\CodeCoverage\CodeCoverage;

/**
 * @coversDefaultClass \BrianHenryIE\WP_Plugins_Page\Admin\Admin_Assets
 */
class Admin_Unit_Test extends \Codeception\Test\Unit {

	protected function setUp(): void {
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * Verifies enqueue_styles() calls wp_enqueue_style() with appropriate parameters.
	 * Verifies the .css file exists.
	 *
	 * @covers ::enqueue_scripts
	 * @see wp_enqueue_style()
	 */
	public function test_enqueue_script_on_plugins_page(): void {

		global $pagenow;
		$pagenow = 'plugins.php';

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'times'  => 2,
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => array(),
			)
		);

		\WP_Mock::passthruFunction( 'admin_url' );
		\WP_Mock::passthruFunction( 'wp_create_nonce' );

		$js_src = $plugin_root_dir . '/assets/bh-wp-plugins-page-admin.js';
		$js_url = '/Users/brianhenry/Sites/bh-wp-plugins-page/assets/bh-wp-plugins-page-admin.js';

		\WP_Mock::userFunction(
			'wp_json_encode',
			array(
				'times'  => 2,
				'return' => '',
			)
		);

		\WP_Mock::userFunction(
			'wp_add_inline_script',
			array(
				'times' => 1,
				'args'  => array( 'bh-wp-plugins-page', \WP_Mock\Functions::type( 'string' ), 'before' ),
			)
		);

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( 'bh-wp-plugins-page', $js_url, array( 'jquery' ), '*', true ),
			)
		);

		\WP_Mock::userFunction(
			'wp_enqueue_style',
			array(
				'times' => 1,
				'args'  => array( 'bh-wp-plugins-page', \WP_Mock\Functions::type( 'string' ), array(), '*' ),
			)
		);

		$admin = new Admin_Assets();

		$admin->enqueue_scripts();

		$this->assertFileExists( $js_src );
	}

	/**
	 * Verifies enqueue_scripts() calls wp_enqueue_script() with appropriate parameters.
	 * Verifies the .js file exists.
	 *
	 * @covers ::enqueue_scripts
	 * @see wp_enqueue_script()
	 */
	public function test_enqueue_scripts_on_other_pages(): void {

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 0,
			)
		);

		$admin = new Admin_Assets();

		$admin->enqueue_scripts();
	}
}
