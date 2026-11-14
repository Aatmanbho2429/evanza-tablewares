<?php
/**
 * Product post type and category taxonomy.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'evanza_register_product_types' );
function evanza_register_product_types() {
	register_post_type(
		'evanza_product',
		array(
			'labels'        => array(
				'name'               => __( 'Products', 'evanza' ),
				'singular_name'      => __( 'Product', 'evanza' ),
				'add_new_item'       => __( 'Add new product', 'evanza' ),
				'edit_item'          => __( 'Edit product', 'evanza' ),
				'new_item'           => __( 'New product', 'evanza' ),
				'view_item'          => __( 'View product', 'evanza' ),
				'search_items'       => __( 'Search products', 'evanza' ),
				'not_found'          => __( 'No products found', 'evanza' ),
				'not_found_in_trash' => __( 'No products found in the bin', 'evanza' ),
				'all_items'          => __( 'All products', 'evanza' ),
				'menu_name'          => __( 'Products', 'evanza' ),
			),
			'public'        => true,
			'has_archive'   => 'collection',
			'rewrite'       => array(
				'slug'       => 'collection',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-coffee',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => false,
		)
	);

	// Categories are filtered on /collection/?category=slug, so they get no URLs of their own.
	register_taxonomy(
		'evanza_category',
		'evanza_product',
		array(
			'labels'             => array(
				'name'          => __( 'Product categories', 'evanza' ),
				'singular_name' => __( 'Product category', 'evanza' ),
				'menu_name'     => __( 'Categories', 'evanza' ),
				'add_new_item'  => __( 'Add new category', 'evanza' ),
				'edit_item'     => __( 'Edit category', 'evanza' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'hierarchical'       => true,
			'rewrite'            => false,
			'show_in_rest'       => false,
		)
	);
}
