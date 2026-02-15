<?php
/**
 * Prevent redirects away from plugins.php after plugin activation.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

/**
 * Filters wp_redirect to cancel redirecting away from plugins.php.
 */
class Plugins_Page {

	/**
	 * Captures redirects and cancels AJAX redirects during plugin-install, redirects to plugins.php for other requests.
	 *
	 * This really should cancel all redirects but the WordPress documentation encourages code that often ends in exit.
	 *
	 * @hooked wp_redirect
	 * @see wp_redirect()
	 *
	 * @param string     $location The URL to redirect to.
	 * @param int|string $status The HTTP status being used.
	 *
	 * @return string|bool A location or flag to cancel redirecting.
	 *
	 * phpcs:disable WordPress.Security.NonceVerification.Recommended
	 * phpcs:disable WordPress.Security.NonceVerification.Missing
	 */
	public function prevent_redirect( string $location, int|string $status ): string|bool {

		global $pagenow;

		if ( 'plugins.php' === $pagenow && false === stristr( $location, 'plugins.php' ) ) {

			return admin_url( add_query_arg( array_merge( $_POST, $_GET ), 'plugins.php' ) );
		}

		if ( 'admin-ajax.php' === $pagenow
			&& isset( $_POST['plugin-install'] )
			&& (
				! is_string( $_POST['plugin-install'] )
				|| 'plugin-install' === sanitize_text_field( wp_unslash( $_POST['plugin-install'] ) )
			)
		) {
			return false;
		}

		return $location;
	}
}
