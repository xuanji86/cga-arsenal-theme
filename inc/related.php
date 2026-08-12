<?php
/**
 * OSA — related-product discovery.
 *
 * Deterministic related-product resolver (cache-safe: no rand), the
 * sold-out rescue block + mobile strip on the single-product summary,
 * stock-flip cache purge, the per-product JSON used by the client-side
 * recently-viewed strip, and the discovery JS enqueue.
 *
 * Relatedness chain, in-stock only: same pa_model → same pa_manufacturer
 * → same product_cat within ±40% price band → newest in stock.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * IDs of in-stock products related to $product_id, best match first.
 *
 * @param int $product_id Product post ID.
 * @param int $limit      Max IDs to return.
 * @return int[]
 */
function osa_get_related_product_ids( $product_id, $limit = 8 ) {
	$product_id = (int) $product_id;
	$limit      = max( 1, (int) $limit );
	if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
		return [];
	}

	$found = [];
	$tiers = [
		[ 'tax' => 'pa_model' ],
		[ 'tax' => 'pa_manufacturer' ],
		[ 'tax' => 'product_cat', 'price_band' => true ],
		[], // newest in-stock, site-wide fallback
	];

	foreach ( $tiers as $tier ) {
		$remaining = $limit - count( $found );
		if ( $remaining < 1 ) {
			break;
		}
		$found = array_merge( $found, osa_related_query_tier( $product_id, $tier, $remaining, $found ) );
	}

	return $found;
}

/**
 * One tier of the relatedness chain; up to $remaining new IDs, newest first.
 *
 * @param int   $product_id Current product.
 * @param array $tier       ['tax' => taxonomy] + optional ['price_band' => true].
 * @param int   $remaining  How many more IDs are needed.
 * @param int[] $exclude    Already-selected IDs.
 * @return int[]
 */
function osa_related_query_tier( $product_id, $tier, $remaining, $exclude ) {
	// Sold-out and hidden products never qualify. WooCommerce maintains both
	// as product_visibility terms — far cheaper than a _stock_status meta query.
	$tax_query = [
		[
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => [ 'exclude-from-catalog', 'outofstock' ],
			'operator' => 'NOT IN',
		],
	];

	if ( ! empty( $tier['tax'] ) ) {
		$terms = get_the_terms( $product_id, $tier['tax'] );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return [];
		}
		$tax_query[] = [
			'taxonomy' => $tier['tax'],
			'field'    => 'term_id',
			'terms'    => wp_list_pluck( $terms, 'term_id' ),
		];
	}

	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $remaining,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'post__not_in'   => array_merge( [ $product_id ], $exclude ),
		'tax_query'      => $tax_query,
	];

	if ( ! empty( $tier['price_band'] ) ) {
		$price = (float) get_post_meta( $product_id, '_price', true );
		if ( $price > 0 ) {
			$args['meta_query'] = [
				[
					'key'     => '_price',
					'value'   => [ $price * 0.6, $price * 1.4 ],
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(10,2)',
				],
			];
		}
	}

	$q = new WP_Query( $args );
	return array_map( 'intval', $q->posts );
}

/**
 * Human label for a product's strip heading: manufacturer, else primary
 * category, else a neutral fallback. Also used by the catalog-grid block
 * in strip mode.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function osa_related_strip_label( $product_id ) {
	$label = function_exists( 'osa_product_term' ) ? osa_product_term( 'pa_manufacturer', $product_id ) : '';
	if ( ! $label ) {
		$cats  = get_the_terms( $product_id, 'product_cat' );
		$label = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'the rack';
	}
	return $label;
}

/**
 * Sold-out rescue block on the single-product summary. Sits at 32 — right
 * after the specs table (27) and osa-growth's OOS alert form (31), which
 * WooCommerce's block-template compatibility layer renders ABOVE the
 * price row; on a sold-out page that is prime real estate. In-stock
 * products get nothing here (their strip is a template-level block so it
 * never pushes the add-to-cart row down).
 */
add_action( 'woocommerce_single_product_summary', 'osa_render_related_summary', 32 );
function osa_render_related_summary() {
	global $product;
	if ( ! $product instanceof WC_Product || $product->is_in_stock() ) {
		return;
	}
	// SOLD archives render their own strip further down the info column, after
	// the sale record (inc/sold-archive.php §3.1). Rendering here as well would
	// duplicate it AND put the "ready today" pitch above the news that this one
	// sold. Ordinary out-of-stock products — restockable uniform-price lots —
	// are untouched and keep this placement.
	if ( function_exists( 'osa_is_sold_archived' ) && osa_is_sold_archived( $product ) ) {
		return;
	}
	$ids = osa_get_related_product_ids( $product->get_id(), 3 );
	if ( ! $ids ) {
		return;
	}
	echo '<section class="osa-related-rescue" data-osa-list="comparable_oos">';
	echo '<p class="osa-mono-caps osa-related-kicker">Still in the Case</p>';
	echo '<h2 class="osa-related-title">Comparable pieces, ready today</h2>';
	echo '<div class="osa-catalog-grid osa-catalog-grid--cols-3 osa-related-rescue__grid">';
	foreach ( $ids as $id ) {
		osa_render_product_card_compact( $id, '16-9' );
	}
	echo '</div></section>';
}

/**
 * A sold or restocked piece changes every page that recommends it, and
 * LiteSpeed caches whole pages — flush the site cache on real stock-status
 * flips. ~100-product catalog: purge-all is cheap. No-op where LiteSpeed
 * is absent (local Docker) because nothing listens to the action.
 */
add_action( 'woocommerce_product_set_stock_status', 'osa_related_purge_on_stock_flip', 20 );
function osa_related_purge_on_stock_flip() {
	do_action( 'litespeed_purge_all' );
}

/**
 * Per-product JSON consumed by the client-side recently-viewed strip
 * (assets/js/osa-discovery.js). Product data only — identical for every
 * visitor of the URL, so full-page caching is unaffected.
 */
add_action( 'wp_footer', 'osa_related_print_product_json', 5 );
function osa_related_print_product_json() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return;
	}
	$img = get_the_post_thumbnail_url( $product->get_id(), 'medium' );
	printf(
		'<script type="application/json" id="osa-product-json">%s</script>',
		wp_json_encode( [
			'id'    => $product->get_id(),
			// Titles arrive entity-encoded (&#8243; etc.); the strip renders
			// them as text, so decode once here or entities show literally.
			'name'  => html_entity_decode( get_the_title( $product->get_id() ), ENT_QUOTES, 'UTF-8' ),
			'price' => html_entity_decode(
				wp_strip_all_tags( wc_price( (float) $product->get_price() ) ),
				ENT_QUOTES,
				'UTF-8'
			),
			'url'   => get_permalink( $product->get_id() ),
			'img'   => $img ? $img : '',
		] )
	);
}

/**
 * Discovery JS (Reddit banner + recently-viewed). Same guarded-enqueue
 * pattern as inc/ux.php — inert until the asset file exists.
 */
add_action( 'wp_enqueue_scripts', function () {
	$rel  = '/assets/js/osa-discovery.js';
	$path = OSA_THEME_DIR . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script( 'osa-discovery', OSA_THEME_URI . $rel, [], (string) filemtime( $path ), true );
}, 20 );
