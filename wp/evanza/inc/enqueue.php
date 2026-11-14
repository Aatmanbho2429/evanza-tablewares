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
	$og = array(
		'og:type'        => 'website',
		'og:site_name'   => 'Evanza Tablewares',
		'og:title'       => wp_get_document_title(),
		'og:description' => 'Ceramic mug manufacturer in Morbi, Gujarat. Everyday mugs, gift sets, cafe and hotel ranges and custom printing, in bulk.',
		'og:image'       => evanza_asset_url( 'img/brand/mark-512.png' ),
		'og:url'         => home_url( add_query_arg( null, null ) ),
	);
	foreach ( $og as $property => $content ) {
		printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
	}
	echo '<meta name="twitter:card" content="summary">' . "\n";
	echo '<meta name="description" content="' . esc_attr( 'Evanza Tablewares is a ceramic mug manufacturer in Morbi, Gujarat. Everyday mugs, gift sets, cafe and hotel ranges and custom printing, in every glaze and in bulk.' ) . '">' . "\n";
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( evanza_asset_url( 'img/brand/mark-32.png' ) ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( evanza_asset_url( 'img/brand/mark-180.png' ) ) );
	}
}

// Readable document titles for the product archive.
add_filter( 'document_title_parts', 'evanza_document_title' );
function evanza_document_title( $parts ) {
	if ( is_post_type_archive( 'evanza_product' ) ) {
		$parts['title'] = 'Ceramic mug collection';
	}
	return $parts;
}

// Settings the enquiry list and product buttons read (Customizer values).
add_action( 'wp_footer', 'evanza_print_config', 1 );
function evanza_print_config() {
	$config = array(
		'whatsapp'   => evanza_whatsapp_number(),
		'email'      => evanza_enquiry_email(),
		'contactUrl' => home_url( '/contact/' ),
		'collection' => home_url( '/collection/' ),
		'siteName'   => 'Evanza Tablewares',
	);
	echo '<script type="application/json" id="evanza-config">' . wp_json_encode( $config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}
