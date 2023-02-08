<?php
/**
 * AJAX handler for edits to plugins.php
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class AJAX {
	use LoggerAwareTrait;

	protected API $api;

	public function __construct( API $api, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->api = $api;
	}

	public function set_plugin_name() {

		$plugin_basename = $_POST['pluginBasename'];
		$plugin_title    = $_POST['pluginName'];

		$result = $this->api->set_plugin_name( $plugin_basename, $plugin_title );

		wp_send_json_success( array( $result ) );
	}

}
