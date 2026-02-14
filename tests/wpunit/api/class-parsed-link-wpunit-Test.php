<?php

namespace BrianHenryIE\WP_Plugins_Page\API;

use BrianHenryIE\WP_Plugins_Page\WPUnit_Testcase;

/**
 * @coversDefaultClass \BrianHenryIE\WP_Plugins_Page\API\Parsed_Link
 */
class Parsed_Link_WPUnit_Test extends WPUnit_Testcase {

	public function test_parse_html_happy_path(): void {

		$html_string = '<a href="http://demo.accesspressthemes.com/wordpress-plugins/apex-notification-bar-lite" target="_blank"><span class="dashicons dashicons-welcome-view-site"></span>Live Demo</a>';

		$sut = new Parsed_Link( '', $html_string );

		$this->assertTrue( $sut->has_external_url() );
		$this->assertEquals( 'links', $sut->get_type() );
		$this->assertFalse( $sut->is_empty() );
	}

	public function test_parse_html_settings(): void {

		$html_string = '<a href="https://example.com/wp-admin/admin.php?page=wc-settings&tab=shipping&section=bh-wc-address-validation">Settings</a>';

		$sut = new Parsed_Link( '', $html_string );

		$this->assertEquals( 'settings', $sut->get_type() );
	}

	public function test_map_html_to_anchor_element_javascript_and_more(): void {

		$html_string = "\r\n                    <a href='https:\/\/getenhanced.shop\/wp-admin\/plugins.php?puc_check_for_updates=1&amp;puc_slug=pw-gift-cards&amp;pw_refresh=true&amp;_wpnonce=d1f3491503' id='pimwick-license-link-pw-gift-cards' aria-label='View \/ Edit license key'>View \/ Edit license key<\/a>\r\n                    <script>\r\n                        jQuery('#pimwick-license-link-pw-gift-cards').on('click', function(e) {\r\n                            var editLink = jQuery(this);\r\n                            var href = jQuery(this).attr('href');\r\n                            var key = prompt('License Key', 'PW-61afd1cb6a1fe');\r\n                            if (key && key != 'PW-61afd1cb6a1fe') {\r\n                                editLink.hide().after('<div style=\"color: red; font-weight: 600; font-size: 1.5em;\">Please wait...<\/div>');\r\n                                jQuery.post(ajaxurl, {'action': 'pimwick_change_license_key', 'plugin': 'pw-gift-cards-license-data', 'license_key': key, 'security': 'b2bc00dee6'}, function(result) {\r\n                                    if (!result.success) {\r\n                                        alert(result.data.message);\r\n                                    }\r\n                                    window.location.href = href;\r\n                                }).fail(function(xhr, textStatus, errorThrown) {\r\n                                    if (errorThrown) {\r\n                                        alert(errorThrown);\r\n                                    } else {\r\n                                        alert('Unknown error');\r\n                                    }\r\n                                    window.location.href = href;\r\n                                });\r\n                            }\r\n                            e.preventDefault();\r\n                            return false;\r\n                        });\r\n                    <\/script>\r\n                ";

		$sut = new Parsed_Link( '', $html_string );

		$this->assertStringNotContainsString( 'jQuery', $sut->get_cleaned_link() );
	}


	public function test_parse_link_view_details(): void {

		$html_string = '<a href="http://localhost:8080/bh-wp-plugins-page/wp-admin/plugin-install.php?tab=plugin-information&#038;plugin=ga-google-analytics&#038;TB_iframe=true&#038;width=600&#038;height=550" class="thickbox open-plugin-details-modal" aria-label="More information about GA Google Analytics" data-title="GA Google Analytics">View details</a>';

		$sut = new Parsed_Link( '', $html_string );

		$this->assertEquals( 'view-details', $sut->get_type() );
	}
}
