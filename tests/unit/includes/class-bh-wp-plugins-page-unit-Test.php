<?php
/**
 * @package BH_WP_Plugins_Page_Unit_Name
 * @author  Your Name <email@example.com>
 */

namespace BH_WP_Plugins_Page\includes;

use BH_WP_Plugins_Page\admin\Admin;
use BH_WP_Plugins_Page\admin\Plugins_List_Table;
use WP_Mock\Matcher\AnyInstance;

/**
 * Class BH_WP_Plugins_Page_Unit_Test
 */
class BH_WP_Plugins_Page_Unit_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function _tearDown() {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers \BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page::set_locale
	 */
	public function test_set_locale_hooked() {

		\WP_Mock::expectActionAdded(
			'plugins_loaded',
			array( new AnyInstance( I18n::class ), 'load_plugin_textdomain' )
		);

		new BH_WP_Plugins_Page();
	}

	/**
	 * @covers \BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page::define_admin_hooks
	 */
	public function test_admin_hooks() {

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin::class ), 'enqueue_scripts' ),
			PHP_INT_MAX
		);

		new BH_WP_Plugins_Page();
	}

	/**
	 * @covers \BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page::define_plugins_list_table_hooks
	 */
	public function test_plugins_list_table_hooks() {

		$active_plugins = array(
			'one-plugin/one-plugin.php',
			'another-plugin/another-plugin.php'
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args' => array( 'active_plugins',  \WP_Mock\Functions::type( 'array' ) ),
				'return' => $active_plugins
			)
		);

		\WP_Mock::expectActionAdded(
			'plugin_action_links',
			array( new AnyInstance( Plugins_List_Table::class ), 'action_links' ),
			PHP_INT_MAX,
			4
		);

		\WP_Mock::expectActionAdded(
			'plugin_row_meta',
			array( new AnyInstance( Plugins_List_Table::class ), 'row_meta' ),
			PHP_INT_MAX,
			4
		);

		\WP_Mock::expectActionAdded(
			'plugin_action_links_one-plugin/one-plugin.php',
			'__CLOSURE__',
			PHP_INT_MAX,
			1
		);

		\WP_Mock::expectActionAdded(
			'plugin_action_links_another-plugin/another-plugin.php',
			'__CLOSURE__',
			PHP_INT_MAX,
			1
		);

		new BH_WP_Plugins_Page();

	}
}
