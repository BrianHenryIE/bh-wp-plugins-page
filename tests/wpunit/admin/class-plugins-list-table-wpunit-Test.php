<?php
/**
 *
 *
 * @package BH_WP_Plugins_Page
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BH_WP_Plugins_Page\admin;

/**
 *
 */
class Plugins_List_Table_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @covers \BH_WP_Plugins_Page\admin\Plugins_List_Table::action_links
	 */
	public function a_test_array_keys_preserved() {

		// plugin_action_links

		$sut = new Plugins_List_Table();

		$plugin_file = 'a-plugin/a-plugin.php';

		$actions = array(
			'deactivate' => '<a href="' . get_site_url() . " . '/wp-admin/plugins.php?action=deactivate&plugin=" . urlencode( $plugin_file ) . '">Deactivate</a>',
		);

		$plugin_data = array( 'not-used' );
		$context     = 'not-used';

		$new_actions = $sut->action_links( $actions, $plugin_file, $plugin_data, $context );

		$this->assertEqualSets( array_keys( $actions ), array_keys( $new_actions ) );

	}

	/**
	 * Test inner spans with e.g. dashicons are removed.
	 */
	public function test_row_meta() {

		$plugin_basename = 'apex-notification-bar-lite/apex-notification-bar-lite.php';

		$data = array(
			0         => 'Version 2.0.4',
			1         => 'By <a href="http://accesspressthemes.com">AccessPress Themes</a>',
			2         => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/plugin-install.php?tab=plugin-information&#038;plugin=apex-notification-bar-lite&#038;TB_iframe=true&#038;width=600&#038;height=550" class="thickbox open-plugin-details-modal" aria-label="More information about Apex Notification Bar Lite" data-title="Apex Notification Bar Lite">View details</a>',
			'demo'    => '<a href="http://demo.accesspressthemes.com/wordpress-plugins/apex-notification-bar-lite" target="_blank"><span class="dashicons dashicons-welcome-view-site"></span>Live Demo</a>',
			'doc'     => '<a href="https://accesspressthemes.com/documentation/apex-notification-bar-lite/" target="_blank"><span class="dashicons dashicons-media-document"></span>Documentation</a>',
			'support' => '<a href="http://accesspressthemes.com/support" target="_blank"><span class="dashicons dashicons-admin-users"></span>Support</a>',
			'pro'     => '<a href="https://accesspressthemes.com/wordpress-plugins/apex-notification-bar" target="_blank"><span class="dashicons dashicons-cart"></span>Premium version</a>',
		);

		$sut = new Plugins_List_Table();

		$sut->plugin_specific_action_links( array(), $plugin_basename, array(), '' );

		$result = $sut->row_meta( $data, $plugin_basename, array(), '' );

		$this->assertArrayHasKey( 'doc', $result );

		$this->assertEquals( '<a href="https://accesspressthemes.com/documentation/apex-notification-bar-lite/" target="_blank">Documentation</a>', $result['doc'] );

	}

	public function data() {

		// Apex Notification Bar Lite: meta keys
		$data = array(
			0         => 'Version 2.0.4',
			1         => 'By <a href="http://accesspressthemes.com">AccessPress Themes</a>',
			2         => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/plugin-install.php?tab=plugin-information&#038;plugin=apex-notification-bar-lite&#038;TB_iframe=true&#038;width=600&#038;height=550" class="thickbox open-plugin-details-modal" aria-label="More information about Apex Notification Bar Lite" data-title="Apex Notification Bar Lite">View details</a>',
			'demo'    => '<a href="http://demo.accesspressthemes.com/wordpress-plugins/apex-notification-bar-lite" target="_blank"><span class="dashicons dashicons-welcome-view-site"></span>Live Demo</a>',
			'doc'     => '<a href="https://accesspressthemes.com/documentation/apex-notification-bar-lite/" target="_blank"><span class="dashicons dashicons-media-document"></span>Documentation</a>',
			'support' => '<a href="http://accesspressthemes.com/support" target="_blank"><span class="dashicons dashicons-admin-users"></span>Support</a>',
			'pro'     => '<a href="https://accesspressthemes.com/wordpress-plugins/apex-notification-bar" target="_blank"><span class="dashicons dashicons-cart"></span>Premium version</a>',
		);

		// webappick-product-feed-for-woocommerce/woo-feed.php
		// Says it only has one action link
		// BUT THERE ARE THREE ON THE PAGE!
		$webapppick_actions = array(
			'deactivate' => '<a href="plugins.php?action=deactivate&amp;plugin=webappick-product-feed-for-woocommerce%2Fwoo-feed.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=18cc9813fc" id="deactivate-webappick-product-feed-for-woocommerce" aria-label="Deactivate CTX Feed">Deactivate</a>',
		);

	}

	public function test_ga_google_analytics() {

		$ga_google_analytics_actions = array(
			0            => '<a target="_blank" rel="noopener noreferrer" href="https://plugin-planet.com/ga-google-analytics-pro/?plugin" title="Get GA Pro!" style="font-weight:bold;">Go&nbsp;Pro</a>',
			1            => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/options-general.php?page=ga-google-analytics">Settings</a>',
			'deactivate' => '<a href="plugins.php?action=deactivate&amp;plugin=ga-google-analytics%2Fga-google-analytics.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=5629922594" id="deactivate-ga-google-analytics" aria-label="Deactivate GA Google Analytics">Deactivate</a>',
		);

		$ga_google_analytics_meta = array(
			0 => 'Version 20210719',
			1 => 'By <a href="https://plugin-planet.com/">Jeff Starr</a>',
			2 => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/plugin-install.php?tab=plugin-information&#038;plugin=ga-google-analytics&#038;TB_iframe=true&#038;width=600&#038;height=550" class="thickbox open-plugin-details-modal" aria-label="More information about GA Google Analytics" data-title="GA Google Analytics">View details</a>',
			3 => '<a target="_blank" rel="noopener noreferrer" href="https://perishablepress.com/google-analytics-plugin/" title="Plugin Homepage">Homepage</a>',
			4 => '<a target="_blank" rel="noopener noreferrer" href="https://wordpress.org/support/plugin/ga-google-analytics/reviews/?rate=5#new-post" title="Click here to rate and review this plugin on WordPress.org">Rate this plugin&nbsp;&raquo;</a>',
		);

		$sut = new Plugins_List_Table();

		$plugin_actions = $ga_google_analytics_actions;
		$plugin_meta    = $ga_google_analytics_meta;
		$plugin_file    = 'plugin/plugin.php';
		$plugin_data    = array();
		$status         = '';

		$sut->row_meta( $ga_google_analytics_meta, $plugin_file, array(), '' );

		$actions_result = $sut->plugin_specific_action_links( $plugin_actions, $plugin_file, array(), '' );

		// The Get Pro link sould be gone.
		$this->assertCount( 2, $actions_result );

		$this->assertEquals( 'deactivate', array_key_last( $actions_result ) );

		$meta_result = $sut->row_meta( $plugin_meta, $plugin_file, $plugin_data, $status );

		// The review link should be gone.
		$this->assertCount( 4, $meta_result );

	}


	/**
	 * Test upsells are removed
	 */
	public function test_row_meta_remove_upsells() {

		$plugin_basename = 'apex-notification-bar-lite/apex-notification-bar-lite.php';

		// Apex Notification Bar Lite: meta keys
		$data = array(
			0         => 'Version 2.0.4',
			1         => 'By <a href="http://accesspressthemes.com">AccessPress Themes</a>',
			2         => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/plugin-install.php?tab=plugin-information&#038;plugin=apex-notification-bar-lite&#038;TB_iframe=true&#038;width=600&#038;height=550" class="thickbox open-plugin-details-modal" aria-label="More information about Apex Notification Bar Lite" data-title="Apex Notification Bar Lite">View details</a>',
			'demo'    => '<a href="http://demo.accesspressthemes.com/wordpress-plugins/apex-notification-bar-lite" target="_blank"><span class="dashicons dashicons-welcome-view-site"></span>Live Demo</a>',
			'doc'     => '<a href="https://accesspressthemes.com/documentation/apex-notification-bar-lite/" target="_blank"><span class="dashicons dashicons-media-document"></span>Documentation</a>',
			'support' => '<a href="http://accesspressthemes.com/support" target="_blank"><span class="dashicons dashicons-admin-users"></span>Support</a>',
			'pro'     => '<a href="https://accesspressthemes.com/wordpress-plugins/apex-notification-bar" target="_blank"><span class="dashicons dashicons-cart"></span>Premium version</a>',
		);

		$sut = new Plugins_List_Table();

		$result = $sut->row_meta( $data, $plugin_basename, array(), '' );

		$this->assertArrayNotHasKey( 'pro', $result );

	}


	public function test_external_links_removed_from_first_column() {

	}

	public function test_external_links_added_to_second_column() {

	}

	public function test_internal_links_removed_from_second_column() {

	}

	public function test_internal_meta_links_added_to_first_column() {

		$plugin_basename = 'wp-data-access/wp-data-access.php';

		$action_links = array(
			'opt-in-or-opt-out wp-data-access' => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/admin.php?page=wpda">Opt In</a>',
			'deactivate'                       => '<a href="plugins.php?action=deactivate&amp;plugin=wp-data-access%2Fwp-data-access.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=9c9092932f" id="deactivate-wp-data-access" aria-label="Deactivate WP Data Access">Deactivate</a><i class="fs-module-id" data-module-id="6189"></i>',
		);

		$meta_links = array(
			0 => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/options-general.php?page=wpdataaccess">Settings</a>',
		);

		$return_meta_links = function( $links, $plugin_basename, $plugin_data, $status ) use ( $meta_links ) {
			return $meta_links;
		};
		add_filter( 'plugin_row_meta', $return_meta_links, 10, 4 );

		$sut = new Plugins_List_Table();

		// This is fired inside `plugin_specific_action_links` in order to find internal meta links.
		add_filter( 'plugin_row_meta', array( $sut, 'row_meta' ), PHP_INT_MAX, 4 );

		$result = $sut->plugin_specific_action_links( $action_links, $plugin_basename, array(), '' );

		// Check a link with the word Settings is now in the first column.
		$contains_settings = array_reduce(
			$result,
			function( $carry, $element ) {
				return $carry || stristr( $element, 'Settings' );
			},
			false
		);

		remove_filter( 'plugin_row_meta', array( $sut, 'row_meta' ), PHP_INT_MAX );
		remove_filter( 'plugin_row_meta', $return_meta_links );

		$this->assertTrue( $contains_settings );

	}

	public function test_internal_meta_links_are_removed() {

		$plugin_basename = 'pricing-deals-for-woocommerce/vt-pricing-deals.php';

		$action_links = array(
			0            => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/edit.php?post_type=vtprd-rule&page=vtprd_setup_options_page" target="_blank">Settings</a>',
			1            => '<a href="https://www.varktech.com/documentation/pricing-deals/introrule/" target="_blank">Docs</a>',
			'deactivate' => '<a href="plugins.php?action=deactivate&amp;plugin=pricing-deals-for-woocommerce%2Fvt-pricing-deals.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=163f8509bd" id="deactivate-pricing-deals-for-woocommerce" aria-label="Deactivate VarkTech Pricing Deals for WooCommerce">Deactivate</a>',
		);

		$meta_links = array(
			0 => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/edit.php?post_type=vtprd-rule&page=vtprd_setup_options_page#vtprd-delete-plugin-buttons-anchor" target="_blank">Remove All</a>',
		);

		$return_meta_links = function( $links, $plugin_file, $plugin_data, $status ) use ( $meta_links ) {
			return $meta_links;
		};
		add_filter( 'plugin_row_meta', $return_meta_links, 10, 4 );

		$sut = new Plugins_List_Table();

		// This is fired inside `plugin_specific_action_links` in order to find internal meta links.
		add_filter( 'plugin_row_meta', array( $sut, 'row_meta' ), PHP_INT_MAX, 4 );

		$result = $sut->plugin_specific_action_links( $action_links, $plugin_basename, array(), '' );

		// Check a link with the word Settings is now in the first column.
		$contains_settings = array_reduce(
			$result,
			function( $carry, $element ) {
				return $carry || stristr( $element, 'Remove' );
			},
			false
		);

		remove_filter( 'plugin_row_meta', array( $sut, 'row_meta' ), PHP_INT_MAX );
		remove_filter( 'plugin_row_meta', $return_meta_links );

		$this->assertTrue( $contains_settings );

	}

	/**
	 * For slider-wd, the meta was showing "... View details | Ask a question | | Help ".
	 *
	 * This test ensures there is no empty item.
	 */
	public function test_no_empty_meta() {

		$plugin_basename = 'slider-wd/slider-wd.php';

		$action_links = array(
			'deactivate' => '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/plugins.php?action=deactivate&plugin=slider-wd/slider-wd.php&_wpnonce=4271d719f1" class="wds_deactivate_link">Deactivate</a>',
			0            => '<a href="https://wordpress.org/support/plugin/slider-wd/#new-post" target="_blank">Help</a>',
		);

		$meta_links = array(
			0 => '<a href=\'https://wordpress.org/support/plugin/slider-wd/#new-post\' target=\'_blank\'>Ask a question</a>',
			1 => '<a href=\'https://wordpress.org/support/plugin/slider-wd/reviews#new-post\' target=\'_blank\' title=\'Rate\'>
            <i class=\'wdi-rate-stars\'><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'15\' height=\'15\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'feather feather-star\'><polygon points=\'12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\'/></svg><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'15\' height=\'15\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'feather feather-star\'><polygon points=\'12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\'/></svg><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'15\' height=\'15\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'feather feather-star\'><polygon points=\'12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\'/></svg><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'15\' height=\'15\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'feather feather-star\'><polygon points=\'12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\'/></svg><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'15\' height=\'15\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'feather feather-star\'><polygon points=\'12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\'/></svg></i></a>',
		);

		$return_meta_links = function( $links, $plugin_file, $plugin_data, $status ) use ( $meta_links ) {
			return $meta_links;
		};
		add_filter( 'plugin_row_meta', $return_meta_links, 10, 4 );

		$sut = new Plugins_List_Table();

		// Need to run this to move them from the left to the center column.
		$sut->plugin_specific_action_links( $action_links, $plugin_basename, array(), '' );

		$result = $sut->row_meta( $meta_links, $plugin_basename, array(), '' );

		remove_filter( 'plugin_row_meta', $return_meta_links );

		$this->assertCount( 2, $result );

	}

	public function test_remove_empty_meta_links() {

		$plugin_basename = 'slider-wd/slider-wd.php';

		$problem = "<a href='https://wordpress.org/support/plugin/slider-wd/reviews#new-post' target='_blank' title='Rate'>
            <i class='wdi-rate-stars'><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg></i></a>";
		$sut     = new Plugins_List_Table();
		$result  = $sut->row_meta( array( $problem ), $plugin_basename, array(), '' );

		$this->assertEmpty( $result );

	}

}
