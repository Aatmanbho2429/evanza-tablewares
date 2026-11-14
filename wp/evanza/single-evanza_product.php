<?php
/**
 * Product page. The room takes its colour from the selected variant; assets/js/product.js adds the
 * live 3D mug, the glaze pour on colour change and the enquiry list.
 */
get_header();

while ( have_posts() ) :
	the_post();
	$product = evanza_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}
	$catalogue = evanza_get_products();
	$category  = null;
	foreach ( $catalogue['categories'] as $c ) {
		if ( $c['slug'] === $product['category'] ) {
			$category = $c;
		}
	}
	$wanted  = isset( $_GET['colour'] ) ? sanitize_key( wp_unslash( $_GET['colour'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$variant = $product['variants'][0];
	foreach ( $product['variants'] as $v ) {
		if ( $v['slug'] === $wanted ) {
			$variant = $v;
		}
	}
	$room = evanza_room_for( $variant['hex'] );
	$specs = $product['specs'];
	$rows  = array(
		'Capacity'       => $specs['capacity'] ?? '',
		'Material'       => $specs['material'] ?? '',
		'Finish'         => $specs['finish'] ?? '',
		'Microwave safe' => ! empty( $specs['microwaveSafe'] ) ? 'Yes' : 'No',
		'Dishwasher safe' => ! empty( $specs['dishwasherSafe'] ) ? 'Yes' : 'No',
		'Minimum order'  => $specs['moq'] ?? '',
		'Set'            => $specs['set'] ?? '',
	);
	$moq_number = (int) preg_replace( '/\D/', '', $specs['moq'] ?? '' );

	// Related: the same category first, then other featured mugs.
	$related = array();
	foreach ( $catalogue['products'] as $p ) {
		if ( $p['id'] !== $product['id'] && $p['category'] === $product['category'] ) {
			$related[] = $p;
		}
	}
	foreach ( $catalogue['products'] as $p ) {
		if ( count( $related ) >= 8 ) {
			break;
		}
		if ( $p['id'] !== $product['id'] && $p['category'] !== $product['category'] && $p['featured'] ) {
			$related[] = $p;
		}
	}
	?>
<main id="main" class="site-main" data-product data-slug="<?php echo esc_attr( $product['slug'] ); ?>">
	<section class="product-room room" data-room data-product-room data-tone="<?php echo esc_attr( $room['tone'] ); ?>" style="--room: <?php echo esc_attr( $room['color'] ); ?>">
		<div class="container product">
			<div class="product__stage gallery" data-gallery>
				<div class="gallery__track" data-track>
					<div class="gallery__slide" data-slide="3d">
						<img class="gallery__still" data-still src="<?php echo esc_url( $variant['render'] ); ?>" alt="<?php echo esc_attr( $product['name'] . ', ' . $variant['name'] ); ?>" width="800" height="800" fetchpriority="high">
						<canvas class="gallery__canvas" data-canvas role="img" aria-label="<?php echo esc_attr( '3D view of the ' . $product['name'] . '. Drag to turn it.' ); ?>"></canvas>
					</div>
				</div>
				<div class="gallery__thumbs" data-thumbs role="group" aria-label="Views" hidden></div>
			</div>

			<div class="product__info">
				<nav class="crumbs" aria-label="Breadcrumb">
					<ol>
						<li><a class="link" href="<?php echo esc_url( home_url( '/collection/' ) ); ?>">Collection</a></li>
						<?php if ( $category ) : ?>
							<li><a class="link" href="<?php echo esc_url( add_query_arg( 'category', $category['slug'], home_url( '/collection/' ) ) ); ?>"><?php echo esc_html( $category['name'] ); ?></a></li>
						<?php endif; ?>
						<li aria-current="page"><?php echo esc_html( $product['name'] ); ?></li>
					</ol>
				</nav>
				<h1 class="product__name"><?php echo esc_html( $product['name'] ); ?></h1>
				<?php if ( $product['badges'] ) : ?>
					<p class="product__badges small"><?php echo esc_html( ucfirst( implode( ', ', $product['badges'] ) ) ); ?></p>
				<?php endif; ?>
				<p class="product__desc"><?php echo esc_html( $product['description'] ); ?></p>

				<div class="swatches">
					<p class="swatches__label" id="colour-label">Colour: <strong data-variant-name><?php echo esc_html( $variant['name'] ); ?></strong></p>
					<div class="swatches__row" role="radiogroup" aria-labelledby="colour-label" data-swatches>
						<?php foreach ( $product['variants'] as $v ) : ?>
							<a class="glaze<?php echo 'multi' === $v['family'] ? ' glaze--multi' : ''; ?>" style="--glaze: <?php echo esc_attr( $v['hex'] ); ?>" role="radio" aria-checked="<?php echo $v['slug'] === $variant['slug'] ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $v['name'] ); ?>" data-variant="<?php echo esc_attr( $v['slug'] ); ?>" href="<?php echo esc_url( add_query_arg( 'colour', $v['slug'], $product['url'] ) ); ?>"></a>
						<?php endforeach; ?>
					</div>
				</div>

				<dl class="specs">
					<?php foreach ( $rows as $label => $value ) : ?>
						<?php if ( '' !== $value ) : ?>
							<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
						<?php endif; ?>
					<?php endforeach; ?>
				</dl>

				<div class="product__buy">
					<div class="field qty-field">
						<label for="qty">Quantity (pieces)</label>
						<div class="qty" data-qty>
							<button class="qty__btn" type="button" data-step="-50" aria-label="Fewer pieces">&minus;</button>
							<input class="qty__input" id="qty" type="number" inputmode="numeric" min="1" step="1" value="<?php echo (int) ( $moq_number ? $moq_number : 100 ); ?>" data-qty-input>
							<button class="qty__btn" type="button" data-step="50" aria-label="More pieces">+</button>
						</div>
					</div>
					<div class="product__cta">
						<button class="btn" type="button" data-add-enquiry>Add to enquiry list</button>
						<a class="btn btn--outline" data-whatsapp href="https://wa.me/<?php echo esc_attr( evanza_whatsapp_number() ); ?>?text=<?php echo rawurlencode( 'Hello Evanza Tablewares, I would like to enquire about the ' . $product['name'] . ', ' . $variant['name'] . '. ' . add_query_arg( 'colour', $variant['slug'], $product['url'] ) ); ?>" target="_blank" rel="noopener">Enquire on WhatsApp</a>
					</div>
					<?php if ( $category ) : ?>
						<a class="link small" href="<?php echo esc_url( $category['catalogue'] ); ?>" target="_blank" rel="noopener">Download <?php echo esc_html( $category['name'] ); ?> catalogue (PDF)</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $related ) : ?>
		<section class="related" aria-labelledby="related-title">
			<div class="container"><h2 class="t-2" id="related-title">More mugs</h2></div>
			<ul class="rail" data-rail>
				<?php foreach ( $related as $p ) : ?>
					<?php get_template_part( 'template-parts/product-tile', null, array( 'product' => $p ) ); ?>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
	<?php evanza_print_products_json(); ?>
</main>
<?php endwhile; ?>
<?php get_footer(); ?>
