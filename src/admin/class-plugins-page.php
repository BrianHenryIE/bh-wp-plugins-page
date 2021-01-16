<?php

namespace BH_WP_Plugins_Page\admin;

class Plugins_Page {

	/**
	 * @hooked plugin_action_links_
	 */
	public function action_links() {

		foreach( $action_links as $action_link ) {
			
		}


	}

	/**
	 *
	 * @hooked plugin_row_meta
	 *
	 * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
	 *
	 * @param string[] $plugin_meta The meta information/links displayed by the plugin description.
	 * @param string   $plugin_file_name The plugin filename to match when filtering.
	 * @param array    $plugin_data Associative array including PluginURI, slug, Author, Version.
	 * @param string   $status The plugin status, e.g. 'Inactive'.
	 *
	 * @return array The filtered $plugin_meta.
	 */
	public function row_meta( $plugin_meta, $plugin_file_name, $plugin_data, $status ) {

		// Which runs first... this or action links????


		$plugin_meta[] = '<a target="_blank" href="https://github.com/BrianHenryIE/BH-WC-Shipment-Tracking-Email-Action">View plugin on GitHub</a>';

		return $plugin_meta;
	}

}