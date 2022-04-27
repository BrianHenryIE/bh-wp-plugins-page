<?php
/**
 * The main class in the plugin.
 * Hooked to actions and filters defined in WP_Plugins_List_Table.
 *
 * Actions links runs first, but needs to pull in links from meta links, so fires that filter during its run, which
 * is then fired again later during the normal meta links run.
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
use DOMElement;
use DOMNode;
use function tad\WPBrowser\strip_all_tags;

/**
 * Class Plugins_List_Table
 *
 * @package BH_WP_Plugins_Page\admin
 */
class Plugins_List_Table {

	/**
	 * Links found in the first column that should be moved to the middle column.
	 * indexed by plugin basename.
	 *
	 * @var array<array<int|string, string>>
	 */
	protected array $external_action_links = array();

	/**
	 * Links from the middle column that should be moved to the first column.
	 * indexed by plugin basename.
	 *
	 * @var array<array<int|string, string>>
	 */
	protected array $internal_meta_links = array();

	/**
	 * Hooked to plugin-specific action links filters (by looping over 'active_plugins' option).
	 *
	 * @hooked plugin_action_links_{$basename}
	 *
	 * @param array<int|string, string> $action_links The existing plugin links (usually "Deactivate").
	 * @param string                    $plugin_basename The plugin's directory/filename.php.
	 * @param array<int|string, mixed>  $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string                    $context     The plugin context. 'all'|'active'|'inactive'|'recently_activated'
	 *                                                |'upgrade'|'mustuse'|'dropins'|'search'.
	 *
	 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
	 */
	public function plugin_specific_action_links( array $action_links, string $plugin_basename, array $plugin_data, string $context ): array {

		/**
		 * Remove empty links.
		 * `myworks-woo-sync-for-quickbooks-online%2Fmyworks-woo-sync-for-quickbooks-online.php` was adding an empty entry!
		 */
		$action_links = array_filter( $action_links );

		// Save external links to move them to the middle column.
		$this->external_action_links[ $plugin_basename ] = array_filter( $action_links, array( $this, 'is_html_contains_external_link' ) );

		// Discard external links from the first column.
		$action_links = array_filter( $action_links, array( $this, 'is_not_html_contains_external_link' ) );

		// Get internal links from second column.
		apply_filters( 'plugin_row_meta', array(), $plugin_basename, $plugin_data, $context );
		$internal_meta_links = $this->internal_meta_links[ $plugin_basename ] ?? array();

		$action_links = $this->merge_assoc_arrays( $action_links, $internal_meta_links );

		$settings   = array();
		$links      = array();
		$log        = array();
		$deactivate = array();

		foreach ( $action_links as $key => $link ) {

			$anchor = $this->map_html_to_anchor_element( $link );

			// If there is no anchor, e.g. it is just text, we do not want it in the action links.
			if ( is_null( $anchor ) ) {
				continue;
			}

			if ( false !== stristr( $link, 'View details' ) ) {
				continue;
			}

			// Filter unwanted links.
			// Remove upsells.
			if ( ! $this->check_link_has_no_unwanted_terms( $link ) ) {
				continue;
			}

			// Remove licence links.
			if ( ! $this->check_link_is_not_licence( $link ) ) {
				continue;
			}

			if ( stristr( $link, 'settings' ) ) {
				if ( is_int( $key ) ) {
					$settings[] = $link;
				} else {
					$settings[ $key ] = $link;
				}
			} elseif ( stristr( $link, 'log' ) ) {
				if ( is_int( $key ) ) {
					$log[] = $link;
				} else {
					$log[ $key ] = $link;
				}
			} elseif ( stristr( $link, 'deactivate' ) ) {
				if ( is_int( $key ) ) {
					$deactivate[] = $link;
				} else {
					$deactivate[ $key ] = $link;
				}
			} else {
				if ( is_int( $key ) ) {
					$links[] = $link;
				} else {
					$links[ $key ] = $link;
				}
			}
		}

		// Reorder:
		// Move settings to the beginning.
		// Move Logs second to end.
		// Move Deactivate at the end.
		$action_links = array();
		$links_arrays = array( $settings, $links, $log, $deactivate );
		foreach ( $links_arrays as $links_array ) {
			$action_links = $this->merge_assoc_arrays( $action_links, $links_array );
		}

		$action_links = $this->remove_unwanted_html( $action_links );

		return $action_links;
	}


	/**
	 * Row meta is the middle column.
	 *
	 * Thankfully, plugin_row_meta runs after plugin_action_links, allowing us to move links from the more important
	 * column to the description column.
	 *
	 * @hooked plugin_row_meta
	 * Hooked at PHP_INT_MAX so all links have been added first.
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
	public function row_meta( $meta_links, $plugin_file_name, $plugin_data, $status ): array {

		// Get external links from first column.
		$external_action_links = $this->external_action_links[ $plugin_file_name ] ?? array();

		$meta_links = $this->merge_assoc_arrays( $meta_links, $external_action_links );

		// Filter unwanted links.
		// Remove upsells.
		$meta_links = array_filter( array_filter( $meta_links ), array( $this, 'check_link_has_no_unwanted_terms' ) );

		// Remove licence links.
		$meta_links = array_filter( $meta_links, array( $this, 'check_link_is_not_licence' ) );

		// Save internal links.
		$this->internal_meta_links[ $plugin_file_name ] = array_filter( $meta_links, array( $this, 'is_internal_link' ) );

		// Remove internal links.
		$meta_links = array_filter( $meta_links, array( $this, 'is_not_link_or_is_external_link_or_is_view_details_link' ) );

		$meta_links = $this->remove_unwanted_html( $meta_links );

		// Remove empty elements.
		$meta_links = array_filter( $meta_links, array( $this, 'is_not_empty_anchor_text' ) );

		// Replace (GitHub) links with icon.
		$meta_links = $this->replace_text_with_icons( $meta_links );

		return $meta_links;
	}

	/**
	 * Merge two associative arrays. If the primary array already has the same key, just append the value.
	 *
	 * TODO: Is there a PHP function for this already?
	 *
	 * @param array<string|int, mixed> $primary_array The array whose keys take precedence.
	 * @param array<string|int, mixed> $secondary_array The lesser array.
	 * @return array<string|int, mixed>
	 */
	protected function merge_assoc_arrays( array $primary_array, array $secondary_array ): array {

		foreach ( $secondary_array as $key => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}
			if ( is_int( $key ) ) {
				$primary_array[] = $value;
			} elseif ( array_key_exists( $key, $primary_array ) ) {
				$primary_array[] = $value;
			} else {
				$primary_array[ $key ] = $value;
			}
		}

		return $primary_array;
	}

	/**
	 *
	 * TODO: Are there CSS classes that need to be removed still? YES!
	 *
	 * @param array<int|string, string> $links The meta/action links in full.
	 * @return array<int|string, string>
	 */
	protected function remove_unwanted_html( array $links ): array {

		$allowed_html = array(
			'a' => array(
				'href'       => array(),
				'target'     => array(),
				'class'      => array(),
				'aria-label' => array(),
				'title'      => array(),
				'data-title' => array(),
			),
		);

		foreach ( $links as $key => $html_string ) {

			$cleaned_html_string = wp_kses( $html_string, $allowed_html );

			if ( $html_string !== $cleaned_html_string ) {
				$links[ $key ] = $cleaned_html_string;
			}
		}

		return $links;

	}


	/**
	 * Get the anchor as an object from the HTML string.
	 *
	 * Discards the rest of the HTML string (e.g. "Version...", "By...").
	 * Presumes one anchor per string.
	 *
	 * @param string $html_anchor_string A HTML string whose anchor we want to analyse.
	 *
	 * @return DOMElement|null
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 * phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
	 */

	protected function map_html_to_anchor_element( $html_anchor_string ): ?DOMNode {
		$dom_document                      = new DOMDocument();
		$dom_document->strictErrorChecking = false;

		$previous_internal_errors_value = libxml_use_internal_errors( true );
		$bool_result                    = @$dom_document->loadHTML( $html_anchor_string );
		libxml_use_internal_errors( $previous_internal_errors_value );

		if ( false === $bool_result ) {
			return null;
		}

		return $dom_document->getElementsByTagName( 'a' )->item( 0 );
	}

	/**
	 * Filter to remove upsells and marketing links.
	 *
	 * "Donate" links are not removed.
	 *
	 * @param string $html The full meta/action html string.
	 * @return bool True if the link should remain, false to remove.
	 */
	protected function check_link_has_no_unwanted_terms( string $html ): bool {

		$link = $this->map_html_to_anchor_element( $html );

		if ( is_null( $link ) ) {
			return true;
		}

		$definitely_unwanted_terms = array(
			'opt in',
			'opt-in',
			'add on',
			'add-on',
			'free',
			'upgrade',
			'trial',
			'review',
			'rate',
		);

		foreach ( $definitely_unwanted_terms as $term ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( stristr( $link->nodeValue, $term ) ) {
				return false;
			}
		}

		$probably_unwanted_terms = array(
			'pro',
			'premium',
		);

		foreach ( $probably_unwanted_terms as $term ) {
			if ( is_null( $link->attributes ) || is_null( $link->attributes->getNamedItem( 'href' ) ) ) {
				continue;
			}

			$hyperlink = $link->attributes->getNamedItem( 'href' )->nodeValue;

            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( preg_match( '/\b' . $term . '\b/i', $link->nodeValue ) && $this->is_external_link( $hyperlink ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks a bare url to see does it contain http and a domain other than this site's domain.
	 *
	 * @param string $url The URL to check.
	 * @return bool
	 */
	protected function is_external_link( string $url ): bool {

		if ( stristr( $url, 'http' ) && ! stristr( $url, get_site_url() ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks is it a relative URL or a link containing the site url.
	 *
	 * @param string $url The URL to check.
	 * @return bool
	 */
	protected function is_internal_link( string $url ): bool {
		return ! $this->is_external_link( $url );
	}

	/**
	 * Given a DOMElement, check is it an anchor link whose href points to an external site.
	 *
	 * @param DOMElement $anchor_node The A element.
	 * @return bool
	 */
	protected function is_anchor_element_external_link( DOMElement $anchor_node ): ?bool {

		if ( is_null( $anchor_node->attributes ) || is_null( $anchor_node->attributes->getNamedItem( 'href' ) ) ) {
			return null;
		}

		$url_string = $anchor_node->attributes->getNamedItem( 'href' )->nodeValue;

		return $this->is_external_link( $url_string );
	}

	/**
	 * Checks does the HTML contain, partially or totally, a HTML anchor.
	 *
	 * @param string $html_string A meta link, e.g. "By BrianHenryIE" where it is partially a hyperlink.
	 * @return bool|null Null when the string could not be parsed.
	 */
	protected function is_html_contains_external_link( string $html_string ): ?bool {

		$anchor_node = $this->map_html_to_anchor_element( $html_string );

		if ( is_null( $anchor_node ) ) {
			return false;
		}

		return $this->is_anchor_element_external_link( $anchor_node );
	}

	/**
	 * Determine is the HTML string NOT an external link.
	 *
	 * @param string $html_string A HTML string that may contain a link.
	 *
	 * @return bool
	 */
	protected function is_not_html_contains_external_link( string $html_string ): bool {
		$is_html_contains_external_link = $this->is_html_contains_external_link( $html_string );
		return is_null( $is_html_contains_external_link ) ? true : ! $is_html_contains_external_link;
	}

	/**
	 * Checks do we want to keep the link in the meta column.
	 *
	 * Checks is this any of
	 * * Not a link at all (i.e. the version)
	 * * Is the view details link
	 * * Is an external link
	 *
	 * @param string $html_string A HTML string from the array of links.
	 * @return bool
	 */
	protected function is_not_link_or_is_external_link_or_is_view_details_link( string $html_string ): ?bool {

		if ( stristr( $html_string, 'view details' ) ) {
			return true;
		}

		$anchor_node = $this->map_html_to_anchor_element( $html_string );

		if ( is_null( $anchor_node ) ) {
			return true;
		}

		return $this->is_anchor_element_external_link( $anchor_node );
	}

	/**
	 * Check is the anchor text empty. Some plugins have a JavaScript rating tool in the meta links which when
	 * removed by wp_kses, results in a string of only whitespace.
	 *
	 * @param string $html_string HTML containing and anchor which may not have any text.
	 * @return bool
	 */
	protected function is_not_empty_anchor_text( string $html_string ): bool {

		$node = $this->map_html_to_anchor_element( $html_string );

		if ( is_null( $node ) ) {
			return true;
		}

        //phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$text = trim( $node->nodeValue );

		return ! empty( $text );
	}

	/**
	 * Filter to check for Licence links.
	 *
	 * TODO: A dismissible admin banner should be used to keep the link available for a few days after plugin activation.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 *
	 * @param string $action_link The HTML anchor.
	 *
	 * @return bool True if it is not a licence link (i.e. keep the link).
	 */
	protected function check_link_is_not_licence( $action_link ): bool {

		$action_link = $this->map_html_to_anchor_element( $action_link );

		if ( is_null( $action_link ) ) {
			return true;
		}

		return ! ( stristr( $action_link->nodeValue, 'licence' )
				|| stristr( $action_link->nodeValue, 'license' ) );
	}

	/**
	 * Looks for GitHub links and replaces them with the GitHub icon.
	 *
	 * * Adds the original text as a mouseover hint.
	 * TODO: Pulling the icon straight from GitHub probably isn't best practice.
	 * TODO: WordPress.org links with WordPress icon?
	 *
	 * @param array<int|string, string> $meta_links The array of links.
	 * @return array<int|string, string>
	 */
	protected function replace_text_with_icons( array $meta_links ): array {

		$new_links = array();
		foreach ( $meta_links as $key => $html_string ) {

			$match_github_repo_links = '/https?:\/\/github.com\/(?!sponsors)[^\/]*\/[^\/]*\//i';

			if ( '<a' !== substr( $html_string, 0, 2 )
				|| '</a>' !== substr( $html_string, -4 )
				|| 1 !== preg_match( $match_github_repo_links, $html_string ) ) {
				$new_links[ $key ] = $html_string;
				continue;
			}

			$node = $this->map_html_to_anchor_element( $html_string );
			if ( is_null( $node ) ) {
				$new_links[ $key ] = $html_string;
				continue;
			}
			$style = "background: url('https://github.com/favicon.ico'); background-size: contain; height: 14px; width: 14px;  display: inline-block; vertical-align: middle; margin-top: -1px;";

			$old_text = $node->nodeValue;

			$node->setAttribute( 'style', $style );
			$node->setAttribute( 'title', $old_text );

            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$node->nodeValue = '';

			if ( is_null( $node->ownerDocument ) ) {
				$new_links[ $key ] = $html_string;
				continue;
			}

			$node_html = $node->ownerDocument->saveHTML();

			if ( false === $node_html ) {
				$new_links[ $key ] = $html_string;
				continue;
			}

			$new_links[ $key ] = $node_html;

		}

		return $new_links;

	}

}
