<?php
/**
 * Navigation helpers: current-page state for menu links and a fallback menu.
 */

defined( 'ABSPATH' ) || exit;

/** Marks the link for the current page with aria-current. The Collection link also matches product pages. */
add_filter( 'nav_menu_link_attributes', 'evanza_nav_link_attributes', 10, 3 );
function evanza_nav_link_attributes( $atts, $item ) {
	$path = trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' );
	$home = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$slug = trim( preg_replace( '#^' . preg_quote( $home, '#' ) . '#', '', $path ), '/' );

	if ( '' === $slug ) {
		$current = is_front_page();
	} elseif ( 'collection' === $slug ) {
		$current = is_post_type_archive( 'evanza_product' ) || is_singular( 'evanza_product' );
	} else {
		$current = is_page( $slug );
	}
	if ( $current ) {
		$atts['aria-current'] = 'page';
	}
	return $atts;
}

/** Used when a menu location has no menu assigned. */
function evanza_fallback_menu( $args = array() ) {
	$items = array(
		'Collection' => home_url( '/collection/' ),
		'About'      => home_url( '/about/' ),
		'Gallery'    => home_url( '/gallery/' ),
		'Contact'    => home_url( '/contact/' ),
	);
	$class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'nav-list';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $label => $url ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}
