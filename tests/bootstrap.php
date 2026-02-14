<?php
/**
 * @package brianhenryie/bh-wp-plugins-page
 */

use Alley_Interactive\Autoloader\Autoloader;

$GLOBALS['project_root_dir']   = $project_root_dir  = dirname( __DIR__, 1 );
$GLOBALS['plugin_root_dir']    = $plugin_root_dir   = $project_root_dir;
$GLOBALS['plugin_name']        = $plugin_name       = basename( $project_root_dir );
$GLOBALS['plugin_name_php']    = $plugin_name_php   = $plugin_name . '.php';
$GLOBALS['plugin_path_php']    = $plugin_root_dir . '/' . $plugin_name_php;
$GLOBALS['plugin_basename']    = $plugin_name . '/' . $plugin_name_php;
$GLOBALS['wordpress_root_dir'] = $project_root_dir . '/wordpress';

Autoloader::generate( 'BrianHenryIE\\WP_Plugins_Page', __DIR__ . '/unit' )->register();
Autoloader::generate( 'BrianHenryIE\\WP_Plugins_Page', __DIR__ . '/wpunit' )->register();

// Fix "sh: php: command not found" when running wpunit tests in PhpStorm.
$is_phpstorm = array_reduce(
	$GLOBALS['argv'],
	fn( bool $carry, string $arg ) => $carry || str_contains( $arg, 'PhpStorm' ),
	false
);
if ( $is_phpstorm ) {
	define( 'WP_PHP_BINARY', PHP_BINARY );
}
