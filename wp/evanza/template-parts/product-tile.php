<?php
/**
 * A product tile for rails (related products). The Collection builds its own tiles with JS.
 *
 * @param array $args product.
 */
$product = $args['product'];
$variant = $product['variants'][0];
?>
<li class="tile">
	<a class="tile__link" href="<?php echo esc_url( $product['url'] ); ?>">
		<span class="tile__art" style="--c: <?php echo esc_attr( $variant['hex'] ); ?>">
			<img class="tile__render" src="<?php echo esc_url( $variant['render'] ); ?>" alt="" width="800" height="800" loading="lazy" decoding="async" draggable="false">
		</span>
		<span class="tile__name t-3"><?php echo esc_html( $product['name'] ); ?></span>
		<span class="tile__meta"><?php echo esc_html( $product['specs']['capacity'] ?? '' ); ?></span>
	</a>
</li>
