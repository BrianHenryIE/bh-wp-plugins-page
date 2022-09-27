<?php
/**
 * Loads all required classes
 *
 * Uses classmap, PSR4 & wp-namespace-autoloader.
 *
 * @link              https://github.com/brianhenryie/bh-wp-plugins-page
 * @since             1.0.0
 * @package           brianhenryie/bh-wp-plugins-page
 *
 * @see https://github.com/pablo-sg-pacheco/wp-namespace-autoloader/
 */

namespace BrianHenryIE\WP_Plugins_Page;

use BrianHenryIE\WP_Plugins_Page\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;


$class_map_file = __DIR__ . '/autoload-classmap.php';
if ( file_exists( $class_map_file ) ) {

	$class_map = include $class_map_file;

	if ( is_array( $class_map ) ) {
		spl_autoload_register(
			function ( $classname ) use ( $class_map ) {

				if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
					require_once $class_map[ $classname ];
				}
			}
		);
	}
	unset( $class_map_file );
}


require_once __DIR__ . '/vendor-prefixed/autoload.php';

$wpcs_autoloader = new WP_Namespace_Autoloader( array( 'classes_dir' => array( 'src' ) ) );
$wpcs_autoloader->init();
