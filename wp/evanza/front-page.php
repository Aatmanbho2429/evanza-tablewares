<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'eyebrow' => 'Morbi, Gujarat',
			'title'   => 'Crafted for <em>coffee</em> lovers.',
			'lede'    => 'Ceramic mug manufacturers, making coffee mugs, latte mugs and custom-printed ceramics since 2022.',
		)
	);
	?>
</main>
<?php get_footer(); ?>
