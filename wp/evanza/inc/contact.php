<?php
/**
 * Contact form: nonce, honeypot, sanitising, a saved copy of every enquiry and an email to the
 * enquiry address. The saved copy means nothing is lost if the host's mail is unreliable.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'evanza_register_enquiry_type' );
function evanza_register_enquiry_type() {
	register_post_type(
		'evanza_enquiry',
		array(
			'labels'          => array(
				'name'          => __( 'Enquiries', 'evanza' ),
				'singular_name' => __( 'Enquiry', 'evanza' ),
				'menu_name'     => __( 'Enquiries', 'evanza' ),
				'edit_item'     => __( 'Enquiry', 'evanza' ),
				'search_items'  => __( 'Search enquiries', 'evanza' ),
				'not_found'     => __( 'No enquiries yet', 'evanza' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email',
			'menu_position'   => 21,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}

/** The form fields, in order: name => label. */
function evanza_enquiry_fields() {
	return array(
		'name'     => 'Name',
		'company'  => 'Company',
		'email'    => 'Email',
		'phone'    => 'Phone',
		'country'  => 'Country',
		'interest' => 'Interested in',
		'message'  => 'Message',
	);
}

add_action( 'add_meta_boxes_evanza_enquiry', 'evanza_enquiry_meta_box' );
function evanza_enquiry_meta_box() {
	add_meta_box(
		'evanza-enquiry',
		__( 'Enquiry', 'evanza' ),
		static function ( $post ) {
			echo '<table class="form-table"><tbody>';
			foreach ( evanza_enquiry_fields() as $key => $label ) {
				printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( (string) get_post_meta( $post->ID, '_evanza_' . $key, true ) ) ) );
			}
			printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Email sent', 'evanza' ), get_post_meta( $post->ID, '_evanza_mailed', true ) ? 'Yes' : 'No, check the host mail settings' );
			echo '</tbody></table>';
		},
		'evanza_enquiry',
		'normal',
		'high'
	);
}

add_action( 'admin_post_nopriv_evanza_contact', 'evanza_handle_contact' );
add_action( 'admin_post_evanza_contact', 'evanza_handle_contact' );
function evanza_handle_contact() {
	$wants_json = false !== strpos( isset( $_SERVER['HTTP_ACCEPT'] ) ? wp_unslash( $_SERVER['HTTP_ACCEPT'] ) : '', 'application/json' ); // phpcs:ignore

	if ( ! isset( $_POST['_evanza_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_evanza_nonce'] ) ), 'evanza_contact' ) ) {
		evanza_contact_respond( false, array( 'form' => 'This page has expired. Reload it and send the form again.' ), array(), $wants_json, 403 );
	}

	// Honeypot: bots fill the hidden field. They get the same answer as a person, and nothing is saved.
	if ( ! empty( $_POST['website'] ) ) {
		evanza_contact_respond( true, array(), array(), $wants_json );
	}

	$in = array(
		'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'company'  => sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) ),
		'email'    => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'phone'    => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'country'  => sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) ),
		'interest' => sanitize_text_field( wp_unslash( $_POST['interest'] ?? '' ) ),
		'message'  => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
	);

	$errors = array();
	if ( '' === $in['name'] ) {
		$errors['name'] = 'Enter your name.';
	}
	if ( ! is_email( $in['email'] ) ) {
		$errors['email'] = 'Enter a valid email address.';
	}
	if ( strlen( $in['message'] ) < 5 ) {
		$errors['message'] = 'Tell us what you are looking for.';
	}
	if ( $in['phone'] && ! preg_match( '/^[0-9+()\-\s.]{5,25}$/', $in['phone'] ) ) {
		$errors['phone'] = 'Use digits, spaces and + only.';
	}
	if ( $errors ) {
		evanza_contact_respond( false, $errors, $in, $wants_json, 422 );
	}

	// At most 5 enquiries per visitor every 10 minutes.
	$bucket = 'evanza_rate_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown' );
	$count  = (int) get_transient( $bucket );
	if ( $count >= 5 ) {
		evanza_contact_respond( false, array( 'form' => 'Too many enquiries just now. Please try again in a few minutes.' ), $in, $wants_json, 429 );
	}
	set_transient( $bucket, $count + 1, 10 * MINUTE_IN_SECONDS );

	$title   = sprintf( '%s%s', $in['name'], $in['company'] ? ' (' . $in['company'] . ')' : '' );
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'evanza_enquiry',
			'post_status' => 'publish',
			'post_title'  => $title,
		)
	);
	if ( ! $post_id || is_wp_error( $post_id ) ) {
		evanza_contact_respond( false, array( 'form' => 'We could not save your enquiry. Please email us instead.' ), $in, $wants_json, 500 );
	}
	foreach ( $in as $key => $value ) {
		update_post_meta( $post_id, '_evanza_' . $key, $value );
	}

	$lines = array();
	foreach ( evanza_enquiry_fields() as $key => $label ) {
		$lines[] = $label . ': ' . $in[ $key ];
	}
	$mailed = wp_mail(
		evanza_enquiry_email(),
		'Website enquiry from ' . $title,
		implode( "\n", $lines ),
		array( 'Reply-To: ' . $in['name'] . ' <' . $in['email'] . '>' )
	);
	update_post_meta( $post_id, '_evanza_mailed', $mailed ? 1 : 0 );
	if ( ! $mailed ) {
		error_log( 'Evanza: enquiry ' . $post_id . ' was saved but the email could not be sent.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}

	evanza_contact_respond( true, array(), array(), $wants_json );
}

/** JSON for the script, or a redirect back to the contact page for a plain form post. */
function evanza_contact_respond( $ok, $errors, $values, $wants_json, $status = 200 ) {
	if ( $wants_json ) {
		wp_send_json( array( 'ok' => $ok, 'errors' => $errors ), $ok ? 200 : $status );
	}
	$url = home_url( '/contact/' );
	if ( $ok ) {
		$url = add_query_arg( 'sent', '1', $url );
	} else {
		$token = wp_generate_password( 12, false );
		set_transient( 'evanza_form_' . $token, array( 'errors' => $errors, 'values' => $values ), 10 * MINUTE_IN_SECONDS );
		$url = add_query_arg( 'e', $token, $url );
	}
	wp_safe_redirect( $url . '#form' );
	exit;
}
