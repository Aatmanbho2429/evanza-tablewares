<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'title' => 'Let\'s talk.',
			'lede'  => 'Tell us the shapes, colours and quantities you are after and the team will reply.',
			'room'  => 'blush',
		)
	);
	?>
</main>
<?php get_footer(); ?>
