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

		return $this->get_good_action_links( $actions, $plugin_file );
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

		return $this->get_good_action_links( $links_array, $plugin_basename );
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

		// Remove upsells.
		$plugin_meta = array_filter( $plugin_meta, array( $this, 'check_link_has_no_unwanted_terms' ) );

		// Remove formatting.
		$plugin_meta = array_map( array( $this, 'remove_formatting' ), $plugin_meta );

		// Merge external links from action links.
		if ( isset( $this->move_to_meta_column[ $plugin_file_name ] ) ) {
			$plugin_meta = array_merge( $plugin_meta, $this->move_to_meta_column[ $plugin_file_name ] );
		}

		// Replace (GitHub) links with icon.
		$plugin_meta = array_map( array( $this, 'replace_text_with_icons' ), $plugin_meta );

		return array_unique( $plugin_meta );
	}


	/**
	 * Runs the desired filters on the links, returning only good links.
	 *
	 * TODO: Use a proper array_sort().
	 *
	 * @param string[] $action_links Array of HTML anchors.
	 * @param string   $plugin_basename The plugin's directory/filename.php.
	 *
	 * @return string[]
	 */
	protected function get_good_action_links( $action_links, $plugin_basename ) {

		$deactivate_link = null;
		$settings_link   = null;

		// Remove upsells.
		$action_links = array_filter( $action_links, array( $this, 'check_link_has_no_unwanted_terms' ) );

		// Remove licence links.
		$action_links = array_filter( $action_links, array( $this, 'check_link_is_not_licence' ) );

		// Remove external links.
		$action_links = array_filter(
			$action_links,
			function( $action_link ) use ( $plugin_basename ) {
				return $this->check_link_has_no_external_url( $action_link, $plugin_basename );
			}
		);

		$action_links = array_map( array( $this, 'remove_formatting' ), $action_links );

		$good_actions = array();

		foreach ( $action_links as $action_link ) {

			// Nothing to do on deactivated plugins.
			if ( strstr( $action_link, 'Activate' ) ) {
				return $action_links;
			}

			// Grab the deactivate link so it can be added to the end.
			if ( strstr( $action_link, 'Deactivate' ) ) {
				$deactivate_link = $action_link;
				continue;
			}

			// Grab the settings link so it can be moved to the start.
			if ( strstr( $action_link, 'Settings' ) ) {
				$settings_link = $action_link;
				continue;
			}

			$good_actions[] = $action_link;
		}

		// Place the Settings link at the beginning.
		if ( ! is_null( $settings_link ) ) {
			array_unshift( $good_actions, $settings_link );

		}

		// Place the Deactivate link at the end.
		$good_actions[] = $deactivate_link;

		return $good_actions;
	}

	/**
	 * Filter to remove upsells and marketing links.
	 * .
	 * "Donate" links are not removed.
	 *
	 * @param string $link The HTML anchor.
	 *
	 * @return bool True if the link should remain, false to remove.
	 */
	protected function check_link_has_no_unwanted_terms( $link ) {

		$unwanted_terms = array(
			'opt in',
			'opt-in',
			'add on',
			'add-on',
			'free',
			'upgrade',
			'trial',
			'premium',
		);

		foreach ( $unwanted_terms as $term ) {
			if ( stristr( $link, $term ) ) {
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
	 * @param string $action_link The HTML anchor.
	 * @param string $plugin_basename The plugin's directory/filename.php.
	 *
	 * @return bool True if no external URL (i.e. array_filter should keep it).
	 */
	protected function check_link_has_no_external_url( $action_link, $plugin_basename ) {

		$other_domain_match_pattern = '/<a href="http(?!' . preg_quote( substr( get_site_url(), 4 ), '/' ) . ')/';

		// Check for external domains.
		if ( 1 === preg_match( $other_domain_match_pattern, $action_link ) ) {
			if ( ! isset( $this->move_to_meta_column[ $plugin_basename ] ) ) {
				$this->move_to_meta_column[ $plugin_basename ] = array();
			}
			$this->move_to_meta_column[ $plugin_basename ][] = $action_link;

			return false;
		}

		return true;

	}

	/**
	 * Filter to check for Licence links.
	 *
	 * TODO: A dismissible admin banner should be used to keep the link available for a few days after plugin activation.
	 *
	 * @param string $action_link The HTML anchor.
	 *
	 * @return bool True if it is not a licence link (i.e. keep the link).
	 */
	protected function check_link_is_not_licence( $action_link ) {

		return ! ( stristr( $action_link, 'licence' )
				|| stristr( $action_link, 'license' ) );
	}

	/**
	 * Remove CSS styles and classes.
	 *
	 * TODO: Use wp_kses();
	 *
	 * @see wp_kses()
	 *
	 * @param string $link The HTML anchor link.
	 *
	 * @return string
	 */
	protected function remove_formatting( $link ) {

		// Remove styles.
		$link = preg_replace( '/\s(style="[^"]*")/', '$2', $link );

		// Remove classes.
		$link = preg_replace( '/\s(class="[^"]*")/', '$2', $link );

		return $link;
	}

	/**
	 * Looks for GitHub links and replaces them with the GitHub icon.
	 *
	 * TODO: Add the original text as a mouseover hint.
	 * TODO: Pulling the icon straight from GitHub probably isn't best practice.
	 * TODO: Should this only act on repo links?
	 * TODO: WordPress.org links with WordPress icon?
	 *
	 * @param string $link The HTML anchor.
	 *
	 * @return string
	 */
	protected function replace_text_with_icons( $link ) {

		if ( ! stristr( $link, 'GitHub.com/' ) ) {
			return $link;
		}

		$style = "background: url('https://github.com/favicon.ico'); background-size: contain; height: 14px; width: 14px;  display: inline-block; vertical-align: middle; margin-top: -1px;";

		if ( 1 === preg_match( '/".*?(github.com[^"]*)/', $link, $output_array ) ) {

			$github_link = untrailingslashit( $output_array[1] );

			$link = "<a target=\"_blank\" style=\"{$style}\" href=\"https://{$github_link}\"></a>";

		}

		return $link;

	}

}
