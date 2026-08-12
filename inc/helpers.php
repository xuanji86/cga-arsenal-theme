<?php
/**
 * OSA — render helpers shared across blocks, patterns, and templates.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a `_osa_<key>` product post-meta value with a default.
 */
function osa_product_meta( $key, $product_id = 0, $default = '' ) {
	$product_id = $product_id ?: get_the_ID();
	$value      = get_post_meta( $product_id, '_osa_' . $key, true );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Render an inline LOT badge.
 */
function osa_lot_badge( $lot, $attrs = [] ) {
	$lot = trim( (string) $lot );
	if ( '' === $lot ) {
		return '';
	}
	$class = 'osa-lot-badge ' . ( $attrs['class'] ?? '' );
	return sprintf( '<span class="%s">%s</span>', esc_attr( $class ), esc_html( $lot ) );
}

/*
 * osa_grade_chip() and osa_get_product_grade() have been moved to the
 * ffl-core plugin (includes/catalog/condition-helpers.php) and now use
 * the pa_condition taxonomy (5-tier OSA standard). The plugin loads
 * before the theme, so callers below resolve to the plugin definitions.
 */

/**
 * Find an existing term by name or create it, returning the term_id.
 * Handles the find/insert race by re-reading on `term_exists` error.
 */
function osa_find_or_create_term_id( $name, $taxonomy ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( $term ) {
		return (int) $term->term_id;
	}
	$result = wp_insert_term( $name, $taxonomy );
	if ( ! is_wp_error( $result ) ) {
		return (int) $result['term_id'];
	}
	$existing_id = (int) $result->get_error_data( 'term_exists' );
	return $existing_id ?: 0;
}

/**
 * Resolve a product's caliber display string. Prefers the pa_caliber term
 * (canonical) and falls back to the legacy _osa_caliber post meta for
 * products migrated but never re-saved through OSA Catalog Details.
 */
function osa_get_product_caliber( $product_id = 0 ) {
	$product_id = $product_id ?: get_the_ID();
	if ( function_exists( 'wc_get_product_terms' ) ) {
		$names = wc_get_product_terms( $product_id, 'pa_caliber', [ 'fields' => 'names' ] );
		if ( ! empty( $names[0] ) ) {
			return (string) $names[0];
		}
	}
	return (string) osa_product_meta( 'caliber', $product_id );
}

/**
 * First term name of a product attribute taxonomy (e.g. pa_manufacturer,
 * pa_model), or '' when unset. POS data syncs straight into these WooCommerce
 * attributes, so the spec table reads them directly — no separate _osa_* meta.
 */
function osa_product_term( $taxonomy, $product_id = 0 ) {
	$product_id = $product_id ?: get_the_ID();
	if ( function_exists( 'wc_get_product_terms' ) ) {
		$names = wc_get_product_terms( $product_id, $taxonomy, [ 'fields' => 'names' ] );
		if ( ! empty( $names[0] ) ) {
			return (string) $names[0];
		}
	}
	return '';
}

/**
 * Product UPC/GTIN from WooCommerce's native global_unique_id field
 * ("GTIN, UPC, EAN, or ISBN"), where the POS sync writes the UPC.
 */
function osa_product_gtin( $product_id = 0 ) {
	$product_id = $product_id ?: get_the_ID();
	if ( function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $product_id );
		if ( $product && method_exists( $product, 'get_global_unique_id' ) ) {
			$gtin = (string) $product->get_global_unique_id();
			if ( '' !== $gtin ) {
				return $gtin;
			}
		}
	}
	return (string) get_post_meta( $product_id, '_global_unique_id', true );
}

/**
 * Render an OSA product image placeholder.
 *
 * @param string $label Placeholder caption.
 * @param string $ar    Aspect ratio token: 1-1, 4-3, 5-4, 3-4, 16-9, 16-10, 21-9.
 */
function osa_placeholder( $label = 'product shot', $ar = '4-3' ) {
	$ar    = sanitize_html_class( $ar );
	$label = esc_html( $label );
	return sprintf(
		'<span class="osa-bw-img osa-bw-img--ar-%1$s" role="img" aria-label="%2$s"><span class="osa-bw-img__label">%2$s</span></span>',
		esc_attr( $ar ),
		$label
	);
}

/**
 * Resolve a logo URL by variant.
 *
 * @param string $variant inline-dark | inline-light | stacked-dark | stacked-light | mark-dark | mark-light | banner-dark | banner-light
 */
function osa_logo_url( $variant = 'inline-dark' ) {
	$variant = sanitize_file_name( $variant );
	return OSA_THEME_URI . '/assets/logos/osa-' . $variant . '.png';
}

/**
 * Header cart-count badge markup. Always rendered (hidden when empty) with a
 * stable `.osa-cart-count` hook so it can be returned as a WooCommerce
 * add-to-cart fragment and updated live without a reload.
 *
 * @param int $count Cart item count.
 */
function osa_cart_count_badge( $count ) {
	$count = (int) $count;
	return sprintf(
		'<span class="osa-header-icon__badge osa-cart-count"%2$s>%1$d</span>',
		$count,
		$count > 0 ? '' : ' hidden'
	);
}

/**
 * Account link target + label, login-aware. Shared by the header controls and
 * the mobile nav account link so the blockified header matches the old pattern.
 *
 * @return array{url:string,label:string}
 */
function osa_account_link_info() {
	if ( is_user_logged_in() ) {
		$url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : site_url( '/my-account/' );
		$label = __( 'Account', 'osa-arsenal' );
	} else {
		$url   = defined( 'OSA_LOGIN_PATH' ) ? site_url( OSA_LOGIN_PATH ) : site_url( '/account/login/' );
		$label = __( 'Sign In', 'osa-arsenal' );
	}
	return [ 'url' => $url, 'label' => $label ];
}

/**
 * Phase-4 hook target: count consigned items by status. Reads a single
 * user meta map written by the consignment workflow (status → count).
 * Returns 0 for unknown statuses until that workflow is built.
 */
function osa_count_consigned_status( $user_id, $status ) {
	static $cache = [];
	if ( ! isset( $cache[ $user_id ] ) ) {
		$cache[ $user_id ] = (array) get_user_meta( $user_id, '_osa_consigned_status_counts', true );
	}
	return (int) ( $cache[ $user_id ][ $status ] ?? 0 );
}

/**
 * Resolve the tile list for a dealer KPI row variant. Lazy — only the
 * requested variant pays for its lookups.
 */
function osa_dealer_kpi_preset( $variant ) {
	switch ( $variant ) {
		case 'inventory':
			$uid = get_current_user_id();
			return [
				[ 'kind' => 'literal',         'label' => 'Listed',   'value' => (string) osa_count_consigned_status( $uid, 'on_floor' ) ],
				[ 'kind' => 'literal',         'label' => 'Reserved', 'value' => (string) osa_count_consigned_status( $uid, 'reserved' ) ],
				[ 'kind' => 'literal',         'label' => 'En Route', 'value' => (string) osa_count_consigned_status( $uid, 'en_route' ) ],
				[ 'kind' => 'literal',         'label' => 'Sold MTD', 'value' => (string) osa_count_consigned_status( $uid, 'sold' ) ],
				[ 'kind' => 'pending_payout',  'emphasis' => true ],
			];
		case 'account':
			return [
				[ 'kind' => 'lifetime_orders' ],
				[ 'kind' => 'watching' ],
				[ 'kind' => 'consigned' ],
				[ 'kind' => 'credit' ],
			];
		case 'dashboard':
		default:
			return [
				[ 'kind' => 'open_orders' ],
				[ 'kind' => 'in_transit' ],
				[ 'kind' => 'pending_payout' ],
				[ 'kind' => 'consigned' ],
			];
	}
}

/**
 * Render the OSA compact product card markup inline. Used by:
 *   - blocks/product-card-compact/render.php
 *   - blocks/catalog-grid/render.php (per-cell rendering)
 *   - patterns and templates as needed
 *
 * @param int    $post_id Product ID.
 * @param string $ar      Aspect ratio token (e.g. "5-4", "4-3").
 */
function osa_render_product_card_compact( $post_id, $ar = '5-4' ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return;
	}

	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $post_id ) : null;
	$name    = get_the_title( $post_id );
	$url     = get_permalink( $post_id );
	$ar_safe = sanitize_html_class( $ar );

	$lot     = osa_product_meta( 'lot_number', $post_id );
	$caliber = osa_get_product_caliber( $post_id );
	$year    = osa_product_meta( 'year', $post_id );
	// osa_get_product_grade() is defined in ffl-core; guard for graceful
	// degradation when the plugin is inactive.
	$grade   = function_exists( 'osa_get_product_grade' ) ? osa_get_product_grade( $post_id ) : null;

	printf(
		'<a class="osa-card" href="%s" data-pid="%d" data-pname="%s">',
		esc_url( $url ),
		$post_id,
		esc_attr( $name )
	);
	echo '<span class="osa-card__media">';

	if ( has_post_thumbnail( $post_id ) ) {
		printf(
			'<span class="osa-bw-img osa-bw-img--ar-%1$s osa-bw-img--flat" style="aspect-ratio:%2$s;">',
			esc_attr( $ar_safe ),
			esc_attr( str_replace( '-', '/', $ar_safe ) )
		);
		// Use a large, UNCROPPED source so the whole firearm is present (the
		// WooCommerce "thumbnail" size is hard-cropped + small, which clips the
		// barrel/stock and looks soft when scaled up into the grid cell).
		// object-fit:contain then shows the full photo with no edges clipped;
		// the paper-tint band on .osa-bw-img--flat fills any letterbox space.
		echo get_the_post_thumbnail(
			$post_id,
			'large',
			[
				'style'   => 'width:100%;height:100%;object-fit:contain;display:block;',
				'loading' => 'lazy',
				'sizes'   => '(max-width: 600px) 92vw, (max-width: 1024px) 46vw, 460px',
				// Fall back to the product name so grid cards are never alt-less
				// when the attachment has no alt set (image SEO + accessibility).
				'alt'     => $name,
			]
		);
		echo '</span>';
	} else {
		$cat_terms = get_the_terms( $post_id, 'product_cat' );
		$cat_label = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? strtolower( $cat_terms[0]->name ) : 'product shot';
		echo osa_placeholder( $cat_label, $ar_safe );
	}

	if ( $lot ) {
		echo osa_lot_badge( $lot );
	}
	if ( $product && ! $product->is_in_stock() ) {
		echo '<span class="osa-card__sold">Sold</span>';
	}
	// Corner tags over the card media (single top-right slot; sold cards keep
	// it for "Sold"). Hooked by e.g. the seasonal promo in inc/promo.php.
	foreach ( apply_filters( 'osa_card_tags', [], $post_id, $product ) as $card_tag ) {
		if ( is_string( $card_tag ) && '' !== $card_tag ) {
			printf( '<span class="osa-card__tag">%s</span>', esc_html( $card_tag ) );
		}
	}
	// Per owner: do NOT show the condition percentage badge over the product
	// image. (Condition is still shown on the single-product page via the
	// CONDITION · OSA STANDARD scale.) $grade intentionally unused here.
	unset( $grade );

	echo '</span>';

	echo '<span class="osa-card__body">';
	printf( '<span class="osa-card__name">%s</span>', esc_html( $name ) );

	$meta_bits = array_filter( [ $caliber, $year ] );
	if ( $meta_bits ) {
		printf( '<span class="osa-card__meta">%s</span>', esc_html( implode( ' · ', $meta_bits ) ) );
	}

	if ( $product ) {
		printf( '<span class="osa-card__price">%s</span>', wp_kses_post( $product->get_price_html() ) );
	}

	echo '</span>';
	echo '</a>';
}

/*
 * osa_grade_terms_ordered() and osa_grade_info_map() have been moved to
 * the ffl-core plugin (includes/catalog/condition-helpers.php) and now
 * use the pa_condition taxonomy (5-tier OSA standard).
 */
