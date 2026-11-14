<?php
get_header();
$catalogue = evanza_get_products();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'eyebrow' => 'Collection',
			'title'   => 'Mugs made in <em>Morbi</em>.',
			'lede'    => 'Browse the range by category and colour, then send us an enquiry.',
		)
	);
	?>
	<div class="container">
		<ul class="stack">
			<?php foreach ( $catalogue['products'] as $product ) : ?>
				<li><a class="link" href="<?php echo esc_url( $product['url'] ); ?>"><?php echo esc_html( $product['name'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php evanza_print_products_json(); ?>
</main>
<?php get_footer(); ?>
