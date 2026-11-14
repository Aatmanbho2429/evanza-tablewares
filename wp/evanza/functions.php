<?php
/**
 * Evanza Tablewares theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'EVANZA_VERSION', '1.1.0' );
define( 'EVANZA_DIR', get_template_directory() );
define( 'EVANZA_URI', get_template_directory_uri() );

foreach ( array( 'setup', 'customizer', 'business', 'media', 'nav', 'enqueue', 'cpt', 'products', 'seed', 'gallery', 'contact' ) as $evanza_inc ) {
	require_once EVANZA_DIR . "/inc/{$evanza_inc}.php";
}
unset( $evanza_inc );
