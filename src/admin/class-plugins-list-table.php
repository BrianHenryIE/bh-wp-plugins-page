<?php
/**
 * The main class in the plugin.
 * Hooked to actions and filters defined in WP_Plugins_List_Table.
 *
 * @see WP_Plugins_List_Table
 *
 * @link       https://github.com/brianhenryie/bh-wp-plugins-page
 * @since      1.0.0
 *
 * @package    BH_WP_Plugins_Page
 * @subpackage BH_WP_Plugins_Page/admin
 */

namespace BH_WP_Plugins_Page\admin;

use DOMDocument;
use DOMNode;

/**
 * Class Plugins_List_Table
 *
 * @package BH_WP_Plugins_Page\admin
 */
class Plugins_List_Table {

	/**
	 * Instance variable to hold external links that were found in the first plugins column and
	 * should be moved to the description column.
	 *
	 * @var array
	 */
	protected $move_to_meta_column = array();

	/**
	 * Get the anchor as an object from the HTML string.
	 *
	 * Discards the rest of the HTML string (e.g. "Version...", "By...").
	 * Presumes one anchor per string.
	 *
	 * @param string $html_anchor_string A HTML string whose anchor we want to analyse.
	 *
	 * @return DOMNode|null
	 */
	protected function map_html_link_to_node( $html_anchor_string ): ?DOMNode {
		$dom_document = new DOMDocument();

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$bool_result = @$dom_document->loadHtml( $html_anchor_string );

		if ( false === $bool_result ) {
			return null;
		}

		return $dom_document->getElementsByTagName( 'a' )->item( 0 );
	}

	/**
	 * Given a DOMNode (presumably a modified link), return its HTML string.
	 *
	 * @param DOMNode $node The object that has been analysed.
	 *
	 * @return string HTML.
	 */
	protected function map_node_to_html_link( DOMNode $node ): string {

		$node_document = new DOMDocument();
		$node_document->appendChild( $node_document->importNode( $node, true ) );
		$node_html = $node_document->saveHTML();

		return $node_html;
	}

	/**
	 * Hooked to the general filter for action links for plugins.
	 *
	 * @hooked plugin_action_links
	 * @see WP_Plugins_List_Table::single_row()
	 *
	 * @param string[] $actions     An array of plugin action links. By default this can include 'activate',
	 *                              'deactivate', and 'delete'. With Multisite active this can also include
	 *                              'network_active' and 'network_only' items.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string   $context     The plugin context. By default this can include 'all', 'active', 'inactive',
	 *                              'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 */
	public function action_links( $actions, $plugin_file, $plugin_data, $context ) {

		$actions_nodes = array_map( array( $this, 'map_html_link_to_node' ), $actions );

		$good_action_links = $this->get_good_action_links( $actions_nodes, $plugin_file );

		return array_map( array( $this, 'map_node_to_html_link' ), $good_action_links );
	}


	/**
	 * Hooked to plugin-specific action links filters (by looping over 'active_plugins' option).
	 *
	 * @hooked plugin_action_links_{$basename} via closure. The closure needed to also pass the basename.
	 *
	 * @param array  $links_array The existing plugin links (usually "Deactivate").
	 * @param string $plugin_basename The plugin's directory/filename.php.
	 *
	 * @return array The links to display below the plugin name on plugins.php.
	 */
	public function plugin_specific_action_links( $links_array, $plugin_basename ) {

		$actions_nodes = array_map( array( $this, 'map_html_link_to_node' ), $links_array );

		$good_action_links = $this->get_good_action_links( $actions_nodes, $plugin_basename );

		return array_map( array( $this, 'map_node_to_html_link' ), $good_action_links );
	}


	/**
	 *
	 * Thankfully, plugin_row_meta runs after plugin_action_links, allowing us to move links from the more important
	 * column to the description column.
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

		$plugin_meta_anchor_nodes = array_map( array( $this, 'map_html_link_to_node' ), $plugin_meta );

		$plugin_meta_templates = array_map(
			function( $element ) {
				return preg_replace( '/<a.*\/a>/', '%anchor%', $element );
			},
			$plugin_meta
		);

		$plugin_meta_anchor_nodes = array_combine( $plugin_meta_templates, $plugin_meta_anchor_nodes );

		// Remove upsells.
		$plugin_meta_anchor_nodes = array_filter( $plugin_meta_anchor_nodes, array( $this, 'check_link_has_no_unwanted_terms' ) );

		// Remove formatting.
		$plugin_meta_anchor_nodes = array_map( array( $this, 'remove_formatting' ), $plugin_meta_anchor_nodes );

		// Replace (GitHub) links with icon.
		$plugin_meta_anchor_nodes = array_map( array( $this, 'replace_text_with_icons' ), $plugin_meta_anchor_nodes );

		$new_plugin_meta = array();

		foreach ( $plugin_meta_anchor_nodes as $template => $node ) {
			if ( $node instanceof DOMNode ) {
				$html_link         = $this->map_node_to_html_link( $node );
				$new_plugin_meta[] = str_replace( '%anchor%', $html_link, $template );
			} else {
				$new_plugin_meta[] = $template;
			}
		}

		// Merge external links from action links.
		if ( isset( $this->move_to_meta_column[ $plugin_file_name ] ) ) {
			$new_plugin_meta = array_merge(
				$new_plugin_meta,
				array_map(
					array( $this, 'map_node_to_html_link' ),
					$this->move_to_meta_column[ $plugin_file_name ]
				)
			);
		}

		return array_unique( $new_plugin_meta );

	}


	/**
	 * Runs the desired filters on the links, returning only good links.
	 *
	 * TODO: Use a proper array_sort().
	 *
	 * @param DOMNode[] $action_nodes Array of HTML anchors.
	 * @param string    $plugin_basename The plugin's directory/filename.php.
	 *
	 * @return string[]
	 */
	protected function get_good_action_links( $action_nodes, $plugin_basename ) {

		$deactivate_node = null;
		$settings_node   = null;

		// Remove upsells.
		$action_nodes = array_filter( $action_nodes, array( $this, 'check_link_has_no_unwanted_terms' ) );

		// Remove licence links.
		$action_nodes = array_filter( $action_nodes, array( $this, 'check_link_is_not_licence' ) );

		// Remove external links.
		$action_nodes = array_filter(
			$action_nodes,
			function( $action_link ) use ( $plugin_basename ) {
				return $this->check_link_has_no_external_url( $action_link, $plugin_basename );
			}
		);

		$action_nodes = array_map( array( $this, 'remove_formatting' ), $action_nodes );

		$good_action_nodes = array();

		foreach ( $action_nodes as $action_node ) {

			/**
			 * "WooCommerce Sync for QuickBooks Online - by MyWorks Software" has an empty entry in its action links.
			 */
			if ( ! ( $action_node instanceof DOMNode ) ) {
				continue;
			}

			// Nothing to do on deactivated plugins.
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( strstr( $action_node->nodeValue, 'Activate' ) ) {
				return $action_nodes;
			}

			// Grab the deactivate link so it can be added to the end.
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( strstr( $action_node->nodeValue, 'Deactivate' ) ) {
				$deactivate_node = $action_node;
				continue;
			}

			// Grab the settings link so it can be moved to the start.
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( strstr( $action_node->nodeValue, 'Settings' ) ) {
				$settings_node = $action_node;
				continue;
			}

			$good_action_nodes[] = $action_node;
		}

		// Place the Settings link at the beginning.
		if ( ! is_null( $settings_node ) ) {
			array_unshift( $good_action_nodes, $settings_node );

		}

		// Place the Deactivate link at the end.
		$good_action_nodes[] = $deactivate_node;

		return $good_action_nodes;
	}

	/**
	 * Filter to remove upsells and marketing links.
	 * .
	 * "Donate" links are not removed.
	 *
	 * @param DOMNode $link The HTML anchor.
	 *
	 * @return bool True if the link should remain, false to remove.
	 */
	protected function check_link_has_no_unwanted_terms( ?DOMNode $link ): bool {

		if ( is_null( $link ) ) {
			return true;
		}

		$unwanted_terms = array(
			'opt in',
			'opt-in',
			'add on',
			'add-on',
			'free',
			'upgrade',
			'trial',
		);

		foreach ( $unwanted_terms as $term ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( stristr( $link->nodeValue, $term ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks is the action links an internal link.
	 * Records external links in an instance array keyed with the plugin basename so they
	 * can be added to the description column.
	 *
	 * @param DOMNode $action_node The HTML anchor.
	 * @param string  $plugin_basename The plugin's directory/filename.php.
	 *
	 * @return bool True if no external URL (i.e. array_filter should keep it).
	 */
	protected function check_link_has_no_external_url( ?DOMNode $action_node, string $plugin_basename ): bool {

		if ( is_null( $action_node ) ) {
			return true;
		}

		$other_domain_match_pattern = '/http(?!' . preg_quote( substr( get_site_url(), 4 ), '/' ) . ')/';

		$href = $action_node->attributes->getNamedItem( 'href' )->nodeValue;

		// Check for external domains.
		if ( 1 === preg_match( $other_domain_match_pattern, $href ) ) {
			if ( ! isset( $this->move_to_meta_column[ $plugin_basename ] ) ) {
				$this->move_to_meta_column[ $plugin_basename ] = array();
			}
			$this->move_to_meta_column[ $plugin_basename ][] = $action_node;

			return false;
		}

		return true;

	}

	/**
	 * Filter to check for Licence links.
	 *
	 * TODO: A dismissible admin banner should be used to keep the link available for a few days after plugin activation.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 *
	 * @param DOMNode $action_link The HTML anchor.
	 *
	 * @return bool True if it is not a licence link (i.e. keep the link).
	 */
	protected function check_link_is_not_licence( ?DOMNode $action_link ): bool {

		if ( is_null( $action_link ) ) {
			return true;
		}

		return ! ( stristr( $action_link->nodeValue, 'licence' )
				|| stristr( $action_link->nodeValue, 'license' ) );
	}

	/**
	 * Remove CSS styles and classes.
	 *
	 * @param ?DOMNode $link The HTML anchor link.
	 *
	 * @return string
	 */
	protected function remove_formatting( ?DOMNode $link ): ?DOMNode {

		if ( is_null( $link ) ) {
			return $link;
		}

		$link->setAttribute( 'style', null );

		if ( is_null( $link->attributes->getNamedItem( 'class' ) ) ) {
			return $link;
		}

		$link_css_classes = $link->attributes->getNamedItem( 'class' )->textContent;

		$allowable_css_classes = array( 'thickbox', 'open-plugin-details-modal' );

		$allowed_classes = array_filter(
			explode( ' ', $link_css_classes ),
			function( $css_class ) use ( $allowable_css_classes ) {
				return in_array( $css_class, $allowable_css_classes, true );
			}
		);

		$link->setAttribute( 'class', implode( ' ', $allowed_classes ) );

		return $link;
	}

	/**
	 * Looks for GitHub links and replaces them with the GitHub icon.
	 *
	 * TODO: Add the original text as a mouseover hint.
	 * TODO: Pulling the icon straight from GitHub probably isn't best practice.
	 * TODO: WordPress.org links with WordPress icon?
	 *
	 * @param DOMNode $link_node The HTML anchor.
	 *
	 * @return string
	 */
	protected function replace_text_with_icons( ?DOMNode $link_node ): ?DOMNode {

		if ( is_null( $link_node ) ) {
			return $link_node;
		}

		$href = $link_node->attributes->getNamedItem( 'href' )->nodeValue;

		$style = "background: url('https://github.com/favicon.ico'); background-size: contain; height: 14px; width: 14px;  display: inline-block; vertical-align: middle; margin-top: -1px;";

		$match_github_repo_links = '/https?:\/\/github.com\/(?!sponsors)[^\/]*\/[^\/]*\/?$/i';

		if ( 1 === preg_match( $match_github_repo_links, $href ) ) {

			$link_node->setAttribute( 'style', $style );
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$link_node->nodeValue = '';

		}

		return $link_node;

	}

}
