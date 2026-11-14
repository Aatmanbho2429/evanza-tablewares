<?php
/**
 * Site header and mobile menu.
 */
$biz = evanza_business();
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'evanza' ); ?></a>

<header class="site-header" data-header>
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $biz['name'] ); ?>, home">
			<?php echo evanza_img( 'brand/logo-dark', $biz['name'], array( 'sizes' => '80px', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'evanza' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-list',
					'fallback_cb'    => 'evanza_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<div class="site-header__actions">
			<a class="icon-btn enquiry-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" data-enquiry-open aria-label="<?php esc_attr_e( 'Enquiry list', 'evanza' ); ?>">
				<?php echo evanza_icon( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="badge" data-enquiry-count aria-hidden="true" hidden>0</span>
			</a>
			<button class="icon-btn menu-btn" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="<?php esc_attr_e( 'Open menu', 'evanza' ); ?>" data-menu-toggle>
				<span class="menu-btn__bars" aria-hidden="true"></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-menu" id="mobile-menu" data-mobile-menu>
	<nav aria-label="<?php esc_attr_e( 'Mobile', 'evanza' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-list',
				'fallback_cb'    => 'evanza_fallback_menu',
				'depth'          => 1,
			)
		);
		?>
	</nav>
	<div class="mobile-menu__foot">
		<a class="link" href="https://wa.me/<?php echo esc_attr( $biz['whatsapp'] ); ?>"><?php echo evanza_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> WhatsApp</a>
		<a class="link" href="mailto:<?php echo esc_attr( $biz['email'] ); ?>"><?php echo evanza_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $biz['email'] ); ?></a>
	</div>
</div>
