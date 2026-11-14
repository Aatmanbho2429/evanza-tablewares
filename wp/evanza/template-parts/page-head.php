<?php
/**
 * Heading for inner pages: a colour room with a big title.
 *
 * @param array $args title, lede, room (a --glaze token name, default chalk), tone.
 */
$args = wp_parse_args(
	$args,
	array(
		'title' => '',
		'lede'  => '',
		'room'  => 'chalk',
		'tone'  => 'light',
	)
);
?>
<section class="page-head room" data-room data-tone="<?php echo esc_attr( $args['tone'] ); ?>" style="--room: var(--<?php echo esc_attr( $args['room'] ); ?>)">
	<div class="container">
		<h1><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $args['lede'] ) : ?>
			<p class="lede"><?php echo esc_html( $args['lede'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
