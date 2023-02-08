<?php
/**
 * Prevent redirects away from plugins.php after plugin activation.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

class Plugins_Page {

	/**
	 *
	 * This only captures pageloads on plugins.php, no AJAX redirects.
	 *
	 * @hooked wp_redirect
	 * @see wp_redirect()
	 *
	 * @return string|bool
	 */
	public function prevent_redirect( string $location, $status ): string {

		global $pagenow;

		if ( 'plugins.php' === $pagenow && false === stristr( $location, 'plugins.php' ) ) {

			// There's a problem when activating all plugins (after WC and this are active).

			// Problem does not occur when omitting:
			// wpmtst-getting-started - Strong Testimonials

			return admin_url( add_query_arg( array_merge( $_POST, $_GET ), 'plugins.php' ) );
		}

		if ( 'admin-ajax.php' === $pagenow && 'plugin-install' === $_POST['plugin-install'] ) {
			return false;
		}

		return $location;
	}

	/**
	 *
	 * Freemius plugins hook to admin_init and call a
	 *
	 * @hooked admin_init
	 *
	 * @return void
	 */
	public function add_hook_for_freemius_redirect(): void {

		global $pagenow;

		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		add_action( 'all', array( $this, 'prevent_freemius_redirect' ), 1 );

		// add_action(
		// 'admin_init',
		// function() {
		// remove_action( 'all', array( $this, 'prevent_freemius_redirect' ) );
		// },
		// 999
		// );
	}

	public function prevent_freemius_redirect() {

		$filter = current_filter();

		if ( 0 === strpos( $filter, 'fs_redirect_on_activation' ) ) {
			add_filter( $filter, '__return_false', 999 );

			return false;
			// add_filter( $filter, function() {
			// return false;
			// }, 999 );
		}

	}
}
