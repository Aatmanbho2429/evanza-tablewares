<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'title' => 'Gallery.',
			'lede'  => 'Our mugs, our factory and our certificates.',
			'room'  => 'powder',
		)
	);
	?>
</main>
<?php get_footer(); ?>
