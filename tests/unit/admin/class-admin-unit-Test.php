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
	public function test_enqueue_script_on_plugins_page() {

		global $pagenow;
		$pagenow = 'plugins.php';

		global $plugin_root_dir;

		// Return any old url.
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_root_dir . '/',
			)
		);

		$js_src = $plugin_root_dir . '/assets/bh-wp-plugins-page-admin.js';
		$js_url = '/Users/brianhenry/Sites/bh-wp-plugins-page/assets/bh-wp-plugins-page-admin.js';

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( 'bh-wp-plugins-page', $js_url, array( 'jquery' ), '*', false ),
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
	public function test_enqueue_scripts_on_other_pages() {

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
