<?php
/**
 * Gallery: three shelves (products, factory, certificates). Tapping a photo opens the lightbox.
 */
get_header();
$sets  = evanza_gallery_sets();
$rooms = array(
	'products'     => 'chalk',
	'factory'      => 'graphite',
	'certificates' => 'chalk',
);
?>
<main id="main" class="site-main gallery" data-gallery-page>
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'title' => 'Gallery.',
			'lede'  => 'Our mugs, our factory and our certificates. Drag a shelf sideways, tap a photo to see it larger.',
			'room'  => 'powder',
		)
	);
	?>
	<?php foreach ( $sets as $key => $set ) : ?>
		<?php $tone = 'graphite' === $rooms[ $key ] ? 'dark' : 'light'; ?>
		<section class="shelf-room room" data-room data-tone="<?php echo esc_attr( $tone ); ?>" style="--room: var(--<?php echo esc_attr( $rooms[ $key ] ); ?>)" aria-labelledby="shelf-<?php echo esc_attr( $key ); ?>">
			<div class="container"><h2 class="t-2" id="shelf-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $set['title'] ); ?></h2></div>
			<div class="shelf" data-rail>
				<?php foreach ( $set['items'] as $item ) : ?>
					<?php echo evanza_lightbox_item( $key, $item[0], $item[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>
</main>
<?php get_footer(); ?>
