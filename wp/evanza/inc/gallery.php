<?php
/**
 * The photographs used by the Gallery and About pages: image base (under assets/img) and caption.
 */

defined( 'ABSPATH' ) || exit;

function evanza_gallery_sets() {
	return array(
		'products'     => array(
			'title' => 'Products',
			'items' => array(
				array( 'products/016A1265', 'A navy latte mug' ),
				array( 'products/016A1271', 'A green classic mug' ),
				array( 'products/016A1290', 'Heart handle mugs in black and red' ),
				array( 'products/016A1307', 'A white mug with a navy heart handle' ),
				array( 'products/016A1315', 'A white mug with a black inside and handle' ),
				array( 'products/016A1383', 'Pastel espresso cups' ),
				array( 'products/016A1396', 'Mugs in every glaze' ),
				array( 'products/009-01', 'Black marble mugs with gold veining' ),
				array( 'products/012-01', 'A gradient mug in sand' ),
				array( 'products/IMG_5235-2', 'A pink gradient mug' ),
				array( 'products/001-2', 'A black gift set with a gold band' ),
				array( 'products/IMG_4551', 'A stack of glazes' ),
				array( 'products/IMG_4800-2', 'Cream-lined mugs in colour' ),
				array( 'products/IMG_4022', 'A custom-printed mug' ),
				array( 'products/IMG_5364-1', 'Valentine print mugs' ),
				array( 'products/IMG_3706', 'Mugs in teal, black and brown' ),
			),
		),
		'factory'      => array(
			'title' => 'Factory',
			'items' => array(
				array( 'factory/factory-exterior', 'The Evanza factory in Morbi' ),
				array( 'factory/raw-material', 'Clay loaded into the plant' ),
				array( 'factory/ball-mill', 'The ball mill' ),
				array( 'factory/clay-extruder', 'Clay extruded into billets' ),
				array( 'factory/forming', 'Forming' ),
				array( 'factory/pressing', 'Pressing a mug' ),
				array( 'factory/turntable', 'The forming turntable' ),
				array( 'factory/drying-racks', 'Mugs drying on racks' ),
				array( 'factory/bisque-racks', 'Bisque mugs on racks' ),
				array( 'factory/finishing', 'Finishing by hand' ),
				array( 'factory/handling', 'Mugs ready for glazing' ),
				array( 'factory/glazing', 'Glazing' ),
				array( 'factory/glazed-racks', 'Glazed mugs on racks' ),
				array( 'factory/kiln-control', 'The kiln control panel' ),
				array( 'factory/kiln-rollers', 'The kiln rollers' ),
				array( 'factory/kiln-line', 'The roller kiln line' ),
				array( 'factory/kiln-mugs', 'Mugs in the kiln' ),
				array( 'factory/packing', 'Packing' ),
				array( 'factory/dispatch', 'Loading a dispatch truck' ),
			),
		),
		'certificates' => array(
			'title' => 'Certificates',
			'items' => array(
				array( 'certificates/iso-9001', 'ISO 9001:2015 certificate' ),
				array( 'certificates/qsa-ce', 'QSA and CE certificates' ),
			),
		),
	);
}

/** One lightbox trigger: a button holding the picture. Needs lib/lightbox.js. */
function evanza_lightbox_item( $group, $base, $caption, $args = array() ) {
	$sources = evanza_image_sources( $base );
	if ( ! $sources ) {
		return '';
	}
	$large = end( $sources );
	return sprintf(
		'<button class="%s" type="button" data-lightbox="%s" data-src="%s" data-caption="%s" aria-label="%s">%s</button>',
		esc_attr( isset( $args['class'] ) ? $args['class'] : 'shelf__item' ),
		esc_attr( $group ),
		esc_url( $large['url'] ),
		esc_attr( $caption ),
		esc_attr( 'Open photo: ' . $caption ),
		evanza_img(
			$base,
			'',
			array(
				'sizes' => isset( $args['sizes'] ) ? $args['sizes'] : '(min-width: 48rem) 30rem, 80vw',
			)
		)
	); // phpcs:ignore WordPress.Security.EscapeOutput
}
