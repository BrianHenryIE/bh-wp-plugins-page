<?php
/**
 * Add .zip download link in the yellow "update available" banner.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

use stdClass;

/**
 * Append "v1.2.3 zip" as a download link to each plugin update notification.
 */
class Updates {

	/**
	 * Append "Download v1.2.3 zip" to each plugins.php yellow update notification.
	 *
	 * @hooked in_plugin_update_message-{plugin_basename}
	 *
	 * @param array<string,mixed> $plugin_data An array of plugin metadata. See get_plugin_data()
	 *                           and the {@see 'plugin_row_meta'} filter for the list
	 *                           of possible values.
	 * @param stdClass            $response {
	 *                An object of metadata about the available plugin update.
	 *
	 *     @type string   $id           Plugin ID, e.g. `w.org/plugins/[plugin-name]`.
	 *     @type string   $slug         Plugin slug.
	 *     @type string   $plugin       Plugin basename.
	 *     @type string   $new_version  New plugin version.
	 *     @type string   $url          Plugin URL.
	 *     @type string   $package      Plugin update package URL.
	 *     @type string[] $icons        An array of plugin icon URLs.
	 *     @type string[] $banners      An array of plugin banner URLs.
	 *     @type string[] $banners_rtl  An array of plugin RTL banner URLs.
	 *     @type string   $requires     The version of WordPress which the plugin requires.
	 *     @type string   $tested       The version of WordPress the plugin is tested against.
	 *     @type string   $requires_php The version of PHP which the plugin requires.
	 * }
	 */
	public function add_zip_download_link( array $plugin_data, stdClass $response ): void {

		if ( ! is_string( $response->package ) || sanitize_url( $response->package ) !== $response->package ) {
			return;
		}

		if ( ! is_string( $response->new_version ) ) {
			return;
		}

		$parsed_url = wp_parse_url( $response->package );

		if ( ! $parsed_url || ! isset( $parsed_url['path'] ) ) {
			return;
		}

		$filetype = wp_check_filetype( basename( $parsed_url['path'] ) )['ext'];

		if ( ! is_string( $filetype ) ) {
			return;
		}

		echo ' Download <a title="' . esc_attr( esc_url( $response->package ) ) . '" href="' . esc_url( $response->package ) . '" target="_top">v' . esc_html( $response->new_version ) . ' ' . esc_html( $filetype ) . '</a>.';
	}
}
