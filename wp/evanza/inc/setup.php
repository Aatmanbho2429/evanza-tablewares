<?php
/**
 * Theme supports, menus and the activation routine.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'evanza_setup' );
function evanza_setup() {
	load_theme_textdomain( 'evanza', EVANZA_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'evanza' ),
			'footer'  => __( 'Footer menu', 'evanza' ),
		)
	);
}

/**
 * The pages the site needs. slug => title. Home is the static front page.
 */
function evanza_required_pages() {
	return array(
		'home'    => 'Home',
		'about'   => 'About',
		'gallery' => 'Gallery',
		'contact' => 'Contact',
	);
}

/**
 * Runs when the theme is activated (Playground blueprint or Hostinger).
 * The work needs post types and taxonomies, so it waits for `init` when
 * after_switch_theme fires earlier (older WordPress) and runs at once when it
 * fires later (current WordPress). Every step is idempotent.
 */
add_action( 'after_switch_theme', 'evanza_on_activation' );
function evanza_on_activation() {
	if ( did_action( 'init' ) ) {
		evanza_run_activation();
	} else {
		add_action( 'init', 'evanza_run_activation', 99 );
	}
}

function evanza_run_activation() {
	evanza_create_pages();
	evanza_create_menus();
	do_action( 'evanza_activated' );
	flush_rewrite_rules();
}

function evanza_create_pages() {
	$ids = array();
	foreach ( evanza_required_pages() as $slug => $title ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $title,
				'post_name'   => $slug,
			)
		);
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
}

function evanza_create_menus() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	$primary_items = array(
		'Home'       => home_url( '/' ),
		'About'      => home_url( '/about/' ),
		'Gallery'    => home_url( '/gallery/' ),
		'Collection' => home_url( '/collection/' ),
		'Contact'    => home_url( '/contact/' ),
	);
	$footer_items  = array(
		'Collection' => home_url( '/collection/' ),
		'About'      => home_url( '/about/' ),
		'Gallery'    => home_url( '/gallery/' ),
		'Contact'    => home_url( '/contact/' ),
	);

	$locations['primary'] = evanza_ensure_menu( 'Primary', $primary_items );
	$locations['footer']  = evanza_ensure_menu( 'Footer', $footer_items );
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Creates a custom-link menu unless a menu with that name already exists.
 * Returns the menu id.
 */
function evanza_ensure_menu( $name, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		return $menu->term_id;
	}
	$menu_id = wp_create_nav_menu( $name );
	foreach ( $items as $label => $url ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => $label,
				'menu-item-url'    => $url,
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			)
		);
	}
	return $menu_id;
}
