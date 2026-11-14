<?php
/**
 * Styles, scripts and <head> extras.
 */

defined( 'ABSPATH' ) || exit;

function evanza_asset_version( $relative ) {
	$file = EVANZA_DIR . '/assets/' . $relative;
	return file_exists( $file ) ? (string) filemtime( $file ) : EVANZA_VERSION;
}

add_action( 'wp_enqueue_scripts', 'evanza_enqueue_assets' );
function evanza_enqueue_assets() {
	$previous = array();
	foreach ( array( 'tokens', 'base', 'components', 'layout' ) as $name ) {
		$handle = 'evanza-' . $name;
		wp_enqueue_style( $handle, evanza_asset_url( "css/{$name}.css" ), $previous, evanza_asset_version( "css/{$name}.css" ) );
		$previous = array( $handle );
	}
	wp_enqueue_script_module( 'evanza-main', evanza_asset_url( 'js/main.js' ), array(), evanza_asset_version( 'js/main.js' ) );
}

// Block, global and emoji styles are not used by this classic theme.
add_action( 'wp_enqueue_scripts', 'evanza_dequeue_core_styles', 100 );
function evanza_dequeue_core_styles() {
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Flag JS early so CSS can hide reveal-on-scroll content only when scripts run.
add_action( 'wp_head', 'evanza_head_js_flag', 0 );
function evanza_head_js_flag() {
	echo "<script>document.documentElement.className=document.documentElement.className.replace('no-js','js');</script>\n";
}

add_action( 'wp_head', 'evanza_head_extras', 2 );
function evanza_head_extras() {
	foreach ( array( 'instrument-serif-400.woff2', 'geist-variable.woff2' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( evanza_asset_url( 'fonts/' . $font ) ) );
	}
	echo '<meta name="theme-color" content="#f5f1ea">' . "\n";
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( evanza_asset_url( 'img/brand/mark-32.png' ) ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( evanza_asset_url( 'img/brand/mark-180.png' ) ) );
	}
}
