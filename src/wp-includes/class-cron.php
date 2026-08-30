<?php
/**
 * Schedule and handle cron events.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

namespace BrianHenryIE\WP_Plugins_Page\WP_Includes;

use BrianHenryIE\WP_Plugins_Page\API\API;

class Cron {

	protected API $api;

	public function __construct( API $api ) {
		$this->api = $api;
	}

	public function schedule_hourly_update(): void {

	}

	public function hourly_update(): void {

	}
}
