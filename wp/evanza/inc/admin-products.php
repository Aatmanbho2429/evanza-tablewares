<?php
/**
 * Admin editing: product details, the colour-variant repeater, category files, and the
 * "Remove demo products" tool. Everything saved here flows through evanza_get_products().
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	return;
}

const EVANZA_BADGES = array(
	'new'        => 'New',
	'bestseller' => 'Bestseller',
	'custom'     => 'Custom printing',
);

function evanza_shape_options() {
	return array(
		'classic'  => 'Classic (straight sides)',
		'latte'    => 'Latte (wide cone)',
		'belly'    => 'Belly (rounded)',
		'espresso' => 'Espresso (small cup)',
		'heart'    => 'Heart handle',
	);
}

// ---------------------------------------------------------------------------
// Assets
// ---------------------------------------------------------------------------

add_action( 'admin_enqueue_scripts', 'evanza_admin_assets' );
function evanza_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$is_product  = 'evanza_product' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	$is_category = 'evanza_category' === $screen->taxonomy;
	if ( ! $is_product && ! $is_category ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'evanza-admin', evanza_asset_url( 'admin/admin.css' ), array( 'wp-color-picker' ), evanza_asset_version( 'admin/admin.css' ) );
	$deps = array( 'jquery', 'jquery-ui-sortable' );
	if ( $is_product ) {
		$deps[] = 'wp-color-picker';
	}
	wp_enqueue_script( 'evanza-admin', evanza_asset_url( 'admin/editor.js' ), $deps, evanza_asset_version( 'admin/editor.js' ), true );
}

// ---------------------------------------------------------------------------
// Product meta boxes
// ---------------------------------------------------------------------------

add_action( 'add_meta_boxes_evanza_product', 'evanza_product_meta_boxes' );
function evanza_product_meta_boxes() {
	add_meta_box( 'evanza-details', __( 'Mug details', 'evanza' ), 'evanza_render_details_box', 'evanza_product', 'normal', 'high' );
	add_meta_box( 'evanza-variants', __( 'Colours', 'evanza' ), 'evanza_render_variants_box', 'evanza_product', 'normal', 'high' );
}

add_filter( 'enter_title_here', static function ( $text, $post ) {
	return 'evanza_product' === $post->post_type ? __( 'Mug name', 'evanza' ) : $text;
}, 10, 2 );

function evanza_render_details_box( $post ) {
	wp_nonce_field( 'evanza_product_meta', 'evanza_product_nonce' );
	$shape    = get_post_meta( $post->ID, '_evanza_shape', true ) ?: 'classic';
	$badges   = (array) get_post_meta( $post->ID, '_evanza_badges', true );
	$featured = (bool) get_post_meta( $post->ID, '_evanza_featured', true );
	$specs    = wp_parse_args(
		(array) get_post_meta( $post->ID, '_evanza_specs', true ),
		array(
			'capacity'       => '',
			'material'       => '',
			'finish'         => '',
			'microwaveSafe'  => true,
			'dishwasherSafe' => true,
			'moq'            => '',
			'set'            => '',
		)
	);
	?>
	<p class="description">Write the description in the main box below this one, and choose the category on the right. Add the colours in the box after this.</p>
	<div class="evz-grid">
		<p>
			<label for="evz-shape"><strong>Shape</strong></label>
			<select id="evz-shape" name="evanza_shape">
				<?php foreach ( evanza_shape_options() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $shape, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="description">Sets the 3D mug and the drawn pictures.</span>
		</p>
		<p>
			<strong>Badges</strong><br>
			<?php foreach ( EVANZA_BADGES as $value => $label ) : ?>
				<label class="evz-inline"><input type="checkbox" name="evanza_badges[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $badges, true ) ); ?>> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
			<br><label class="evz-inline"><input type="checkbox" name="evanza_featured" value="1" <?php checked( $featured ); ?>> Featured (shown first)</label>
		</p>
		<p>
			<label for="evz-capacity"><strong>Capacity</strong></label>
			<input id="evz-capacity" type="text" name="evanza_specs[capacity]" value="<?php echo esc_attr( $specs['capacity'] ); ?>" placeholder="330 ml">
		</p>
		<p>
			<label for="evz-material"><strong>Material</strong></label>
			<input id="evz-material" type="text" name="evanza_specs[material]" value="<?php echo esc_attr( $specs['material'] ); ?>" placeholder="Stoneware">
		</p>
		<p>
			<label for="evz-finish"><strong>Finish</strong></label>
			<input id="evz-finish" type="text" name="evanza_specs[finish]" value="<?php echo esc_attr( $specs['finish'] ); ?>" placeholder="Glossy">
		</p>
		<p>
			<label for="evz-moq"><strong>Minimum order</strong></label>
			<input id="evz-moq" type="text" name="evanza_specs[moq]" value="<?php echo esc_attr( $specs['moq'] ); ?>" placeholder="500 pcs">
		</p>
		<p>
			<label for="evz-set"><strong>Set contents</strong> (gift sets only)</label>
			<input id="evz-set" type="text" name="evanza_specs[set]" value="<?php echo esc_attr( $specs['set'] ); ?>" placeholder="Set of 2 mugs in a gift box">
		</p>
		<p>
			<label class="evz-inline"><input type="checkbox" name="evanza_specs[microwaveSafe]" value="1" <?php checked( ! empty( $specs['microwaveSafe'] ) ); ?>> Microwave safe</label>
			<label class="evz-inline"><input type="checkbox" name="evanza_specs[dishwasherSafe]" value="1" <?php checked( ! empty( $specs['dishwasherSafe'] ) ); ?>> Dishwasher safe</label>
		</p>
	</div>
	<?php
}

/** One colour row. $i is the field key; $v the stored variant (or null for the blank template). */
function evanza_variant_row( $i, $v ) {
	$families = evanza_color_families();
	$name     = $v ? $v['name'] : '';
	$family   = $v && isset( $v['family'] ) ? $v['family'] : 'white';
	$outside  = $v ? ( ! empty( $v['body'] ) ? $v['body'] : $v['hex'] ) : '#d9d9d4';
	$inside   = $v && ! empty( $v['inner'] ) ? $v['inner'] : $outside;
	$handle   = $v && ! empty( $v['handle'] ) ? $v['handle'] : $outside;
	$photos   = array();
	if ( $v && ! empty( $v['images'] ) ) {
		foreach ( (array) $v['images'] as $item ) {
			if ( is_string( $item ) && false !== strpos( $item, 'assets/img/mock/' ) ) {
				continue; // a drawn render, regenerated from the colours
			}
			$photos[] = $item;
		}
	}
	$tile = $v && ! empty( $v['render'] ) ? $v['render'] : '';
	$tile_is_demo_render = is_string( $tile ) && false !== strpos( $tile, 'assets/img/mock/' );
	$n    = 'evanza_variants[' . $i . ']';
	?>
	<div class="evz-variant" data-key="<?php echo esc_attr( $i ); ?>">
		<div class="evz-variant__bar">
			<span class="dashicons dashicons-menu evz-handle" title="Drag to reorder"></span>
			<strong class="evz-variant__title"><?php echo $name ? esc_html( $name ) : 'New colour'; ?></strong>
			<button type="button" class="button-link button-link-delete evz-remove">Remove this colour</button>
		</div>
		<div class="evz-variant__fields">
			<p><label>Colour name<br><input type="text" class="regular-text evz-name" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $name ); ?>" placeholder="Midnight Navy"></label></p>
			<p>
				<label>Colour family (for the colour filter)<br>
					<select name="<?php echo esc_attr( $n ); ?>[family]">
						<?php foreach ( $families as $f ) : ?>
							<option value="<?php echo esc_attr( $f['slug'] ); ?>" <?php selected( $family, $f['slug'] ); ?>><?php echo esc_html( $f['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</p>
			<div class="evz-colours">
				<p><span class="evz-colour-label">Outside</span><input type="text" class="evz-color" aria-label="Outside colour" name="<?php echo esc_attr( $n ); ?>[body]" value="<?php echo esc_attr( $outside ); ?>"></p>
				<p><span class="evz-colour-label">Inside</span><input type="text" class="evz-color" aria-label="Inside colour" name="<?php echo esc_attr( $n ); ?>[inner]" value="<?php echo esc_attr( $inside ); ?>"></p>
				<p><span class="evz-colour-label">Handle</span><input type="text" class="evz-color" aria-label="Handle colour" name="<?php echo esc_attr( $n ); ?>[handle]" value="<?php echo esc_attr( $handle ); ?>"></p>
			</div>
		</div>

		<div class="evz-media">
			<p><strong>Photos</strong> <span class="description">Real photos of this colour. Drag to reorder. Optional.</span></p>
			<ul class="evz-photos">
				<?php foreach ( $photos as $item ) : ?>
					<?php $thumb = is_numeric( $item ) ? wp_get_attachment_image_url( (int) $item, 'thumbnail' ) : evanza_resolve_asset( (string) $item ); ?>
					<li data-value="<?php echo esc_attr( $item ); ?>"><img src="<?php echo esc_url( $thumb ); ?>" alt=""><button type="button" class="evz-photo-remove" aria-label="Remove photo">&times;</button></li>
				<?php endforeach; ?>
			</ul>
			<input type="hidden" class="evz-photos-input" name="<?php echo esc_attr( $n ); ?>[images]" value="<?php echo esc_attr( wp_json_encode( array_values( $photos ) ) ); ?>">
			<button type="button" class="button evz-add-photos">Add photos</button>
		</div>

		<div class="evz-tile">
			<p><strong>Tile picture</strong> <span class="description">Optional, a transparent PNG of the mug. Leave it empty and we draw one from the colours above.</span></p>
			<div class="evz-tile__preview"><?php if ( $tile && ! $tile_is_demo_render ) : ?><img src="<?php echo esc_url( evanza_resolve_image( $tile ) ); ?>" alt=""><?php endif; ?></div>
			<input type="hidden" class="evz-tile-input" name="<?php echo esc_attr( $n ); ?>[render]" value="<?php echo esc_attr( $tile_is_demo_render ? '' : $tile ); ?>">
			<button type="button" class="button evz-choose-tile">Choose picture</button>
			<button type="button" class="button-link evz-clear-tile">Remove</button>
		</div>

		<input type="hidden" name="<?php echo esc_attr( $n ); ?>[slug]" value="<?php echo esc_attr( $v ? $v['slug'] : '' ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $n ); ?>[hex_keep]" value="<?php echo esc_attr( $v ? $v['hex'] : '' ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $n ); ?>[body_keep]" value="<?php echo esc_attr( $v && ! empty( $v['body'] ) ? $v['body'] : '' ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $n ); ?>[look_keep]" value="<?php echo esc_attr( $v ? md5( $outside . $inside . $handle ) : '' ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $n ); ?>[render_keep]" value="<?php echo esc_attr( $tile_is_demo_render ? $tile : '' ); ?>">
	</div>
	<?php
}

function evanza_render_variants_box( $post ) {
	$variants = array_values( array_filter( (array) get_post_meta( $post->ID, '_evanza_variants', true ) ) );
	?>
	<p class="description">One row per colour. People pick these on the product page and in the Collection colour filters. Add at least one.</p>
	<div id="evz-variants">
		<?php foreach ( $variants as $i => $v ) : ?>
			<?php evanza_variant_row( 'v' . $i, $v ); ?>
		<?php endforeach; ?>
	</div>
	<p><button type="button" class="button button-primary" id="evz-add-variant">Add a colour</button></p>
	<script type="text/template" id="evz-variant-template"><?php evanza_variant_row( '__i__', null ); ?></script>
	<?php
}

add_action( 'save_post_evanza_product', 'evanza_save_product_meta', 10, 2 );
function evanza_save_product_meta( $post_id, $post ) {
	if ( ! isset( $_POST['evanza_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['evanza_product_nonce'] ) ), 'evanza_product_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$shape = isset( $_POST['evanza_shape'] ) ? sanitize_key( wp_unslash( $_POST['evanza_shape'] ) ) : 'classic';
	update_post_meta( $post_id, '_evanza_shape', array_key_exists( $shape, evanza_shape_options() ) ? $shape : 'classic' );

	$badges = isset( $_POST['evanza_badges'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['evanza_badges'] ) ) : array();
	update_post_meta( $post_id, '_evanza_badges', array_values( array_intersect( $badges, array_keys( EVANZA_BADGES ) ) ) );
	update_post_meta( $post_id, '_evanza_featured', empty( $_POST['evanza_featured'] ) ? 0 : 1 );

	$raw   = isset( $_POST['evanza_specs'] ) ? (array) wp_unslash( $_POST['evanza_specs'] ) : array();
	$specs = array();
	foreach ( array( 'capacity', 'material', 'finish', 'moq', 'set' ) as $key ) {
		$value = isset( $raw[ $key ] ) ? sanitize_text_field( $raw[ $key ] ) : '';
		if ( '' !== $value ) {
			$specs[ $key ] = $value;
		}
	}
	$specs['microwaveSafe']  = ! empty( $raw['microwaveSafe'] );
	$specs['dishwasherSafe'] = ! empty( $raw['dishwasherSafe'] );
	update_post_meta( $post_id, '_evanza_specs', $specs );

	$families = wp_list_pluck( evanza_color_families(), 'slug' );
	$rows     = isset( $_POST['evanza_variants'] ) ? (array) wp_unslash( $_POST['evanza_variants'] ) : array();
	$saved    = array();
	$slugs    = array();
	foreach ( $rows as $row ) {
		$name = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
		if ( '' === $name ) {
			continue;
		}
		$color  = static function ( $key, $fallback ) use ( $row ) {
			$c = isset( $row[ $key ] ) ? sanitize_hex_color( $row[ $key ] ) : '';
			return $c ? strtolower( $c ) : $fallback;
		};
		$outside = $color( 'body', '#d9d9d4' );
		$inside  = $color( 'inner', $outside );
		$handle  = $color( 'handle', $outside );

		// Keep the swatch colour of demo two-tone mugs when the outside colour is unchanged.
		$hex_keep  = isset( $row['hex_keep'] ) ? sanitize_hex_color( $row['hex_keep'] ) : '';
		$body_keep = isset( $row['body_keep'] ) ? sanitize_hex_color( $row['body_keep'] ) : '';
		$old_out   = $body_keep ? $body_keep : $hex_keep;
		if ( $hex_keep && $old_out && strtolower( $old_out ) === $outside ) {
			$hex  = strtolower( $hex_keep );
			$body = $body_keep ? strtolower( $body_keep ) : '';
		} else {
			$hex  = $outside;
			$body = '';
		}

		$slug = isset( $row['slug'] ) ? sanitize_title( $row['slug'] ) : '';
		if ( ! $slug ) {
			$slug = sanitize_title( $name );
		}
		$base = $slug;
		for ( $n = 2; in_array( $slug, $slugs, true ); $n++ ) {
			$slug = $base . '-' . $n;
		}
		$slugs[] = $slug;

		$images = array();
		$list   = isset( $row['images'] ) ? json_decode( (string) $row['images'], true ) : array();
		foreach ( is_array( $list ) ? $list : array() as $item ) {
			if ( is_numeric( $item ) && 'attachment' === get_post_type( (int) $item ) ) {
				$images[] = (int) $item;
			} elseif ( is_string( $item ) && 0 === strpos( $item, 'assets/' ) && false === strpos( $item, '..' ) && false === strpos( $item, 'assets/img/mock/' ) ) {
				$images[] = $item;
			}
		}

		$render = '';
		$tile   = isset( $row['render'] ) ? $row['render'] : '';
		if ( is_numeric( $tile ) && 'attachment' === get_post_type( (int) $tile ) ) {
			$render = (int) $tile;
		} elseif ( ! empty( $row['render_keep'] ) && isset( $row['look_keep'] ) && md5( $outside . $inside . $handle ) === $row['look_keep'] ) {
			$kept = sanitize_text_field( $row['render_keep'] );
			if ( 0 === strpos( $kept, 'assets/img/mock/' ) && false === strpos( $kept, '..' ) ) {
				$render = $kept; // the demo render still matches these colours
			}
		}

		$variant = array(
			'slug'   => $slug,
			'name'   => $name,
			'family' => isset( $row['family'] ) && in_array( $row['family'], $families, true ) ? $row['family'] : 'white',
			'hex'    => $hex,
			'inner'  => $inside,
			'handle' => $handle,
			'images' => $images,
		);
		if ( $body ) {
			$variant['body'] = $body;
		}
		if ( $render ) {
			$variant['render'] = $render;
		}
		$saved[] = $variant;
	}
	if ( ! $saved ) {
		$saved[] = array(
			'slug'   => 'standard',
			'name'   => 'Standard',
			'family' => 'white',
			'hex'    => '#d9d9d4',
			'inner'  => '#d9d9d4',
			'handle' => '#d9d9d4',
			'images' => array(),
		);
	}
	update_post_meta( $post_id, '_evanza_variants', $saved );
}

// ---------------------------------------------------------------------------
// Category files: catalogue PDF and cover image
// ---------------------------------------------------------------------------

function evanza_term_file_field( $name, $label, $help, $value, $type ) {
	?>
	<div class="evz-file" data-type="<?php echo esc_attr( $type ); ?>">
		<input type="text" class="evz-file__url large-text" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" readonly>
		<button type="button" class="button evz-file__choose">Choose <?php echo esc_html( $label ); ?></button>
		<button type="button" class="button-link evz-file__clear">Remove</button>
	</div>
	<p class="description"><?php echo esc_html( $help ); ?></p>
	<?php
}

add_action( 'evanza_category_add_form_fields', static function () {
	wp_nonce_field( 'evanza_term_meta', 'evanza_term_nonce' );
	echo '<div class="form-field"><label for="evanza_catalogue">Catalogue PDF</label>';
	evanza_term_file_field( 'evanza_catalogue', 'PDF', 'The "Download catalogue" file for this category. Leave empty to use the main catalogue.', '', 'pdf' );
	echo '</div><div class="form-field"><label for="evanza_cover">Cover image</label>';
	evanza_term_file_field( 'evanza_cover', 'image', 'A picture for this category.', '', 'image' );
	echo '</div>';
} );

add_action( 'evanza_category_edit_form_fields', static function ( $term ) {
	wp_nonce_field( 'evanza_term_meta', 'evanza_term_nonce' );
	echo '<tr class="form-field"><th scope="row"><label for="evanza_catalogue">Catalogue PDF</label></th><td>';
	evanza_term_file_field( 'evanza_catalogue', 'PDF', 'The "Download catalogue" file for this category. Leave empty to use the main catalogue.', (string) get_term_meta( $term->term_id, 'evanza_catalogue', true ), 'pdf' );
	echo '</td></tr><tr class="form-field"><th scope="row"><label for="evanza_cover">Cover image</label></th><td>';
	evanza_term_file_field( 'evanza_cover', 'image', 'A picture for this category.', (string) get_term_meta( $term->term_id, 'evanza_cover', true ), 'image' );
	echo '</td></tr>';
} );

add_action( 'created_evanza_category', 'evanza_save_term_meta' );
add_action( 'edited_evanza_category', 'evanza_save_term_meta' );
function evanza_save_term_meta( $term_id ) {
	if ( ! isset( $_POST['evanza_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['evanza_term_nonce'] ) ), 'evanza_term_meta' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	foreach ( array( 'evanza_catalogue', 'evanza_cover' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = wp_unslash( $_POST[ $key ] );
			// Keep a theme-relative path (demo data) or a full URL; nothing else.
			$value = 0 === strpos( $value, 'assets/' ) && false === strpos( $value, '..' ) ? sanitize_text_field( $value ) : esc_url_raw( $value );
			update_term_meta( $term_id, $key, $value );
		}
	}
}

// ---------------------------------------------------------------------------
// Demo products: labelled in the list, removable in one click under Tools
// ---------------------------------------------------------------------------

add_filter( 'display_post_states', static function ( $states, $post ) {
	if ( 'evanza_product' === $post->post_type && get_post_meta( $post->ID, '_evanza_demo', true ) ) {
		$states['evanza_demo'] = __( 'Demo', 'evanza' );
	}
	return $states;
}, 10, 2 );

function evanza_demo_product_ids() {
	return get_posts(
		array(
			'post_type'   => 'evanza_product',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_evanza_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

add_action( 'admin_menu', static function () {
	add_management_page( __( 'Remove demo products', 'evanza' ), __( 'Remove demo products', 'evanza' ), 'manage_options', 'evanza-demo', 'evanza_render_demo_tool' );
} );

function evanza_render_demo_tool() {
	$ids     = evanza_demo_product_ids();
	$removed = isset( $_GET['removed'] ) ? (int) $_GET['removed'] : -1; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1>Remove demo products</h1>
		<?php if ( $removed >= 0 ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( sprintf( '%d demo products removed.', $removed ) ); ?></p></div>
		<?php endif; ?>
		<p>The theme came with sample mugs so the site looks complete. Once you have added your own, remove the samples here. Your own products are never touched.</p>
		<p><strong><?php echo esc_html( count( $ids ) ); ?></strong> demo products are on the site now. They are marked "Demo" in the Products list.</p>
		<?php if ( $ids ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Delete all demo products? This cannot be undone.');">
				<input type="hidden" name="action" value="evanza_remove_demo">
				<?php wp_nonce_field( 'evanza_remove_demo' ); ?>
				<?php submit_button( 'Remove demo products', 'delete' ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_post_evanza_remove_demo', static function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'evanza' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'evanza_remove_demo' );
	$count = 0;
	foreach ( evanza_demo_product_ids() as $id ) {
		if ( wp_delete_post( $id, true ) ) {
			++$count;
		}
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'evanza-demo', 'removed' => $count ), admin_url( 'tools.php' ) ) );
	exit;
} );
