<?php
/**
 * Styles, scripts and <head> extras.
 */

defined( 'ABSPATH' ) || exit;

function evanza_asset_version( $relative ) {
	$file = EVANZA_DIR . '/assets/' . $relative;
	return file_exists( $file ) ? (string) filemtime( $file ) : EVANZA_VERSION;
}

/** Pages that load three.js. */
function evanza_uses_3d() {
	return is_front_page() || is_singular( 'evanza_product' );
}

add_action( 'wp_enqueue_scripts', 'evanza_enqueue_assets' );
function evanza_enqueue_assets() {
	$styles = array( 'tokens', 'base', 'components', 'layout', 'enquiry' );
	if ( is_front_page() ) {
		$styles[] = 'home';
	}
	if ( is_front_page() || is_post_type_archive( 'evanza_product' ) || is_singular( 'evanza_product' ) ) {
		$styles[] = 'tiles';
	}
	if ( is_post_type_archive( 'evanza_product' ) ) {
		$styles[] = 'collection';
	}
	if ( is_singular( 'evanza_product' ) ) {
		$styles[] = 'product';
	}
	if ( is_page( array( 'about', 'gallery', 'contact' ) ) ) {
		$styles[] = 'pages';
	}
	$previous = array();
	foreach ( $styles as $name ) {
		$handle = 'evanza-' . $name;
		wp_enqueue_style( $handle, evanza_asset_url( "css/{$name}.css" ), $previous, evanza_asset_version( "css/{$name}.css" ) );
		$previous = array( $handle );
	}
	wp_enqueue_script_module( 'evanza-main', evanza_asset_url( 'js/main.js' ), array(), evanza_asset_version( 'js/main.js' ) );
	if ( is_post_type_archive( 'evanza_product' ) ) {
		wp_enqueue_script_module( 'evanza-collection', evanza_asset_url( 'js/collection.js' ), array(), evanza_asset_version( 'js/collection.js' ) );
	}
	foreach ( array( 'about', 'gallery', 'contact' ) as $page_slug ) {
		if ( is_page( $page_slug ) ) {
			wp_enqueue_script_module( 'evanza-' . $page_slug, evanza_asset_url( "js/{$page_slug}.js" ), array(), evanza_asset_version( "js/{$page_slug}.js" ) );
		}
	}
	if ( is_singular( 'evanza_product' ) ) {
		wp_enqueue_script_module( 'evanza-product', evanza_asset_url( 'js/product.js' ), array(), evanza_asset_version( 'js/product.js' ) );
	}
	if ( is_front_page() ) {
		foreach ( array( 'home', 'story', 'home-lists' ) as $name ) {
			wp_enqueue_script_module( 'evanza-' . $name, evanza_asset_url( "js/{$name}.js" ), array(), evanza_asset_version( "js/{$name}.js" ) );
		}
	}
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

// Flag JS early. If the page scripts never run, a timeout reveals anything they would have prepared.
add_action( 'wp_head', 'evanza_head_js_flag', 0 );
function evanza_head_js_flag() {
	echo "<script>document.documentElement.className=document.documentElement.className.replace('no-js','js');setTimeout(function(){document.documentElement.classList.add('js-ready')},2500);</script>\n";
}

// Import map for three.js, only where it is used. Must come before any module script.
add_action( 'wp_head', 'evanza_head_importmap', 1 );
function evanza_head_importmap() {
	if ( ! evanza_uses_3d() ) {
		return;
	}
	$map = array(
		'imports' => array(
			'three'          => evanza_asset_url( 'vendor/three/three.module.js' ),
			'three/addons/'  => evanza_asset_url( 'vendor/three/addons/' ),
		),
	);
	echo '<script type="importmap">' . wp_json_encode( $map, JSON_UNESCAPED_SLASHES ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}

add_action( 'wp_head', 'evanza_head_extras', 2 );
function evanza_head_extras() {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( evanza_asset_url( 'fonts/bricolage-grotesque.woff2' ) ) );
	echo '<meta name="theme-color" content="#efefec">' . "\n";
	if ( evanza_uses_3d() ) {
		// The mug viewer is imported after the page paints; fetch it early so it is ready sooner.
		foreach ( array( 'js/mug3d.js', 'vendor/three/three.module.js', 'vendor/three/three.core.js', 'vendor/three/addons/environments/RoomEnvironment.js' ) as $module ) {
			printf( '<link rel="modulepreload" href="%s">' . "\n", esc_url( evanza_asset_url( $module ) ) );
		}
	}
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( evanza_asset_url( 'img/brand/mark-32.png' ) ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( evanza_asset_url( 'img/brand/mark-180.png' ) ) );
	}
}
