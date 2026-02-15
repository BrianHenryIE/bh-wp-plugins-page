<?php
/**
 * Runs after WordPress has been initialised (after plugins are loaded) and before tests are run.
 *
 * @package brianhenryie/bh-wp-plugins-page
 */

add_filter(
	'plugins_url',
	function ( $url, $path, $plugin ) {
		$plugin_dir_name = basename( codecept_root_dir() );
		return str_replace( codecept_root_dir(), '/' . $plugin_dir_name . '/', $url );
	},
	10,
	3
);
