<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'eyebrow' => 'Contact',
			'title'   => 'Tell us what you <em>need</em>.',
			'lede'    => 'Tell us the shapes, colours and quantities you are after and the team will reply.',
		)
	);
	?>
</main>
<?php get_footer(); ?>
