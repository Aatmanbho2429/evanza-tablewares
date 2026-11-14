<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'title' => is_singular() ? get_the_title() : get_the_archive_title(),
		)
	);
	?>
	<div class="container">
		<?php while ( have_posts() ) : the_post(); ?>
			<div class="stack"><?php the_content(); ?></div>
		<?php endwhile; ?>
	</div>
</main>
<?php get_footer(); ?>
