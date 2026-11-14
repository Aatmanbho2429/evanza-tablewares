<?php
/**
 * Site footer: a Graphite room.
 */
$biz = evanza_business();
?>
<footer class="site-footer" data-room data-tone="dark">
	<div class="site-footer__stripe" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
	<div class="container">
		<a class="site-footer__talk" href="https://wa.me/<?php echo esc_attr( $biz['whatsapp'] ); ?>"><span class="u-draw">Talk to the factory.</span><svg class="site-footer__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" aria-hidden="true" focusable="false"><path d="M3 12h17M13 5l7 7-7 7"/></svg></a>

		<div class="site-footer__grid">
			<nav aria-label="<?php esc_attr_e( 'Footer', 'evanza' ); ?>">
				<p class="site-footer__label">Pages</p>
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
					<li><a href="<?php echo esc_url( evanza_catalogue_url() ); ?>" target="_blank" rel="noopener"><?php echo evanza_nav_label( 'Download catalogue (PDF)' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
				</ul>
			</nav>

			<div>
				<p class="site-footer__label">Directors</p>
				<ul>
					<?php foreach ( $biz['people'] as $person ) : ?>
						<li><a href="tel:+91<?php echo esc_attr( $person['phone'] ); ?>"><?php echo evanza_nav_label( $person['name'] . ', ' . evanza_format_phone( $person['phone'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<p class="site-footer__label">Write to us</p>
				<address>
					<a href="mailto:<?php echo esc_attr( $biz['email'] ); ?>"><?php echo evanza_nav_label( $biz['email'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				</address>
			</div>

			<div>
				<p class="site-footer__label">The factory</p>
				<address><?php echo esc_html( $biz['address'] ); ?></address>
			</div>
		</div>

		<div class="site-footer__bottom">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $biz['name'] ); ?>, home"><?php echo evanza_img( 'brand/logo-light', $biz['name'], array( 'sizes' => '120px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $biz['name'] ); ?>. Ceramic mugs made in Morbi, Gujarat, since <?php echo (int) $biz['since']; ?>.</span>
			<button class="site-footer__top" type="button" data-back-top>
				Back to top
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" aria-hidden="true" focusable="false"><path d="M12 21V4M5 11l7-7 7 7"/></svg>
			</button>
		</div>
		<div class="site-footer__word" aria-hidden="true"><span>Evanza</span></div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
