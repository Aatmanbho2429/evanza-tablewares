<?php
/**
 * Site footer.
 */
$biz = evanza_business();
?>
<footer class="site-footer">
	<div class="container">
		<div class="site-footer__top">
			<p class="site-footer__statement t-3xl">Crafted for <em>coffee</em> lovers.</p>
			<div class="site-footer__cta">
				<a class="btn btn--light" href="<?php echo esc_url( home_url( '/collection/' ) ); ?>">Explore collection <?php echo evanza_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="btn btn--ghost-light" href="<?php echo esc_url( evanza_catalogue_url() ); ?>" target="_blank" rel="noopener">Download catalogue <?php echo evanza_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
		</div>

		<div class="site-footer__grid">
			<div class="site-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $biz['name'] ); ?>, home"><?php echo evanza_img( 'brand/logo-light', $biz['name'], array( 'sizes' => '120px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<p>Ceramic mug manufacturers in Morbi, Gujarat. Making coffee mugs, latte mugs and custom-printed ceramics since <?php echo (int) $biz['since']; ?>.</p>
			</div>

			<nav aria-label="<?php esc_attr_e( 'Footer', 'evanza' ); ?>">
				<h2>Explore</h2>
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
			</nav>

			<div>
				<h2>Talk to us</h2>
				<ul>
					<?php foreach ( $biz['people'] as $person ) : ?>
						<li class="site-footer__person">
							<a class="link" href="tel:+91<?php echo esc_attr( $person['phone'] ); ?>"><?php echo esc_html( $person['name'] ); ?> &middot; <?php echo esc_html( evanza_format_phone( $person['phone'] ) ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<h2>Visit &amp; write</h2>
				<address>
					<a class="link" href="mailto:<?php echo esc_attr( $biz['email'] ); ?>"><?php echo esc_html( $biz['email'] ); ?></a>
					<span><?php echo esc_html( $biz['address'] ); ?></span>
				</address>
			</div>
		</div>

		<div class="site-footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $biz['name'] ); ?>. All rights reserved.</span>
			<span>Made in Morbi, Gujarat</span>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
