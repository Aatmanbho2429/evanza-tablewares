<?php
/**
 * Imports the demo products from data/products.json when the theme is activated.
 * Does nothing if any product already exists, so re-activating never duplicates.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'evanza_activated', 'evanza_seed_products' );
function evanza_seed_products() {
	$existing = get_posts(
		array(
			'post_type'   => 'evanza_product',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		return;
	}
	$data = evanza_demo_data();
	if ( empty( $data['products'] ) ) {
		return;
	}

	foreach ( $data['categories'] as $category ) {
		$term = term_exists( $category['slug'], 'evanza_category' );
		if ( ! $term ) {
			$term = wp_insert_term(
				$category['name'],
				'evanza_category',
				array(
					'slug'        => $category['slug'],
					'description' => $category['description'],
				)
			);
		}
		if ( ! is_wp_error( $term ) ) {
			update_term_meta( $term['term_id'], 'evanza_cover', $category['cover'] );
			update_term_meta( $term['term_id'], 'evanza_catalogue', $category['catalogue'] );
		}
	}

	foreach ( $data['products'] as $index => $product ) {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'evanza_product',
				'post_status'  => 'publish',
				'post_title'   => $product['name'],
				'post_name'    => $product['slug'],
				'post_content' => $product['description'],
				'menu_order'   => $index,
				'post_date'    => $product['added'] . ' 10:00:00',
				'meta_input'   => array(
					'_evanza_shape'    => $product['shape'],
					'_evanza_badges'   => $product['badges'],
					'_evanza_featured' => $product['featured'] ? 1 : 0,
					'_evanza_specs'    => $product['specs'],
					'_evanza_variants' => $product['variants'],
				),
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			wp_set_object_terms( $post_id, $product['category'], 'evanza_category' );
		}
	}
}
