<?php
/**
 * Theme image helpers. Optimised images live in assets/img/<group>/<name>-<width>.webp
 * (see scripts/optimize-images.mjs).
 */

defined( 'ABSPATH' ) || exit;

function evanza_asset_url( $relative ) {
	return EVANZA_URI . '/assets/' . ltrim( $relative, '/' );
}

/**
 * Available widths for an image base, e.g. 'brand/logo-dark' => [240 => url, 480 => url].
 */
function evanza_image_sources( $base ) {
	static $cache = array();
	if ( isset( $cache[ $base ] ) ) {
		return $cache[ $base ];
	}
	$sources = array();
	foreach ( (array) glob( EVANZA_DIR . '/assets/img/' . $base . '-*.webp' ) as $file ) {
		if ( preg_match( '/-(\d+)\.webp$/', $file, $m ) ) {
			$sources[ (int) $m[1] ] = array(
				'url'  => EVANZA_URI . '/assets/img/' . $base . '-' . $m[1] . '.webp',
				'file' => $file,
			);
		}
	}
	ksort( $sources );
	$cache[ $base ] = $sources;
	return $sources;
}

/**
 * Returns an <img> with srcset, intrinsic size and lazy loading.
 *
 * @param string $base Path under assets/img without the width suffix, e.g. 'factory/glazing'.
 * @param string $alt  Alt text ('' for decorative images).
 * @param array  $args class, sizes, loading, fetchpriority.
 */
function evanza_img( $base, $alt = '', $args = array() ) {
	$args    = wp_parse_args(
		$args,
		array(
			'class'         => '',
			'sizes'         => '100vw',
			'loading'       => 'lazy',
			'fetchpriority' => '',
		)
	);
	$sources = evanza_image_sources( $base );
	if ( ! $sources ) {
		return '';
	}
	$widths  = array_keys( $sources );
	$largest = end( $widths );
	$size    = getimagesize( $sources[ $largest ]['file'] );
	$srcset  = array();
	foreach ( $sources as $w => $s ) {
		$srcset[] = esc_url( $s['url'] ) . ' ' . $w . 'w';
	}
	$smallest = $sources[ $widths[0] ];

	$html  = '<img src="' . esc_url( $smallest['url'] ) . '"';
	$html .= ' srcset="' . implode( ', ', $srcset ) . '" sizes="' . esc_attr( $args['sizes'] ) . '"';
	if ( $size ) {
		$html .= ' width="' . (int) $size[0] . '" height="' . (int) $size[1] . '"';
	}
	$html .= ' alt="' . esc_attr( $alt ) . '"';
	if ( $args['class'] ) {
		$html .= ' class="' . esc_attr( $args['class'] ) . '"';
	}
	$html .= ' loading="' . esc_attr( $args['loading'] ) . '" decoding="async"';
	if ( $args['fetchpriority'] ) {
		$html .= ' fetchpriority="' . esc_attr( $args['fetchpriority'] ) . '"';
	}
	return $html . '>';
}
