<?php
/**
 * AJAX handler for edits to plugins.php
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\Admin;

use BrianHenryIE\WP_Plugins_Page\API\API;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Handle the wp-ajax request.
 */
class AJAX {
	use LoggerAwareTrait;

	/**
	 * Constructor
	 *
	 * @param API             $api The plugin functions.
	 * @param LoggerInterface $logger A PSR logger.
	 */
	public function __construct(
		protected API $api,
		LoggerInterface $logger
	) {
		$this->setLogger( $logger );
	}

	/**
	 * Validates the AJAX request, use API to act, returns JSON response.
	 *
	 * @see API::set_plugin_name()
	 *
	 * @hooked wp_ajax_bh_wp_plugins_page_set_plugin_name
	 */
	public function set_plugin_name(): void {

		if ( ! check_ajax_referer( self::class, false, false ) ) {
			wp_send_json_error( array( 'message' => 'Bad/no nonce.' ), 401 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 401 );
		}

		if ( ! isset( $_POST['pluginBasename'], $_POST['pluginName'] ) ) {
			wp_send_json_error( array( 'message' => 'Bad request.' ), 400 );
		}

		$plugin_basename = sanitize_text_field( wp_unslash( $_POST['pluginBasename'] ) );
		$plugin_name     = sanitize_text_field( wp_unslash( $_POST['pluginName'] ) );

		try {
			$result = $this->api->set_plugin_name( $plugin_basename, $plugin_name );
		} catch ( \Exception $exception ) {
			wp_send_json_error( array( 'message' => $exception->getMessage() ), 500 );
		}

		wp_send_json_success( $result );
	}
}
