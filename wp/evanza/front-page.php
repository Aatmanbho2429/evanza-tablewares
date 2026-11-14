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
			<img class="hero__still" data-hero-still src="<?php echo esc_url( evanza_asset_url( 'img/mock/bella-belly-mug-green.svg' ) ); ?>" alt="A green ceramic mug" width="800" height="800">
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
</main>
<?php get_footer(); ?>
