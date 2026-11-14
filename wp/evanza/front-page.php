<?php
/**
 * Home. The hero is a colour room: the 3D mug stands between the two lines of the headline,
 * and the glaze dots recolour the room (the glaze pour) and the mug.
 */
get_header();

$glazes = array(
	array( 'name' => 'Pistachio', 'room' => '#C9D8A6', 'body' => '#78A04A', 'tone' => 'light' ),
	array( 'name' => 'Butter', 'room' => '#F1DC8B', 'body' => '#E9B52E', 'tone' => 'light' ),
	array( 'name' => 'Powder', 'room' => '#BCD3E6', 'body' => '#5B96CE', 'tone' => 'light' ),
	array( 'name' => 'Blush', 'room' => '#EEC3C9', 'body' => '#DC7A8A', 'tone' => 'light' ),
	array( 'name' => 'Bottle', 'room' => '#1F4A42', 'body' => '#3C8C74', 'tone' => 'dark' ),
	array( 'name' => 'Cobalt', 'room' => '#1E2340', 'body' => '#4766C9', 'tone' => 'dark' ),
);
$first = $glazes[0];
?>
<main id="main" class="site-main">
	<section class="hero room" data-room data-hero data-tone="<?php echo esc_attr( $first['tone'] ); ?>" style="--room: <?php echo esc_attr( $first['room'] ); ?>">
		<div class="hero__stage">
			<h1 class="hero__title t-hero" data-hero-title aria-label="Ceramic mugs for every table.">
				<span class="hero__line" data-split>Ceramic mugs</span>
				<span class="hero__line" data-split>for every table.</span>
			</h1>
			<canvas class="hero__canvas" data-hero-canvas role="img" aria-label="A glossy ceramic mug. Drag to turn it."></canvas>
			<img class="hero__still" data-hero-still src="<?php echo esc_url( evanza_asset_url( 'img/mock/bella-belly-mug-green.svg' ) ); ?>" alt="A green ceramic mug" width="800" height="800" loading="lazy">
			<p class="hero__front t-hero" data-hero-front aria-hidden="true">
				<span class="hero__line" data-split>Ceramic mugs</span>
				<span class="hero__line" data-split>for every table.</span>
			</p>
		</div>

		<div class="hero__bar">
			<div class="hero__sub hero__support" style="--i: 0">
				<p>Made in Morbi, Gujarat, since 2022. Everyday mugs, gift sets, cafe and hotel ranges, and custom printing, in bulk.</p>
				<div class="hero__cta">
					<a class="btn" href="<?php echo esc_url( home_url( '/collection/' ) ); ?>">See the collection</a>
					<a class="btn btn--outline" href="<?php echo esc_url( evanza_catalogue_url() ); ?>" target="_blank" rel="noopener">Download catalogue</a>
				</div>
			</div>
			<div class="hero__glazes hero__support" style="--i: 1">
				<div class="hero__dots" role="group" aria-label="Glaze colour">
					<?php foreach ( $glazes as $i => $g ) : ?>
						<button class="glaze" type="button" style="--glaze: <?php echo esc_attr( $g['body'] ); ?>"
							aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $g['name'] ); ?> glaze"
							data-glaze data-name="<?php echo esc_attr( $g['name'] ); ?>" data-room="<?php echo esc_attr( $g['room'] ); ?>" data-body="<?php echo esc_attr( $g['body'] ); ?>"></button>
					<?php endforeach; ?>
				</div>
				<span class="hero__glaze-name" data-glaze-name aria-live="polite"><?php echo esc_html( $first['name'] ); ?></span>
			</div>
		</div>
	</section>

	<?php
	$data = evanza_get_products();

	// Category rows: name, count and the render of the category's lead mug.
	$rows = array();
	foreach ( $data['categories'] as $cat ) {
		$lead  = null;
		$count = 0;
		foreach ( $data['products'] as $p ) {
			if ( $p['category'] !== $cat['slug'] ) {
				continue;
			}
			++$count;
			if ( ! $lead || ( $p['featured'] && ! $lead['featured'] ) ) {
				$lead = $p;
			}
		}
		if ( $lead ) {
			$rows[] = array(
				'cat'    => $cat,
				'count'  => $count,
				'render' => $lead['variants'][0]['render'],
				'hex'    => $lead['variants'][0]['hex'],
			);
		}
	}

	// New in: mugs badged "new", newest first.
	$new = array_filter(
		$data['products'],
		static function ( $p ) {
			return in_array( 'new', $p['badges'], true );
		}
	);
	usort(
		$new,
		static function ( $a, $b ) {
			return strcmp( $b['added'], $a['added'] );
		}
	);
	?>

	<section class="story room" data-room data-tone="dark" data-story style="--room: var(--graphite)" aria-labelledby="story-title">
		<div class="story__track">
			<div class="story__pin">
				<div class="container story__layout">
					<div class="story__copy">
						<h2 id="story-title">From clay to cup.</h2>
						<ul class="story__steps">
							<li data-step>Clay from Morbi, ground and pressed in our own plant.</li>
							<li data-step>Every mug is glazed, in any of 14 colour families.</li>
							<li data-step>Fired in a roller kiln, checked, packed and dispatched.</li>
						</ul>
					</div>
					<div class="story__stage">
						<canvas class="story__canvas" data-story-canvas role="img" aria-label="A mug going from raw clay to glazed ceramic as you scroll." data-body="<?php echo esc_attr( $first['body'] ); ?>"></canvas>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="cats room" data-room data-tone="light" style="--room: var(--chalk)" aria-labelledby="cats-title">
		<div class="container">
			<h2 id="cats-title" class="cats__title">Shop by category.</h2>
			<ul class="cats__list" data-cats>
				<?php foreach ( $rows as $row ) : ?>
					<li>
						<a class="cats__row" href="<?php echo esc_url( add_query_arg( 'category', $row['cat']['slug'], home_url( '/collection/' ) ) ); ?>" data-render="<?php echo esc_url( $row['render'] ); ?>" data-hex="<?php echo esc_attr( $row['hex'] ); ?>">
							<span class="cats__name"><?php echo esc_html( $row['cat']['name'] ); ?></span>
							<span class="cats__count"><?php echo esc_html( $row['count'] . ( 1 === $row['count'] ? ' mug' : ' mugs' ) ); ?></span>
							<span class="cats__thumb" style="--c: <?php echo esc_attr( $row['hex'] ); ?>"><img src="<?php echo esc_url( $row['render'] ); ?>" alt="" width="160" height="160" loading="lazy" decoding="async"></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php if ( $new ) : ?>
		<section class="newin room" data-room data-tone="light" style="--room: var(--powder)" aria-labelledby="new-title">
			<div class="container newin__head">
				<h2 id="new-title" class="t-2">New in.</h2>
				<a class="link" href="<?php echo esc_url( home_url( '/collection/?sort=new' ) ); ?>">See the whole collection</a>
			</div>
			<ul class="rail" data-rail>
				<?php foreach ( $new as $p ) : ?>
					<?php get_template_part( 'template-parts/product-tile', null, array( 'product' => $p ) ); ?>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<section class="morbi" aria-labelledby="morbi-title">
		<div class="morbi__photo">
			<?php echo evanza_img( 'factory/kiln-line', 'The roller kiln line in the Evanza factory', array( 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="container morbi__text">
			<h2 id="morbi-title" class="t-2">Made in Morbi.</h2>
			<div class="morbi__body">
				<p>We make our mugs in our own factory on the Maliya Highway in Morbi, Gujarat, one of India's ceramic centres. Clay preparation, forming, glazing, firing and packing happen under one roof, and we have been producing since 2022.</p>
				<p>We supply homes, cafes, hotels, retailers, corporate buyers and exporters, in stock glazes or with your own colours and prints.</p>
				<p class="morbi__certs">Certified to ISO 9001:2015, with CE and QSA certificates.</p>
				<a class="link" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">See how we make them</a>
			</div>
		</div>
	</section>

	<section class="catalogue room" data-room data-tone="light" style="--room: var(--butter)" aria-labelledby="catalogue-title">
		<div class="container catalogue__inner">
			<h2 id="catalogue-title" class="t-1">Get the catalogue.</h2>
			<p class="lede">Every mug, colour and capacity in one PDF. Ask us for bulk prices, custom printing or a sample.</p>
			<a class="btn btn--lg" href="<?php echo esc_url( evanza_catalogue_url() ); ?>" target="_blank" rel="noopener">Download catalogue (PDF)</a>
			<div class="catalogue__links">
				<a class="link" href="https://wa.me/<?php echo esc_attr( evanza_whatsapp_number() ); ?>">WhatsApp us</a>
				<a class="link" href="mailto:<?php echo esc_attr( evanza_enquiry_email() ); ?>"><?php echo esc_html( evanza_enquiry_email() ); ?></a>
				<a class="link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Send an enquiry</a>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
