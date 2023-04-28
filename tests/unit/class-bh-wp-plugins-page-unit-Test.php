<?php
/**
 * @package brianhenryie/bh-wp-plugins-page
 * @author  Your Name <email@example.com>
 */

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\ColorLogger\ColorLogger;
use BrianHenryIE\WP_Plugins_Page\Admin\Admin_Assets;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_List_Table;
use BrianHenryIE\WP_Plugins_Page\Admin\Plugins_Page;
use BrianHenryIE\WP_Plugins_Page\API\API;
use BrianHenryIE\WP_Plugins_Page\API\Settings;
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
				'times'  => 2,
			)
		);

		$logger = new ColorLogger();
		$api    = self::make( API::class );
		$settings = self::make( Settings::class );
		new BH_WP_Plugins_Page( $settings, $api, $logger );
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
				'times'  => 2,
			)
		);

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin_Assets::class ), 'enqueue_scripts' ),
			9999
		);

		$logger = new ColorLogger();
		$api    = self::make( API::class );
		$settings = self::make( Settings::class );
		new BH_WP_Plugins_Page( $settings, $api, $logger );
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
				'times'  => 2,
			)
		);

		\WP_Mock::expectFilterAdded(
			'plugin_action_links_one-plugin/one-plugin.php',
			array( new AnyInstance( Plugins_List_Table::class ), 'plugin_specific_action_links' ),
			9999,
			4
		);

		\WP_Mock::expectFilterAdded(
			'plugin_action_links_another-plugin/another-plugin.php',
			array( new AnyInstance( Plugins_List_Table::class ), 'plugin_specific_action_links' ),
			9999,
			4
		);

		\WP_Mock::expectFilterAdded(
			'plugin_row_meta',
			array( new AnyInstance( Plugins_List_Table::class ), 'row_meta' ),
			9999,
			4
		);

		\WP_Mock::expectFilterAdded(
			'all_plugins',
			array( new AnyInstance( Plugins_List_Table::class ), 'edit_plugins_array' )
		);

		$logger = new ColorLogger();
		$api    = self::make( API::class );
		$settings = self::make( Settings::class );
		new BH_WP_Plugins_Page( $settings, $api, $logger );
	}

	/**
	 * @covers ::define_plugins_page_hooks
	 * @covers ::__construct
	 */
	public function test_plugins_page_hooks() {

		\WP_Mock::expectFilterAdded(
			'wp_redirect',
			array( new AnyInstance( Plugins_Page::class ), 'prevent_redirect' ),
			1,
			2
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'active_plugins', \WP_Mock\Functions::type( 'array' ) ),
				'return' => array( 'plugin1/plugin1.php', 'plugin2/plugin2.php' ),
				'times'  => 2,
			)
		);

		\WP_Mock::expectFilterAdded( 'fs_redirect_on_activation_plugin1', '__return_false' );
		\WP_Mock::expectFilterAdded( 'fs_redirect_on_activation_plugin2', '__return_false' );

		$logger = new ColorLogger();
		$api    = self::make( API::class );
		$settings = self::make( Settings::class );
		new BH_WP_Plugins_Page( $settings, $api, $logger );
	}

}
