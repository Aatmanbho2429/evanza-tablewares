<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'title' => "That page isn't here.",
			'lede'  => 'It may have moved. The collection is a good place to start again.',
			'room'  => 'blush',
		)
	);
	?>
	<div class="container" style="padding-block: var(--s-7)">
		<a class="btn" href="<?php echo esc_url( home_url( '/collection/' ) ); ?>">See the collection</a>
	</div>
</main>
<?php get_footer(); ?>
