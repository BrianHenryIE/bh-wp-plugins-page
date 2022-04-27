<?php

namespace BH_WP_Plugins_Page\admin;

use ReflectionClass;

/**
 * @coversDefaultClass \BH_WP_Plugins_Page\admin\Plugins_List_Table
 */
class Plugins_List_Table_Unit_Test extends \Codeception\Test\Unit {

	/**
	 * @covers ::map_html_to_anchor_element
	 */
	public function test_map_html_to_anchor_element(): void {

		$html_anchor_string = '<a href="https:\/\/example.com\/wp-admin\/admin.php?page=wc-settings&tab=shipping&section=bh-wc-address-validation">Settings<\/a>';

		$reflection = new ReflectionClass( Plugins_List_Table::class );
		$method     = $reflection->getMethod( 'map_html_to_anchor_element' );
		$method->setAccessible( true );

		$sut = new Plugins_List_Table();

		$result = $method->invokeArgs( $sut, array( $html_anchor_string ) );

		$this->assertNotNull( $result );
	}

	/**
	 * @covers ::map_html_to_anchor_element
	 */
	public function test_map_html_to_anchor_element_javascript_and_more(): void {
		$html_anchor_string = "\r\n                    <a href='https:\/\/getenhanced.shop\/wp-admin\/plugins.php?puc_check_for_updates=1&amp;puc_slug=pw-gift-cards&amp;pw_refresh=true&amp;_wpnonce=d1f3491503' id='pimwick-license-link-pw-gift-cards' aria-label='View \/ Edit license key'>View \/ Edit license key<\/a>\r\n                    <script>\r\n                        jQuery('#pimwick-license-link-pw-gift-cards').on('click', function(e) {\r\n                            var editLink = jQuery(this);\r\n                            var href = jQuery(this).attr('href');\r\n                            var key = prompt('License Key', 'PW-61afd1cb6a1fe');\r\n                            if (key && key != 'PW-61afd1cb6a1fe') {\r\n                                editLink.hide().after('<div style=\"color: red; font-weight: 600; font-size: 1.5em;\">Please wait...<\/div>');\r\n                                jQuery.post(ajaxurl, {'action': 'pimwick_change_license_key', 'plugin': 'pw-gift-cards-license-data', 'license_key': key, 'security': 'b2bc00dee6'}, function(result) {\r\n                                    if (!result.success) {\r\n                                        alert(result.data.message);\r\n                                    }\r\n                                    window.location.href = href;\r\n                                }).fail(function(xhr, textStatus, errorThrown) {\r\n                                    if (errorThrown) {\r\n                                        alert(errorThrown);\r\n                                    } else {\r\n                                        alert('Unknown error');\r\n                                    }\r\n                                    window.location.href = href;\r\n                                });\r\n                            }\r\n                            e.preventDefault();\r\n                            return false;\r\n                        });\r\n                    <\/script>\r\n                ";

		$reflection = new ReflectionClass( Plugins_List_Table::class );
		$method     = $reflection->getMethod( 'map_html_to_anchor_element' );
		$method->setAccessible( true );

		$sut = new Plugins_List_Table();

		$result = $method->invokeArgs( $sut, array( $html_anchor_string ) );

		$this->assertNotNull( $result );
	}

}
