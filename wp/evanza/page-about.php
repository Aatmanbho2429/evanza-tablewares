<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'eyebrow' => 'About',
			'title'   => 'Made in <em>Morbi</em>.',
			'lede'    => 'A ceramic mug factory in Morbi, Gujarat, producing since 2022.',
		)
	);
	?>
</main>
<?php get_footer(); ?>
