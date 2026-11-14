<?php
get_header();
?>
<main id="main" class="site-main">
	<?php
	get_template_part(
		'template-parts/page-head',
		null,
		array(
			'eyebrow' => '404',
			'title'   => 'This page <em>slipped</em> off the shelf.',
			'lede'    => 'The page you are looking for does not exist or has moved.',
		)
	);
	?>
	<div class="container">
		<a class="btn" href="<?php echo esc_url( home_url( '/collection/' ) ); ?>">Browse the collection <?php echo evanza_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
</main>
<?php get_footer(); ?>
