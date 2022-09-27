<?php
/**
 * @package brianhenryie/bh-wp-plugins-page
 * @author  Your Name <email@example.com>
 */

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\WP_Plugins_Page\Admin\Admin_Assets;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_List_Table;
use BrianHenryIE\WP_Plugins_Page\WP_Includes\I18n;
use WP_Mock\Matcher\AnyInstance;

/**
 * @coversDefaultClass \BrianHenryIE\WP_Plugins_Page\BH_WP_Plugins_Page
 */
class BH_WP_Plugins_Page_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::set_locale
	 */
	public function test_set_locale_hooked() {

		\WP_Mock::expectActionAdded(
			'plugins_loaded',
			array( new AnyInstance( I18n::class ), 'load_plugin_textdomain' )
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'active_plugins', \WP_Mock\Functions::type( 'array' ) ),
				'return' => array(),
				'times'  => 1,
			)
		);

		new BH_WP_Plugins_Page();
	}

	/**
	 * @covers ::define_admin_hooks
	 */
	public function test_admin_hooks() {

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'active_plugins', \WP_Mock\Functions::type( 'array' ) ),
				'return' => array(),
				'times'  => 1,
			)
		);

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin_Assets::class ), 'enqueue_scripts' ),
			PHP_INT_MAX
		);

		new BH_WP_Plugins_Page();
	}

	/**
	 * @covers ::define_plugins_list_table_hooks
	 */
	public function test_plugins_list_table_hooks() {

		$active_plugins = array(
			'one-plugin/one-plugin.php',
			'another-plugin/another-plugin.php',
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'active_plugins', \WP_Mock\Functions::type( 'array' ) ),
				'return' => $active_plugins,
				'times'  => 1,
			)
		);

		\WP_Mock::expectActionAdded(
			'plugin_row_meta',
			array( new AnyInstance( Plugins_List_Table::class ), 'row_meta' ),
			PHP_INT_MAX,
			4
		);

		\WP_Mock::expectActionAdded(
			'plugin_action_links_one-plugin/one-plugin.php',
			array( new AnyInstance( Plugins_List_Table::class ), 'plugin_specific_action_links' ),
			PHP_INT_MAX,
			4
		);

		\WP_Mock::expectActionAdded(
			'plugin_action_links_another-plugin/another-plugin.php',
			array( new AnyInstance( Plugins_List_Table::class ), 'plugin_specific_action_links' ),
			PHP_INT_MAX,
			4
		);

		new BH_WP_Plugins_Page();

	}
}
