<?php
/**
 * Tests for I18n. Tests load_plugin_textdomain.
 *
 * @package brianhenryie/bh-wp-plugins-page
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_Plugins_Page\WP_Includes;

use BrianHenryIE\WP_Plugins_Page\WPUnit_Testcase;

/**
 * @coversDefaultClass \BrianHenryIE\WP_Plugins_Page\WP_Includes\I18n
 *
 * @see I18n
 */
class I18n_WP_Unit_Test extends WPUnit_Testcase {

	/**
	 * Checks if the filter run by WordPress in the load_plugin_textdomain() function is called.
	 *
	 * @covers ::load_plugin_textdomain
	 */
	public function test_load_plugin_textdomain_function() {

		$this->markTestSkipped( 'Something changes ~WP 6.8' );

		$called        = false;
		$actual_domain = null;

		$filter = function ( $locale, $domain ) use ( &$called, &$actual_domain ) {

			$called        = true;
			$actual_domain = $domain;

			return $locale;
		};

		add_filter( 'plugin_locale', $filter, 10, 2 );

		$i18n = new I18n();

		$i18n->load_plugin_textdomain();

		$this->assertTrue( $called, 'plugin_locale filter not called within load_plugin_textdomain() suggesting it has not been set by the plugin.' );
		$this->assertEquals( 'bh-wp-plugins-page', $actual_domain );
	}
}
