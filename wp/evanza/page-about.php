<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'title' => 'Made in Morbi.',
			'lede'  => 'A ceramic mug factory in Morbi, Gujarat, producing since 2022.',
			'room'  => 'chalk',
		)
	);
	?>
</main>
<?php get_footer(); ?>
