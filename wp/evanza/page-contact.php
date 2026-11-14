<?php
/**
 * Contact: a Blush room with the enquiry form, the four directors, the address and a map.
 * The form posts to admin-post.php (see inc/contact.php); assets/js/contact.js sends it without
 * a page load and prefills it from the enquiry list (?enquiry=1).
 */
get_header();
$biz       = evanza_business();
$saved     = isset( $_GET['e'] ) ? get_transient( 'evanza_form_' . sanitize_key( wp_unslash( $_GET['e'] ) ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
$errors    = $saved ? $saved['errors'] : array();
$values    = $saved ? $saved['values'] : array();
$sent      = isset( $_GET['sent'] ); // phpcs:ignore WordPress.Security.NonceVerification
$value     = static function ( $key ) use ( $values ) {
	return isset( $values[ $key ] ) ? $values[ $key ] : '';
};
$catalogue = evanza_get_products();
$map_query = 'Pipaliya Maliya Highway, Chachavadarda, Maliya, Morbi, Gujarat, India';
?>
<main id="main" class="site-main contact">
	<section class="contact-room room" data-room data-tone="light" style="--room: var(--blush)">
		<div class="container">
			<h1 class="contact__title">Let's talk.</h1>
			<p class="lede">Tell us the shapes, colours and quantities you are after. We reply by email or phone, usually within a working day.</p>

			<div class="contact__grid">
				<div class="contact__form" id="form">
					<div class="form-success" data-success role="status" tabindex="-1" <?php echo $sent ? '' : 'hidden'; ?>>
						<p class="t-3">Thank you. We have your enquiry.</p>
						<p>We will reply by email or phone soon. If it is urgent, call or message one of the directors on the right.</p>
						<a class="btn" href="<?php echo esc_url( home_url( '/collection/' ) ); ?>">Back to the collection</a>
					</div>

					<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-contact-form novalidate <?php echo $sent ? 'hidden' : ''; ?>>
						<input type="hidden" name="action" value="evanza_contact">
						<?php wp_nonce_field( 'evanza_contact', '_evanza_nonce' ); ?>
						<p class="form__notice" data-prefill-note hidden></p>
						<?php if ( isset( $errors['form'] ) ) : ?>
							<p class="form__error" role="alert"><?php echo esc_html( $errors['form'] ); ?></p>
						<?php endif; ?>

						<div class="form__row">
							<div class="field">
								<label for="f-name">Name</label>
								<input class="input" id="f-name" name="name" type="text" autocomplete="name" required value="<?php echo esc_attr( $value( 'name' ) ); ?>" <?php echo isset( $errors['name'] ) ? 'aria-invalid="true" aria-describedby="e-name"' : ''; ?>>
								<span class="field__error" id="e-name" data-error="name"><?php echo isset( $errors['name'] ) ? esc_html( $errors['name'] ) : ''; ?></span>
							</div>
							<div class="field">
								<label for="f-company">Company</label>
								<input class="input" id="f-company" name="company" type="text" autocomplete="organization" value="<?php echo esc_attr( $value( 'company' ) ); ?>">
							</div>
						</div>
						<div class="form__row">
							<div class="field">
								<label for="f-email">Email</label>
								<input class="input" id="f-email" name="email" type="email" autocomplete="email" required value="<?php echo esc_attr( $value( 'email' ) ); ?>" <?php echo isset( $errors['email'] ) ? 'aria-invalid="true" aria-describedby="e-email"' : ''; ?>>
								<span class="field__error" id="e-email" data-error="email"><?php echo isset( $errors['email'] ) ? esc_html( $errors['email'] ) : ''; ?></span>
							</div>
							<div class="field">
								<label for="f-phone">Phone</label>
								<input class="input" id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" value="<?php echo esc_attr( $value( 'phone' ) ); ?>" <?php echo isset( $errors['phone'] ) ? 'aria-invalid="true" aria-describedby="e-phone"' : ''; ?>>
								<span class="field__error" id="e-phone" data-error="phone"><?php echo isset( $errors['phone'] ) ? esc_html( $errors['phone'] ) : ''; ?></span>
							</div>
						</div>
						<div class="form__row">
							<div class="field">
								<label for="f-country">Country</label>
								<input class="input" id="f-country" name="country" type="text" autocomplete="country-name" value="<?php echo esc_attr( $value( 'country' ) ); ?>">
							</div>
							<div class="field">
								<label for="f-interest">Interested in</label>
								<select class="select" id="f-interest" name="interest">
									<option value="">Not sure yet</option>
									<?php foreach ( $catalogue['categories'] as $c ) : ?>
										<option value="<?php echo esc_attr( $c['name'] ); ?>" <?php selected( $value( 'interest' ), $c['name'] ); ?>><?php echo esc_html( $c['name'] ); ?></option>
									<?php endforeach; ?>
									<option value="Custom printing" <?php selected( $value( 'interest' ), 'Custom printing' ); ?>>Custom printing</option>
								</select>
							</div>
						</div>
						<div class="field">
							<label for="f-message">Message</label>
							<textarea class="textarea" id="f-message" name="message" rows="6" required <?php echo isset( $errors['message'] ) ? 'aria-invalid="true" aria-describedby="e-message"' : ''; ?>><?php echo esc_textarea( $value( 'message' ) ); ?></textarea>
							<span class="field__error" id="e-message" data-error="message"><?php echo isset( $errors['message'] ) ? esc_html( $errors['message'] ) : ''; ?></span>
						</div>
						<div class="hp" aria-hidden="true">
							<label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
						</div>
						<p class="form__error" data-form-error role="alert" hidden></p>
						<button class="btn btn--lg" type="submit" data-submit>Send enquiry</button>
					</form>
				</div>

				<aside class="contact__side" aria-label="Contact details">
					<ul class="people">
						<?php foreach ( $biz['people'] as $person ) : ?>
							<li class="person">
								<h2 class="t-3"><?php echo esc_html( $person['name'] ); ?></h2>
								<p class="small"><?php echo esc_html( evanza_format_phone( $person['phone'] ) ); ?></p>
								<p class="person__links">
									<a class="link" href="tel:+91<?php echo esc_attr( $person['phone'] ); ?>">Call <?php echo esc_html( $person['name'] ); ?></a>
									<a class="link" href="https://wa.me/91<?php echo esc_attr( $person['phone'] ); ?>" target="_blank" rel="noopener">WhatsApp <?php echo esc_html( $person['name'] ); ?></a>
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="contact__address">
						<p><a class="link" href="mailto:<?php echo esc_attr( $biz['email'] ); ?>"><?php echo esc_html( $biz['email'] ); ?></a></p>
						<address><?php echo esc_html( $biz['address'] ); ?></address>
					</div>
				</aside>
			</div>

			<div class="contact__map">
				<iframe title="Map of the Evanza Tablewares factory on the Pipaliya Maliya Highway near Morbi" src="https://www.google.com/maps?q=<?php echo rawurlencode( $map_query ); ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
			</div>
			<a class="link contact__maplink" href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo rawurlencode( $map_query ); ?>" target="_blank" rel="noopener">Open the map in Google Maps</a>
		</div>
	</section>
</main>
<?php get_footer(); ?>
