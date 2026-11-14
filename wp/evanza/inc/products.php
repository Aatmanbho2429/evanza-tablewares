<?php
/**
 * Product data layer. evanza_get_products() is the single source the templates and
 * the front-end JS read from; it returns the shape documented in PLAN.md.
 */

defined( 'ABSPATH' ) || exit;

/** The bundled demo data (data/products.json). Also the source for colour families. */
function evanza_demo_data() {
	static $data = null;
	if ( null === $data ) {
		$json = file_get_contents( EVANZA_DIR . '/data/products.json' );
		$data = $json ? json_decode( $json, true ) : array();
	}
	return is_array( $data ) ? $data : array();
}

/** Theme-relative asset paths become full URLs; absolute URLs pass through. */
function evanza_resolve_asset( $path ) {
	if ( ! $path ) {
		return '';
	}
	if ( preg_match( '#^(https?:)?//#', $path ) ) {
		return $path;
	}
	return EVANZA_URI . '/' . ltrim( $path, '/' );
}

/** Global "Download catalogue" PDF. */
function evanza_catalogue_url() {
	$custom = get_theme_mod( 'evanza_catalogue', '' );
	if ( $custom ) {
		return $custom;
	}
	$data = evanza_demo_data();
	return evanza_resolve_asset( isset( $data['catalogue'] ) ? $data['catalogue'] : 'assets/catalogues/catalogue-placeholder.pdf' );
}

function evanza_get_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'evanza_category',
			'hide_empty' => false,
			'orderby'    => 'term_id',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$out = array();
	foreach ( $terms as $term ) {
		$out[] = array(
			'slug'        => $term->slug,
			'name'        => wp_specialchars_decode( $term->name, ENT_QUOTES ),
			'description' => wp_specialchars_decode( $term->description, ENT_QUOTES ),
			'cover'       => evanza_resolve_asset( get_term_meta( $term->term_id, 'evanza_cover', true ) ),
			'catalogue'   => evanza_resolve_asset( get_term_meta( $term->term_id, 'evanza_catalogue', true ) ) ?: evanza_catalogue_url(),
		);
	}
	return $out;
}

function evanza_build_product( $post ) {
	$terms    = get_the_terms( $post, 'evanza_category' );
	$category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
	$variants = array();
	foreach ( (array) get_post_meta( $post->ID, '_evanza_variants', true ) as $v ) {
		$v['images'] = array_map( 'evanza_resolve_asset', isset( $v['images'] ) ? (array) $v['images'] : array() );
		$v['render'] = evanza_resolve_asset( isset( $v['render'] ) ? $v['render'] : ( $v['images'][0] ?? '' ) );
		$variants[]  = $v;
	}
	return array(
		'id'          => $post->ID,
		'slug'        => $post->post_name,
		'url'         => get_permalink( $post ),
		'name'        => wp_specialchars_decode( get_the_title( $post ), ENT_QUOTES ),
		'category'    => $category,
		'badges'      => array_values( (array) get_post_meta( $post->ID, '_evanza_badges', true ) ),
		'featured'    => (bool) get_post_meta( $post->ID, '_evanza_featured', true ),
		'shape'       => get_post_meta( $post->ID, '_evanza_shape', true ) ?: 'classic',
		'added'       => get_the_date( 'Y-m-d', $post ),
		'specs'       => (array) get_post_meta( $post->ID, '_evanza_specs', true ),
		'description' => wp_strip_all_tags( $post->post_content ),
		'variants'    => $variants,
	);
}

/** All published products with categories, colour families and the catalogue URL. */
function evanza_get_products() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$posts    = get_posts(
		array(
			'post_type'        => 'evanza_product',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'suppress_filters' => false,
		)
	);
	$demo     = evanza_demo_data();
	$products = array_map( 'evanza_build_product', $posts );
	$cache    = array(
		'categories'    => evanza_get_categories(),
		'colorFamilies' => isset( $demo['colorFamilies'] ) ? $demo['colorFamilies'] : array(),
		'catalogue'     => evanza_catalogue_url(),
		'products'      => $products,
	);
	return $cache;
}

/** One product by post ID or slug, or null. */
function evanza_get_product( $id_or_slug ) {
	foreach ( evanza_get_products()['products'] as $product ) {
		if ( (string) $product['id'] === (string) $id_or_slug || $product['slug'] === $id_or_slug ) {
			return $product;
		}
	}
	return null;
}

/** Prints the catalogue as inline JSON so the front-end needs no API calls. */
function evanza_print_products_json() {
	$json = wp_json_encode( evanza_get_products(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP );
	echo '<script type="application/json" id="evanza-products">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}

/** Room colour for a glaze: 35% of it over Chalk, or a dark room for very dark glazes. Mirrors roomFor() in lib/catalogue.js. */
function evanza_room_for( $hex ) {
	$rgb = static function ( $h ) {
		return array_map( 'hexdec', str_split( ltrim( $h, '#' ), 2 ) );
	};
	$mix = static function ( $a, $b, $t ) use ( $rgb ) {
		$x   = $rgb( $a );
		$y   = $rgb( $b );
		$out = array();
		foreach ( $x as $i => $v ) {
			$out[] = str_pad( dechex( (int) round( $v + ( $y[ $i ] - $v ) * $t ) ), 2, '0', STR_PAD_LEFT );
		}
		return '#' . implode( '', $out );
	};
	$lin = array_map(
		static function ( $v ) {
			$c = $v / 255;
			return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		},
		$rgb( $hex )
	);
	$lum = 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
	if ( $lum < 0.05 ) {
		return array( 'color' => $mix( $hex, '#efefec', 0.12 ), 'tone' => 'dark' );
	}
	return array( 'color' => $mix( '#efefec', $hex, 0.35 ), 'tone' => 'light' );
}
