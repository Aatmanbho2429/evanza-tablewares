<?php
/**
 * Site header and mobile menu. The header takes the colour and tone of the room beneath it (see lib/header.js).
 */
$biz = evanza_business();
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'evanza' ); ?></a>

<header class="site-header" data-header data-tone="light">
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $biz['name'] ); ?>, home">
			<?php echo evanza_img( 'brand/logo-dark', $biz['name'], array( 'class' => 'brand__dark', 'sizes' => '80px', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo evanza_img( 'brand/logo-light', '', array( 'class' => 'brand__light', 'sizes' => '80px', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
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
			<a class="text-btn enquiry-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" data-enquiry-open aria-label="<?php esc_attr_e( 'Enquiry list, 0 items', 'evanza' ); ?>">Enquiry (<span data-enquiry-count>0</span>)</a>
			<button class="text-btn menu-btn" type="button" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle>Menu</button>
		</div>
	</div>
</header>

<div class="mobile-menu" id="mobile-menu" data-mobile-menu data-tone="dark">
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
		<a class="link" href="https://wa.me/<?php echo esc_attr( $biz['whatsapp'] ); ?>">WhatsApp</a>
		<a class="link" href="mailto:<?php echo esc_attr( $biz['email'] ); ?>"><?php echo esc_html( $biz['email'] ); ?></a>
	</div>
</div>
