<?php
/**
 * The main class in the plugin.
 * Hooked to actions and filters defined in WP_Plugins_List_Table.
 *
 * Actions links runs first, but needs to pull in links from meta links, so fires that filter during its run, which
 * is then fired again later during the normal meta links run.
 *
 * @link       https://github.com/brianhenryie/bh-wp-plugins-page
 * @since      1.0.0
 *
 * @package    brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

use BrianHenryIE\WP_Plugins_Page\API\API;
use BrianHenryIE\WP_Plugins_Page\API\Parsed_Link;

/**
 * Parses each link, then decides to move or discard, then removes formatting.
 *
 * @see \WP_List_Table
 * @see \WP_Plugins_List_Table
 */
class Plugins_List_Table {

	/**
	 * Links found in the first column that should be moved to the middle column.
	 * indexed by plugin basename.
	 *
	 * @var array<string, array<Parsed_Link>>
	 */
	protected array $external_parsed_action_links = array();

	/**
	 * Links from the middle column that should be moved to the first column.
	 * indexed by plugin basename.
	 *
	 * @var array<string, array<Parsed_Link>>
	 */
	protected array $internal_parsed_meta_links = array();

	/**
	 * Hooked to plugin-specific action links filters (by looping over 'active_plugins' option).
	 *
	 * @hooked plugin_action_links_{$basename}
	 *
	 * @param array<int|string, string>     $action_links The existing plugin links (usually "Deactivate").
	 * @param string                        $plugin_basename The plugin's directory/filename.php.
	 * @param null|array<int|string, mixed> $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string                        $context     The plugin context. 'all'|'active'|'inactive'|'recently_activated'
	 *                                                    |'upgrade'|'mustuse'|'dropins'|'search'.
	 *
	 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
	 */
	public function plugin_specific_action_links( array $action_links, string $plugin_basename, ?array $plugin_data, string $context ): array {

		// This is probably the case where JetPack (or maybe another plugin, like this does) is running `apply_filters`, so this isn't the case we want to work on.
		if ( empty( $plugin_data ) ) {
			return $action_links;
		}

		$parsed_action_links = array();
		foreach ( $action_links as $key => $html_string ) {
			$parsed_action_links[ $key ] = new Parsed_Link( $key, $html_string );
		}

		$internal_parsed_action_links = array();
		$external_parsed_action_links = array();

		// Save external links to move them to the middle column.
		foreach ( $parsed_action_links as $key => $parsed_link ) {

			if ( $parsed_link->has_external_url() ) {
				$external_parsed_action_links[ $key ] = $parsed_link;
			} else {
				$internal_parsed_action_links[ $key ] = $parsed_link;
			}
		}

		$this->external_parsed_action_links[ $plugin_basename ] = $external_parsed_action_links;

		/**
		 * Get internal links from second column.
		 *
		 * We're already hooked on this filter. We need to invoke it to pull in data from other plugins.
		 *
		 * @see self::row_meta()
		 */
		apply_filters( 'plugin_row_meta', array(), $plugin_basename, $plugin_data, $context );
		$internal_parsed_meta_links = $this->internal_parsed_meta_links[ $plugin_basename ] ?? array();

		/**
		 * All links we want in this column.
		 *
		 * @var Parsed_Link[] $parsed_action_links
		 */
		$parsed_action_links = $this->merge_arrays( array( $internal_parsed_action_links, $internal_parsed_meta_links ) );

		// Reorder:
		// Move settings to the beginning.
		// Move Logs second to end.
		// Move Deactivate at the end.
		$ordered_links_arrays = array(
			'settings'   => array(),
			'links'      => array(),
			'log'        => array(),
			'deactivate' => array(),
		);

		// If there is no anchor, e.g. it is just text, we do not want it in the action links.
		// Filter unwanted links.
		// Remove upsells.
		foreach ( $parsed_action_links as $key => $parsed_link ) {

			if ( $parsed_link->is_empty()
				|| $parsed_link->is_contains_unwanted_terms() ) {
				continue;
			}

			$type = $parsed_link->get_type();

			if ( is_int( $key ) ) {
				$ordered_links_arrays[ $type ][] = $parsed_link;
			} else {
				$ordered_links_arrays[ $type ][ $key ] = $parsed_link;
			}
		}

		/**
		 * Merge the sorted links into one array.
		 *
		 * @var Parsed_Link[] $ordered_links
		 */
		$ordered_links = $this->merge_arrays( $ordered_links_arrays );

		$cleaned_action_links = array();
		foreach ( $ordered_links as $key => $parsed_link ) {
			$cleaned_action_links[ $key ] = $parsed_link->get_cleaned_link();
		}
		return $cleaned_action_links;
	}

	/**
	 * Row meta is the middle column.
	 *
	 * Thankfully, plugin_row_meta runs after plugin_action_links, allowing us to move links from the more important
	 * column to the description column.
	 *
	 * @hooked plugin_row_meta
	 * Hooked at 9999 so all links have been added first.
	 *
	 * @see https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
	 *
	 * @param string[] $meta_links The meta information/links displayed by the plugin description.
	 * @param string   $plugin_file_name The plugin filename to match when filtering.
	 * @param string[] $plugin_data Associative array including PluginURI, slug, Author, Version.
	 * @param string   $status The plugin status, e.g. 'Inactive'.
	 *
	 * @return string[] The filtered $plugin_meta.
	 */
	public function row_meta( array $meta_links, $plugin_file_name, ?array $plugin_data, ?string $status ): array {

		$parsed_meta_links = array();
		foreach ( $meta_links as $key => $html_string ) {
			$parsed_meta_links[ $key ] = new Parsed_Link( $key, $html_string );
		}

		$internal_parsed_meta_links = array();
		$external_parsed_meta_links = array();

		// Save external links to move them to the middle column.
		foreach ( $parsed_meta_links as $key => $parsed_link ) {

			// Check all URLs in the text for external links.
			if ( $parsed_link->has_internal_url() && 'view-details' !== $parsed_link->get_type() ) {
				$internal_parsed_meta_links[ $key ] = $parsed_link;
			} else {
				$external_parsed_meta_links[ $key ] = $parsed_link;
			}
		}

		// Save internal links for use in the first column.
		$this->internal_parsed_meta_links[ $plugin_file_name ] = $internal_parsed_meta_links;

		// Get external links from first column.
		$external_parsed_action_links = $this->external_parsed_action_links[ $plugin_file_name ] ?? array();

		/**
		 * All the links we want to display in this column.
		 *
		 * @var Parsed_Link[] $external_parsed_meta_links
		 */
		$external_parsed_meta_links = $this->merge_arrays( array( $external_parsed_meta_links, $external_parsed_action_links ) );

		// Filter unwanted links.
		// Remove upsells.
		// Remove external license links.
		$cleaned_links = array();
		foreach ( $external_parsed_meta_links as $key => $parsed_link ) {

			if ( $parsed_link->is_empty()
			|| $parsed_link->is_contains_unwanted_terms() ) {
				continue;
			}

			$parsed_link->replace_text_with_icons();
			$cleaned_links[ $key ] = $parsed_link->get_cleaned_link();
		}

		return $cleaned_links;
	}


	/**
	 * Merge associative arrays, preserve string keys.
	 *
	 * @param array<mixed> $all_arrays Array of arrays.
	 *
	 * @return array<mixed>
	 */
	protected function merge_arrays( array $all_arrays ): array {
		$merged_array = array();

		foreach ( $all_arrays as $sub_array ) {
			foreach ( $sub_array as $key => $value ) {
				if ( is_int( $key ) ) {
					$merged_array[] = $value;
				} else {
					$merged_array[ $key ] = $value;
				}
			}
		}
		return $merged_array;
	}

	/**
	 * Merge our saved changes into the get_plugins() array when the page is rendering.
	 *
	 * @hooked all_plugins
	 * @see \WP_Plugins_List_Table::prepare_items()
	 *
	 * @param array<string,array<string,string>> $all_plugins The WordPress `get_plugins()` array.
	 *
	 * @return array<string,array<string,string>>
	 */
	public function edit_plugins_array( array $all_plugins ): array {

		$changes = get_option( API::PLUGINS_PAGE_CHANGES_OPTION_NAME, array() );

		foreach ( $changes as $plugin_basename => $plugin_changes ) {
			if ( isset( $all_plugins[ $plugin_basename ] ) ) {
				$all_plugins[ $plugin_basename ] = array_merge( $all_plugins[ $plugin_basename ], $plugin_changes );
			}
		}

		return $all_plugins;
	}
}
