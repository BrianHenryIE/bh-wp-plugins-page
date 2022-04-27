<?php
/**
 *
 *
 * @package BH_WP_Plugins_Page
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BH_WP_Plugins_Page\admin;

use BH_WP_Plugins_Page\includes\BH_WP_Plugins_Page;
use WP_Scripts;

/**
 *
 */
class Admin_Integration_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @see WP_Scripts
	 * @see WPTestCase::go_to()
	 */
	public function test_is_script_enqueued_on_plugins_page() {

		/**
		 * This unsets `pagenow` but doesn't seem to set it again after.
		 *
		 * @see WPTestCase::go_to()
		 */
		$this->go_to( admin_url( 'plugins.php' ) );
		$GLOBALS['pagenow'] = 'plugins.php';
		set_current_screen( 'plugins.php' );

		do_action( 'admin_enqueue_scripts' );

		$wp_scripts = wp_scripts();

		$scripts = array_map(
			function( $script ) use ( $wp_scripts ) {
				return $wp_scripts->registered[ $script ]->src;
			},
			$wp_scripts->queue
		);

		$expected = get_option( 'siteurl' ) . '/wp-content/plugins/bh-wp-plugins-page/admin/js/bh-wp-plugins-page-admin.js';

		$this->assertContains( $expected, $scripts );

	}


	public function test_script_is_not_enqueued_elsewhere() {

		// Remove scripts that were enqueued in the other tests.
		global $wp_scripts;
		$wp_scripts = new WP_Scripts();

		/**
		 * This unsets `pagenow` but doesn't seem to set it again after.
		 *
		 * @see WPTestCase::go_to()
		 */
		$this->go_to( admin_url( 'index.php' ) );
		$GLOBALS['pagenow'] = 'index.php';
		set_current_screen( 'index.php' );

		do_action( 'admin_enqueue_scripts' );

		$scripts = array_map(
			function( $script ) use ( $wp_scripts ) {
				return $wp_scripts->registered[ $script ]->src;
			},
			$wp_scripts->queue
		);

		$expected = get_option( 'siteurl' ) . '/wp-content/plugins/bh-wp-plugins-page/admin/js/bh-wp-plugins-page-admin.js';

		$this->assertNotContains( $expected, $scripts );
	}
}
