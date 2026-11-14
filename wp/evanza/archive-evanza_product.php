<?php
/**
 * Collection: the enquiry catalogue. The filter UI is built by assets/js/collection.js from the
 * inline JSON; the <noscript> list below is the server-rendered fallback.
 */
get_header();
$catalogue = evanza_get_products();
$count     = count( $catalogue['products'] );
?>
<main id="main" class="site-main" data-collection>
	<section class="coll-head room" data-room data-coll-head data-tone="light" style="--room: var(--chalk)">
		<div class="container">
			<h1>Ceramic mug collection.</h1>
			<div class="coll-head__row">
				<p class="lede">Everyday, cafe, gift and custom-printed mugs, in <?php echo esc_html( count( $catalogue['colorFamilies'] ) ); ?> colour families. Pick a glaze to see only the mugs that come in it.</p>
				<a class="btn" href="<?php echo esc_url( $catalogue['catalogue'] ); ?>" target="_blank" rel="noopener">Download full catalogue (PDF)</a>
			</div>
			<div class="glaze-bar" data-glaze-bar role="group" aria-label="Filter by colour"></div>
		</div>
	</section>

	<div class="filter-bar" data-filter-bar></div>
	<div class="cat-strip room" data-cat-strip data-tone="dark" hidden></div>

	<section class="coll-grid container">
		<ul class="tile-grid" data-grid></ul>
		<div class="coll-empty" data-empty hidden>
			<p class="t-2">No mugs match those filters.</p>
			<p class="lede">Try fewer colours or another category.</p>
			<button class="btn" type="button" data-clear>Clear filters</button>
		</div>
		<noscript>
			<ul class="plain-list">
				<?php foreach ( $catalogue['products'] as $product ) : ?>
					<li><a class="link" href="<?php echo esc_url( $product['url'] ); ?>"><?php echo esc_html( $product['name'] ); ?></a>, <?php echo esc_html( $product['specs']['capacity'] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ul>
		</noscript>
	</section>
	<?php evanza_print_products_json(); ?>
</main>
<?php get_footer(); ?>
