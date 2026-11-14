<?php
/**
 * Studio-style SVG mug renders for variants that have no tile image. A PHP port of
 * scripts/make-mock-images.mjs, so products added in the admin look like the demo ones.
 */

defined( 'ABSPATH' ) || exit;

const EVANZA_RENDER_CX     = 330;
const EVANZA_RENDER_BOTTOM = 560;

function evanza_render_shapes() {
	$shapes          = array(
		'classic'  => array( 'top' => 250, 'rt' => 150, 'rb' => 140, 'bulge' => 0, 'ry' => 27, 'hy' => array( 58, 82 ), 'reach' => 112 ),
		'latte'    => array( 'top' => 250, 'rt' => 172, 'rb' => 98, 'bulge' => 0, 'ry' => 29, 'hy' => array( 58, 82 ), 'reach' => 112 ),
		'belly'    => array( 'top' => 262, 'rt' => 132, 'rb' => 112, 'bulge' => 24, 'ry' => 25, 'hy' => array( 52, 78 ), 'reach' => 104 ),
		'espresso' => array( 'top' => 340, 'rt' => 118, 'rb' => 92, 'bulge' => 6, 'ry' => 22, 'hy' => array( 38, 56 ), 'reach' => 74 ),
	);
	$shapes['heart'] = $shapes['classic'];
	return $shapes;
}

/** Mixes a #rrggbb colour toward white (amount > 0) or black (amount < 0). */
function evanza_render_shade( $hex, $amount ) {
	$target = $amount > 0 ? 255 : 0;
	$out    = '#';
	foreach ( array( 1, 3, 5 ) as $i ) {
		$v    = hexdec( substr( $hex, $i, 2 ) );
		$v    = $v + ( $target - $v ) * abs( $amount );
		$out .= str_pad( dechex( max( 0, min( 255, (int) round( $v ) ) ) ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}

function evanza_render_half_width( $s, $y ) {
	$bottom = EVANZA_RENDER_BOTTOM;
	$t      = ( $y - $s['top'] ) / ( $bottom - $s['top'] );
	$hw     = $s['rt'] + ( $s['rb'] - $s['rt'] ) * $t + $s['bulge'] * sin( M_PI * $t );
	$rc     = 42;
	if ( $y > $bottom - $rc ) {
		$u   = ( $y - ( $bottom - $rc ) ) / $rc;
		$hw -= $rc * ( 1 - sqrt( max( 0, 1 - $u * $u ) ) );
	}
	return $hw;
}

function evanza_render_f( $n ) {
	return number_format( $n, 1, '.', '' );
}

function evanza_render_body_path( $s ) {
	$cx   = EVANZA_RENDER_CX;
	$ys   = range( $s['top'], EVANZA_RENDER_BOTTOM, 4 );
	if ( end( $ys ) !== EVANZA_RENDER_BOTTOM ) {
		$ys[] = EVANZA_RENDER_BOTTOM;
	}
	$left  = array();
	$right = array();
	foreach ( $ys as $y ) {
		$left[] = evanza_render_f( $cx - evanza_render_half_width( $s, $y ) ) . ' ' . $y;
	}
	foreach ( array_reverse( $ys ) as $y ) {
		$right[] = evanza_render_f( $cx + evanza_render_half_width( $s, $y ) ) . ' ' . $y;
	}
	$hb = evanza_render_half_width( $s, EVANZA_RENDER_BOTTOM );
	return 'M' . implode( ' L', $left ) . ' A' . evanza_render_f( $hb ) . ' 15 0 0 0 ' . $right[0] . ' L' . implode( ' L', array_slice( $right, 1 ) ) . ' A' . $s['rt'] . ' ' . $s['ry'] . ' 0 0 1 ' . evanza_render_f( $cx - $s['rt'] ) . ' ' . $s['top'] . ' Z';
}

function evanza_render_handle( $shape, $s, $color ) {
	$cx    = EVANZA_RENDER_CX;
	$dark  = evanza_render_shade( $color, -0.28 );
	$light = evanza_render_shade( $color, 0.3 );
	$y1    = $s['top'] + $s['hy'][0];
	$y2    = EVANZA_RENDER_BOTTOM - $s['hy'][1];
	$x1    = $cx + evanza_render_half_width( $s, $y1 ) - 8;
	$x2    = $cx + evanza_render_half_width( $s, $y2 ) - 8;
	if ( 'heart' === $shape ) {
		$heart = 'M0 -22C-44 -66-110 -22-64 34L0 92 64 34C110-22 44-66 0-22Z';
		$hx    = $cx + evanza_render_half_width( $s, 400 ) + 70;
		return '<g transform="translate(' . evanza_render_f( $hx ) . ' 392) rotate(90) scale(.95)" fill="none" stroke-linejoin="round">'
			. '<path d="' . $heart . '" stroke="' . $dark . '" stroke-width="28"/><path d="' . $heart . '" stroke="' . $color . '" stroke-width="22"/>'
			. '<path d="' . $heart . '" stroke="' . $light . '" stroke-opacity=".55" stroke-width="5" transform="translate(-3 -3)"/></g>';
	}
	$d = 'M' . evanza_render_f( $x1 ) . ' ' . $y1 . ' C' . evanza_render_f( $x1 + $s['reach'] ) . ' ' . ( $y1 - 14 ) . ' ' . evanza_render_f( $x2 + $s['reach'] + 12 ) . ' ' . ( $y2 + 8 ) . ' ' . evanza_render_f( $x2 ) . ' ' . $y2;
	return '<g fill="none" stroke-linecap="round"><path d="' . $d . '" stroke="' . $dark . '" stroke-width="30"/>'
		. '<path d="' . $d . '" stroke="' . $color . '" stroke-width="24"/>'
		. '<path d="' . $d . '" stroke="' . $light . '" stroke-opacity=".6" stroke-width="5" transform="translate(-2 -5)"/></g>';
}

/** The SVG markup for a mug in the given colours (each #rrggbb). */
function evanza_render_svg( $shape, $body, $inner, $handle ) {
	$shapes = evanza_render_shapes();
	$s      = isset( $shapes[ $shape ] ) ? $shapes[ $shape ] : $shapes['classic'];
	$cx     = EVANZA_RENDER_CX;
	$bottom = EVANZA_RENDER_BOTTOM;
	$d      = evanza_render_body_path( $s );
	$rim    = evanza_render_shade( $body, 0.1 );
	$edge   = evanza_render_shade( $inner, -0.25 );
	$hw_mid = evanza_render_half_width( $s, ( $s['top'] + $bottom ) / 2 );
	$hw_bot = evanza_render_half_width( $s, $bottom - 40 );
	$top    = $s['top'];
	$rt     = $s['rt'];
	$ry     = $s['ry'];

	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="105 105 560 560" width="800" height="800" role="img">' . "\n<defs>\n";
	$svg .= '<linearGradient id="shade" gradientUnits="userSpaceOnUse" x1="' . ( $cx - $rt - 4 ) . '" y1="0" x2="' . ( $cx + $rt + 4 ) . '" y2="0"><stop offset="0" stop-color="#000" stop-opacity=".34"/><stop offset=".1" stop-color="#000" stop-opacity=".12"/><stop offset=".28" stop-color="#fff" stop-opacity=".14"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/><stop offset=".8" stop-color="#000" stop-opacity=".12"/><stop offset="1" stop-color="#000" stop-opacity=".42"/></linearGradient>' . "\n";
	$svg .= '<linearGradient id="occl" x1="0" y1="' . $top . '" x2="0" y2="' . $bottom . '" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#000" stop-opacity="0"/><stop offset=".45" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".2"/></linearGradient>' . "\n";
	$svg .= '<linearGradient id="cavity" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . evanza_render_shade( $inner, -0.5 ) . '"/><stop offset=".55" stop-color="' . evanza_render_shade( $inner, -0.22 ) . '"/><stop offset="1" stop-color="' . evanza_render_shade( $inner, 0.04 ) . '"/></linearGradient>' . "\n";
	$svg .= '<clipPath id="body"><path d="' . $d . '"/></clipPath>' . "\n";
	$svg .= '<filter id="soft" x="-30%" y="-100%" width="160%" height="300%"><feGaussianBlur stdDeviation="10"/></filter>' . "\n";
	$svg .= '<filter id="tight" x="-30%" y="-100%" width="160%" height="300%"><feGaussianBlur stdDeviation="4"/></filter>' . "\n";
	$svg .= '<filter id="blur3" x="-50%" y="-10%" width="200%" height="120%"><feGaussianBlur stdDeviation="3.2"/></filter>' . "\n";
	$svg .= '<filter id="blur1"><feGaussianBlur stdDeviation="1"/></filter>' . "\n</defs>\n";
	$svg .= '<ellipse cx="' . ( $cx + 14 ) . '" cy="' . ( $bottom + 8 ) . '" rx="' . round( $hw_bot * 1.35 ) . '" ry="17" fill="#000" opacity=".26" filter="url(#soft)"/>' . "\n";
	$svg .= '<ellipse cx="' . ( $cx + 4 ) . '" cy="' . ( $bottom + 3 ) . '" rx="' . round( $hw_bot * 0.95 ) . '" ry="8" fill="#000" opacity=".4" filter="url(#tight)"/>' . "\n";
	$svg .= evanza_render_handle( $shape, $s, $handle ) . "\n";
	$svg .= '<ellipse cx="' . $cx . '" cy="' . $top . '" rx="' . $rt . '" ry="' . $ry . '" fill="' . $rim . '"/>' . "\n";
	$svg .= '<ellipse cx="' . $cx . '" cy="' . ( $top + 1 ) . '" rx="' . ( $rt - 11 ) . '" ry="' . ( $ry - 6.5 ) . '" fill="' . $edge . '"/>' . "\n";
	$svg .= '<ellipse cx="' . $cx . '" cy="' . ( $top + 2 ) . '" rx="' . ( $rt - 13 ) . '" ry="' . ( $ry - 8 ) . '" fill="url(#cavity)"/>' . "\n";
	$svg .= '<path d="' . $d . '" fill="' . $body . '"/>' . "\n";
	$svg .= '<path d="' . $d . '" fill="url(#shade)"/>' . "\n";
	$svg .= '<path d="' . $d . '" fill="url(#occl)"/>' . "\n";
	$svg .= '<g clip-path="url(#body)">' . "\n";
	$svg .= '<rect x="' . evanza_render_f( $cx - $hw_mid * 0.62 ) . '" y="' . ( $top + 24 ) . '" width="22" height="' . ( $bottom - $top - 70 ) . '" rx="11" fill="#fff" opacity=".5" filter="url(#blur3)"/>' . "\n";
	$svg .= '<rect x="' . evanza_render_f( $cx + $hw_mid * 0.7 ) . '" y="' . ( $top + 34 ) . '" width="7" height="' . ( $bottom - $top - 90 ) . '" rx="3.5" fill="#fff" opacity=".26" filter="url(#blur1)"/>' . "\n";
	$svg .= "</g>\n";
	$svg .= '<path d="M' . ( $cx - $rt + 12 ) . ' ' . ( $top + $ry * 0.55 ) . ' A' . ( $rt - 12 ) . ' ' . ( $ry - 3 ) . ' 0 0 0 ' . ( $cx - $rt * 0.35 ) . ' ' . ( $top + $ry - 1 ) . '" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="2.4" stroke-linecap="round" filter="url(#blur1)"/>' . "\n";
	$svg .= "</svg>\n";
	return $svg;
}

/** The render as a data URI, so it needs no file and survives a static export. */
function evanza_render_data_uri( $shape, $body, $inner, $handle ) {
	static $cache = array();
	$key = $shape . $body . $inner . $handle;
	if ( ! isset( $cache[ $key ] ) ) {
		$cache[ $key ] = 'data:image/svg+xml;base64,' . base64_encode( evanza_render_svg( $shape, $body, $inner, $handle ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}
	return $cache[ $key ];
}
