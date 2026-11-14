<?php
/**
 * Customizer: the contact details the enquiry buttons use, and the global catalogue PDF.
 */

defined( 'ABSPATH' ) || exit;

const EVANZA_DEFAULT_WHATSAPP = '919898798986';
const EVANZA_DEFAULT_EMAIL    = 'evanzatableware@gmail.com';

/** WhatsApp number in international format, digits only. */
function evanza_whatsapp_number() {
	$digits = preg_replace( '/\D/', '', (string) get_theme_mod( 'evanza_whatsapp', EVANZA_DEFAULT_WHATSAPP ) );
	return $digits ? $digits : EVANZA_DEFAULT_WHATSAPP;
}

function evanza_enquiry_email() {
	$email = get_theme_mod( 'evanza_email', EVANZA_DEFAULT_EMAIL );
	return is_email( $email ) ? $email : EVANZA_DEFAULT_EMAIL;
}

add_action( 'customize_register', 'evanza_customize_register' );
function evanza_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'evanza_enquiries',
		array(
			'title'       => __( 'Evanza enquiries', 'evanza' ),
			'priority'    => 30,
			'description' => __( 'Where the enquiry buttons send people, and the catalogue PDF they download.', 'evanza' ),
		)
	);

	$wp_customize->add_setting(
		'evanza_whatsapp',
		array(
			'default'           => EVANZA_DEFAULT_WHATSAPP,
			'sanitize_callback' => static function ( $value ) {
				return preg_replace( '/\D/', '', (string) $value );
			},
		)
	);
	$wp_customize->add_control(
		'evanza_whatsapp',
		array(
			'section'     => 'evanza_enquiries',
			'label'       => __( 'WhatsApp number', 'evanza' ),
			'description' => __( 'Country code first, digits only. India example: 919898798986', 'evanza' ),
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'evanza_email',
		array(
			'default'           => EVANZA_DEFAULT_EMAIL,
			'sanitize_callback' => 'sanitize_email',
		)
	);
	$wp_customize->add_control(
		'evanza_email',
		array(
			'section' => 'evanza_enquiries',
			'label'   => __( 'Enquiry email', 'evanza' ),
			'type'    => 'email',
		)
	);

	$wp_customize->add_setting( 'evanza_catalogue', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control(
		new WP_Customize_Upload_Control(
			$wp_customize,
			'evanza_catalogue',
			array(
				'section'     => 'evanza_enquiries',
				'label'       => __( 'Catalogue PDF', 'evanza' ),
				'description' => __( 'The global "Download catalogue" file. Leave empty to use the placeholder.', 'evanza' ),
			)
		)
	);
}
