<?php
/**
 * Business details used across the theme (footer, contact page, WhatsApp links).
 */

defined( 'ABSPATH' ) || exit;

function evanza_business() {
	return array(
		'name'     => 'Evanza Tablewares',
		'tagline'  => 'Ceramic mugs for every table.',
		'since'    => 2022,
		'email'    => evanza_enquiry_email(),
		'whatsapp' => evanza_whatsapp_number(),
		'address'  => 'Survey no. 79/2 79/3, Pipaliya Maliya Highway, At. Chachavadarda, Tal: Maliya, Morbi, Gujarat, India',
		'people'   => array(
			array( 'name' => 'Amish Patel', 'role' => 'Managing Director', 'phone' => '9898798986' ),
			array( 'name' => 'Darshan Patel', 'role' => 'Managing Director', 'phone' => '6353898249' ),
			array( 'name' => 'Ayush Kalariya', 'role' => 'Managing Director', 'phone' => '7433003208' ),
			array( 'name' => 'Shrey Manvar', 'role' => 'Managing Director', 'phone' => '9409326000' ),
		),
	);
}

/** "9898798986" -> "+91 98987 98986" */
function evanza_format_phone( $digits ) {
	return '+91 ' . substr( $digits, 0, 5 ) . ' ' . substr( $digits, 5 );
}
