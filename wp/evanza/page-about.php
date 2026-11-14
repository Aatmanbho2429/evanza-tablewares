<?php
/**
 * About: the factory, the making process, certificates and the directors.
 */
get_header();
$biz   = evanza_business();
$steps = array(
	array( 'Clay', 'factory/raw-material', 'Clay is loaded into our plant, ground in a ball mill and extruded into billets, so every batch starts the same.' ),
	array( 'Forming', 'factory/pressing', 'Mugs are formed on machine, then trimmed and finished by hand before they dry on racks.' ),
	array( 'Glazing', 'factory/glazing', 'Each mug is dipped and sprayed in its glaze colour, inside and out.' ),
	array( 'Firing', 'factory/kiln-mugs', 'Glazed mugs travel through a roller kiln that fires them at high temperature.' ),
	array( 'Packing and dispatch', 'factory/packing', 'Every mug is checked, packed in cartons and loaded for dispatch to your door or your port.' ),
);
$sets = evanza_gallery_sets();
?>
<main id="main" class="site-main about">
	<section class="about-hero" data-room data-tone="light" style="--room: var(--chalk)">
		<div class="about-hero__photo">
			<?php echo evanza_img( 'factory/factory-exterior', 'The Evanza factory building in Morbi, Gujarat', array( 'sizes' => '100vw', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="container about-hero__text">
			<h1>Made in Morbi, since 2022.</h1>
			<p class="lede">A ceramic mug factory on the Maliya Highway in Gujarat. We make mugs for homes, cafes, hotels, retailers and brands, in stock glazes or your own colours and prints.</p>
		</div>
	</section>

	<section class="process room" data-room data-tone="dark" data-process style="--room: var(--graphite)" aria-labelledby="process-title">
		<div class="container">
			<h2 id="process-title" class="t-2">How a mug is made.</h2>
			<div class="process__layout">
				<div class="process__stage" aria-hidden="true">
					<?php foreach ( $steps as $i => $step ) : ?>
						<div class="process__frame<?php echo 0 === $i ? ' is-active' : ''; ?>" data-frame="<?php echo (int) $i; ?>">
							<?php echo evanza_img( $step[1], '', array( 'sizes' => '(min-width: 56rem) 46vw, 100vw', 'loading' => 0 === $i ? 'eager' : 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php endforeach; ?>
				</div>
				<ol class="process__steps">
					<?php foreach ( $steps as $i => $step ) : ?>
						<li class="process__step<?php echo 0 === $i ? ' is-active' : ''; ?>" data-step="<?php echo (int) $i; ?>">
							<div class="process__photo"><?php echo evanza_img( $step[1], $step[0], array( 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<span class="process__n"><?php echo (int) ( $i + 1 ); ?></span>
							<h3 class="t-2"><?php echo esc_html( $step[0] ); ?></h3>
							<p><?php echo esc_html( $step[2] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</section>

	<section class="certs-room room" data-room data-tone="light" style="--room: var(--butter)" aria-labelledby="certs-title">
		<div class="container">
			<h2 id="certs-title" class="t-2">Certified.</h2>
			<p class="lede">ISO 9001:2015 for quality management, with CE and QSA certificates. Tap one to read it.</p>
			<ul class="certs">
				<?php foreach ( $sets['certificates']['items'] as $item ) : ?>
					<li><?php echo evanza_lightbox_item( 'certificates', $item[0], $item[1], array( 'class' => 'cert', 'sizes' => '(min-width: 48rem) 22rem, 80vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="directors room" data-room data-tone="light" style="--room: var(--pistachio)" aria-labelledby="directors-title">
		<div class="container">
			<h2 id="directors-title" class="t-2">The directors.</h2>
			<ul class="people">
				<?php foreach ( $biz['people'] as $person ) : ?>
					<li class="person">
						<h3 class="t-3"><?php echo esc_html( $person['name'] ); ?></h3>
						<p class="small"><?php echo esc_html( $person['role'] ); ?>, <?php echo esc_html( evanza_format_phone( $person['phone'] ) ); ?></p>
						<p class="person__links">
							<a class="link" href="tel:+91<?php echo esc_attr( $person['phone'] ); ?>">Call <?php echo esc_html( $person['name'] ); ?></a>
							<a class="link" href="https://wa.me/91<?php echo esc_attr( $person['phone'] ); ?>" target="_blank" rel="noopener">WhatsApp <?php echo esc_html( $person['name'] ); ?></a>
						</p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
</main>
<?php get_footer(); ?>
