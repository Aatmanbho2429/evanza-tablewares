<?php
/**
 * Titles, meta descriptions, Open Graph, JSON-LD and the sitemap.
 * Voice: a ceramic mug manufacturer for every use. No prices (it is an enquiry catalogue).
 */

defined( 'ABSPATH' ) || exit;

/** What this page is about: title (without the site name), description, image and type. */
function evanza_page_meta() {
	$meta = array(
		'title'       => '',
		'description' => 'Evanza Tablewares is a ceramic mug manufacturer in Morbi, Gujarat. Everyday mugs, gift sets, cafe and hotel ranges and custom printing, in every glaze and in bulk.',
		'image'       => evanza_asset_url( 'img/brand/og-default.jpg' ),
		'type'        => 'website',
	);
	if ( is_front_page() ) {
		return $meta;
	}
	if ( is_post_type_archive( 'evanza_product' ) ) {
		$meta['title']       = 'Ceramic mug collection';
		$meta['description'] = 'Browse ceramic mugs by category and colour: classic, latte, two-tone, heart handle, marble, printed, espresso cups and gift sets. Made in Morbi, sold in bulk with custom printing.';
	} elseif ( is_singular( 'evanza_product' ) ) {
		$product = evanza_get_product( get_the_ID() );
		if ( $product ) {
			$meta['title'] = $product['name'];
			$lead          = wp_trim_words( $product['description'], 24, '' );
			$moq           = ! empty( $product['specs']['moq'] ) ? ' Minimum order ' . $product['specs']['moq'] . '.' : '';
			$meta['description'] = trim( $lead . ' Made in Morbi by Evanza Tablewares.' . $moq );
			$meta['type']        = 'product';
			foreach ( $product['variants'] as $v ) {
				foreach ( $v['images'] as $img ) {
					if ( 0 !== strpos( $img, 'data:' ) && false === strpos( $img, '/assets/img/mock/' ) ) {
						$meta['image'] = $img;
						break 2;
					}
				}
			}
		}
	} elseif ( is_page( 'about' ) ) {
		$meta['title']       = 'About the factory';
		$meta['description'] = 'How Evanza Tablewares makes ceramic mugs in Morbi, Gujarat: clay, forming, glazing, firing and packing, with ISO 9001:2015, CE and QSA certificates.';
	} elseif ( is_page( 'gallery' ) ) {
		$meta['title']       = 'Gallery';
		$meta['description'] = 'Photos of Evanza mugs, the Morbi factory and our ISO 9001:2015, CE and QSA certificates.';
	} elseif ( is_page( 'contact' ) ) {
		$meta['title']       = 'Contact and enquiries';
		$meta['description'] = 'Send an enquiry to Evanza Tablewares or call the directors. Ceramic mugs for homes, cafes, hotels, retailers and brands, made in Morbi, Gujarat.';
	} elseif ( is_404() ) {
		$meta['title'] = 'Page not found';
	}
	return $meta;
}

add_filter( 'document_title_parts', 'evanza_document_title' );
function evanza_document_title( $parts ) {
	if ( is_front_page() ) {
		$parts['tagline'] = 'Ceramic mug manufacturer in Morbi, Gujarat';
		return $parts;
	}
	$meta = evanza_page_meta();
	if ( $meta['title'] ) {
		$parts['title'] = $meta['title'];
	}
	return $parts;
}

add_action( 'wp_head', 'evanza_head_seo', 3 );
function evanza_head_seo() {
	$meta = evanza_page_meta();
	$url  = is_singular() ? get_permalink() : ( is_post_type_archive( 'evanza_product' ) ? get_post_type_archive_link( 'evanza_product' ) : home_url( '/' ) );
	if ( is_page() ) {
		$url = get_permalink();
	}
	$tags = array(
		'description'    => array( 'name', $meta['description'] ),
		'og:type'        => array( 'property', $meta['type'] ),
		'og:site_name'   => array( 'property', 'Evanza Tablewares' ),
		'og:title'       => array( 'property', wp_get_document_title() ),
		'og:description' => array( 'property', $meta['description'] ),
		'og:image'       => array( 'property', $meta['image'] ),
		'og:url'         => array( 'property', $url ),
		'twitter:card'   => array( 'name', 'summary_large_image' ),
	);
	foreach ( $tags as $key => $tag ) {
		printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $tag[0] ), esc_attr( $key ), esc_attr( $tag[1] ) );
	}
	if ( is_post_type_archive( 'evanza_product' ) ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}
	if ( is_404() ) {
		echo '<meta name="robots" content="noindex">' . "\n";
	}

	foreach ( evanza_json_ld() as $block ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/** Structured data: the organisation on the main pages, the product on product pages. */
function evanza_json_ld() {
	$biz    = evanza_business();
	$blocks = array();
	$org    = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'Organization',
		'@id'          => home_url( '/#organization' ),
		'name'         => $biz['name'],
		'url'          => home_url( '/' ),
		'logo'         => evanza_asset_url( 'img/brand/mark-512.png' ),
		'description'  => 'Ceramic mug manufacturer in Morbi, Gujarat, India.',
		'foundingDate' => (string) $biz['since'],
		'email'        => $biz['email'],
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Pipaliya Maliya Highway, At. Chachavadarda, Tal: Maliya',
			'addressLocality' => 'Morbi',
			'addressRegion'   => 'Gujarat',
			'addressCountry'  => 'IN',
		),
		'contactPoint' => array(),
	);
	foreach ( $biz['people'] as $person ) {
		$org['contactPoint'][] = array(
			'@type'       => 'ContactPoint',
			'contactType' => 'sales',
			'name'        => $person['name'],
			'telephone'   => '+91' . $person['phone'],
			'email'       => $biz['email'],
		);
	}
	if ( is_front_page() || is_page( array( 'about', 'contact' ) ) ) {
		$blocks[] = $org;
	}

	if ( is_singular( 'evanza_product' ) ) {
		$product = evanza_get_product( get_the_ID() );
		if ( $product ) {
			$images = array();
			foreach ( $product['variants'] as $v ) {
				foreach ( $v['images'] as $img ) {
					if ( 0 !== strpos( $img, 'data:' ) && false === strpos( $img, '/assets/img/mock/' ) ) {
						$images[] = $img;
					}
				}
			}
			$ld = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Product',
				'name'        => $product['name'],
				'description' => $product['description'],
				'url'         => $product['url'],
				'sku'         => $product['slug'],
				'brand'       => array( '@type' => 'Brand', 'name' => $biz['name'] ),
				'manufacturer' => array( '@id' => home_url( '/#organization' ) ),
				'color'       => implode( ', ', wp_list_pluck( $product['variants'], 'name' ) ),
			);
			if ( $images ) {
				$ld['image'] = array_slice( $images, 0, 6 );
			}
			if ( ! empty( $product['specs']['material'] ) ) {
				$ld['material'] = $product['specs']['material'];
			}
			$blocks[] = $ld;
		}
	}
	return $blocks;
}

// Sitemap: the pages and the products; not the blog, categories or authors.
add_filter( 'wp_sitemaps_post_types', static function ( $types ) {
	unset( $types['post'] );
	return $types;
} );
add_filter( 'wp_sitemaps_add_provider', static function ( $provider, $name ) {
	return in_array( $name, array( 'users', 'taxonomies' ), true ) ? false : $provider;
}, 10, 2 );
