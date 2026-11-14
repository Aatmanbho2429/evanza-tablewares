<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'eyebrow' => 'Gallery',
			'title'   => 'Inside the <em>factory</em>.',
			'lede'    => 'Products, process and certificates.',
		)
	);
	?>
</main>
<?php get_footer(); ?>
