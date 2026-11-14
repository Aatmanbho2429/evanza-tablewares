<?php
/**
 * Heading block for inner pages.
 *
 * @param array $args eyebrow, title, lede.
 */
$args = wp_parse_args(
	$args,
	array(
		'eyebrow' => '',
		'title'   => '',
		'lede'    => '',
	)
);
?>
<section class="page-head">
	<div class="container">
		<?php if ( $args['eyebrow'] ) : ?>
			<span class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span>
		<?php endif; ?>
		<h1><?php echo wp_kses( $args['title'], array( 'em' => array() ) ); ?></h1>
		<?php if ( $args['lede'] ) : ?>
			<p class="lede"><?php echo esc_html( $args['lede'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
