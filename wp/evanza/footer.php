<?php
/**
 * Site footer: a Graphite room.
 */
$biz = evanza_business();
?>
<footer class="site-footer" data-room data-tone="dark">
	<div class="container">
		<a class="site-footer__talk" href="https://wa.me/<?php echo esc_attr( $biz['whatsapp'] ); ?>">Talk to the factory.</a>

		<div class="site-footer__grid">
			<nav aria-label="<?php esc_attr_e( 'Footer', 'evanza' ); ?>">
				<h2>Pages</h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '<ul>%3$s</ul>',
						'fallback_cb'    => 'evanza_fallback_menu',
						'depth'          => 1,
					)
				);
				?>
				<ul>
					<li><a class="link" href="<?php echo esc_url( evanza_catalogue_url() ); ?>" target="_blank" rel="noopener">Download catalogue (PDF)</a></li>
				</ul>
			</nav>

			<div>
				<h2>Directors</h2>
				<ul>
					<?php foreach ( $biz['people'] as $person ) : ?>
						<li><a class="link" href="tel:+91<?php echo esc_attr( $person['phone'] ); ?>"><?php echo esc_html( $person['name'] ); ?>, <?php echo esc_html( evanza_format_phone( $person['phone'] ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<h2>Write to us</h2>
				<address>
					<a class="link" href="mailto:<?php echo esc_attr( $biz['email'] ); ?>"><?php echo esc_html( $biz['email'] ); ?></a>
				</address>
			</div>

			<div>
				<h2>The factory</h2>
				<address><?php echo esc_html( $biz['address'] ); ?></address>
			</div>
		</div>

		<div class="site-footer__bottom">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $biz['name'] ); ?>, home"><?php echo evanza_img( 'brand/logo-light', $biz['name'], array( 'sizes' => '120px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $biz['name'] ); ?>. Ceramic mugs made in Morbi, Gujarat, since <?php echo (int) $biz['since']; ?>.</span>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
