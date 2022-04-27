<?php
/**
 * Tests for Admin.
 *
 * @see Admin
 *
 * @package bh-wp-plugins-page
 * @author Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BH_WP_Plugins_Page\admin;

/**
 * Class Admin_Test
 */
class Admin_Unit_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function _tearDown() {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * The plugin name. Unlikely to change.
	 *
	 * @var string Plugin name.
	 */
	private $plugin_name = 'plugin-slug';

	/**
	 * The plugin version, matching the version these tests were written against.
	 *
	 * @var string Plugin version.
	 */
	private $version = '1.0.3';

	/**
	 * Verifies enqueue_styles() calls wp_enqueue_style() with appropriate parameters.
	 * Verifies the .css file exists.
	 *
	 * @covers \BH_WP_Plugins_Page\admin\Admin::enqueue_scripts
	 * @see wp_enqueue_style()
	 */
	public function test_enqueue_script_on_plugins_page() {

		global $pagenow;
		$pagenow = 'plugins.php';

		// define( 'BH_WP_PLUGINS_PAGE_VERSION', '1.0.3' );

		global $plugin_root_dir;

		// Return any old url.
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_root_dir . '/admin/',
			)
		);

		$js_src = $plugin_root_dir . '/admin/js/bh-wp-plugins-page-admin.js';

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( 'bh-wp-plugins-page', $js_src, array( 'jquery' ), '*', false ),
			)
		);

		$admin = new Admin();

		$admin->enqueue_scripts();

		$this->assertFileExists( $js_src );
	}

	/**
	 * Verifies enqueue_scripts() calls wp_enqueue_script() with appropriate parameters.
	 * Verifies the .js file exists.
	 *
	 * @covers \BH_WP_Plugins_Page\admin\Admin::enqueue_scripts
	 * @see wp_enqueue_script()
	 */
	public function test_enqueue_scripts_on_other_pages() {

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 0,
			)
		);

		$admin = new Admin();

		$admin->enqueue_scripts();
	}


}
