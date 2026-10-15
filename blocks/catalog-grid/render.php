<?php
/**
 * OSA · Catalog Grid — hard-bordered N-up grid of WooCommerce products.
 *
 * Renders each cell inline with the same shape as the product-card-compact
 * block. (We don't try to embed the card block via do_blocks because passing
 * postId context outside core/query is brittle.)
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$cols    = max( 1, min( 6, (int) ( $attributes['cols']  ?? 4 ) ) );
$limit   = max( 1, (int) ( $attributes['limit'] ?? 12 ) );
$orderby = sanitize_key( $attributes['orderby'] ?? 'date' );
$order   = strtoupper( $attributes['order'] ?? 'DESC' );
$cat     = sanitize_text_field( $attributes['category'] ?? '' );
$model   = sanitize_text_field( $attributes['model'] ?? '' );
$exclude = array_map( 'intval', (array) ( $attributes['exclude'] ?? [] ) );
$related = ! empty( $attributes['related'] );
$strip   = ! empty( $attributes['strip'] );
$instock = ! empty( $attributes['instock'] );
$list    = sanitize_text_field( $attributes['list'] ?? '' );
$schema  = 'collection' === ( $attributes['schema'] ?? '' ) ? 'collection' : '';
$rotation = in_array( $attributes['rotation'] ?? '', [ 'daily', 'hourly' ], true ) ? $attributes['rotation'] : '';
$pinned  = max( 0, (int) ( $attributes['pinned'] ?? 0 ) );

// Strip mode is a related-products presentation: only meaningful on an
// in-stock single product (sold-out pages get the summary rescue block).
if ( $strip ) {
	if ( ! $related || ! is_singular( 'product' ) ) {
		return '';
	}
	$strip_product = function_exists( 'wc_get_product' ) ? wc_get_product( get_queried_object_id() ) : null;
	if ( ! $strip_product || ! $strip_product->is_in_stock() ) {
		return '';
	}
}

if ( $related && is_singular( 'product' ) && function_exists( 'osa_get_related_product_ids' ) ) {
	// Relevance mode: deterministic in-stock neighbors of the current
	// product (same model → manufacturer → category/price band → newest).
	$ids = osa_get_related_product_ids( get_queried_object_id(), $limit );
	if ( ! $ids ) {
		return '';
	}
	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => count( $ids ),
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'no_found_rows'  => true,
	];
} else {
	$visibility_out = [ 'exclude-from-catalog' ];
	if ( $instock ) {
		$visibility_out[] = 'outofstock';
	}

	$args = [
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'orderby'        => $orderby,
		'order'          => 'ASC' === $order ? 'ASC' : 'DESC',
		'post_status'    => 'publish',
		'no_found_rows'  => true,
		'post__not_in'   => $exclude,
		'tax_query'      => [
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => $visibility_out,
				'operator' => 'NOT IN',
			],
		],
	];

	if ( $cat ) {
		$args['tax_query'][] = [
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array_map( 'trim', explode( ',', $cat ) ),
		];
	}

	if ( $model ) {
		$args['tax_query'][] = [
			'taxonomy' => 'pa_model',
			'field'    => 'slug',
			'terms'    => array_map( 'trim', explode( ',', $model ) ),
		];
	}

	// Daily / hourly rotation (homepage shelf; ported from OSA's floor): the
	// first `pinned` slots keep the orderby order (newest arrivals); the rest
	// are a seeded shuffle of everything else that matches, so older listings
	// get their turn. The seed is the store's local date (or date + hour), so
	// every visitor sees one lineup per period, and a LiteSpeed page cache (if
	// present) is capped at the next period boundary.
	if ( $rotation ) {
		$hourly  = 'hourly' === $rotation;
		$id_args = array_merge( $args, [ 'fields' => 'ids' ] );
		$ids     = [];
		if ( $pinned > 0 ) {
			$ids = ( new WP_Query( array_merge( $id_args, [ 'posts_per_page' => min( $pinned, $limit ) ] ) ) )->posts;
		}
		if ( $limit > count( $ids ) ) {
			$ids = array_merge( $ids, ( new WP_Query( array_merge( $id_args, [
				'posts_per_page' => $limit - count( $ids ),
				'post__not_in'   => array_merge( $exclude, $ids ),
				'orderby'        => 'RAND(' . (int) wp_date( $hourly ? 'YmdH' : 'Ymd' ) . ')',
			] ) ) )->posts );
		}
		$now  = new DateTimeImmutable( 'now', wp_timezone() );
		$next = $hourly ? $now->setTime( (int) $now->format( 'G' ), 0 )->modify( '+1 hour' ) : $now->modify( 'tomorrow' );
		do_action( 'litespeed_control_set_ttl', max( 60, $next->getTimestamp() - time() ), "OSA catalog-grid {$rotation} rotation" );
		if ( ! $ids ) {
			return '';
		}
		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => count( $ids ),
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'no_found_rows'  => true,
		];
	}
}

$q = new WP_Query( $args );
if ( ! $q->have_posts() ) {
	return '';
}

if ( $strip ) {
	// Mobile-only horizontal scroller (CSS hides it ≥783px). Placed at
	// template level so it never pushes the price/add-to-cart row down.
	$wrapper = get_block_wrapper_attributes( [
		'class'         => 'osa-related-strip-wrap',
		'data-osa-list' => 'mobile_strip',
	] );
	echo '<section ' . $wrapper . '>';
	printf(
		'<p class="osa-mono-caps osa-related-kicker">More from %s</p>',
		esc_html( function_exists( 'osa_related_strip_label' ) ? osa_related_strip_label( get_queried_object_id() ) : 'the rack' )
	);
	echo '<div class="osa-related-strip">';
	while ( $q->have_posts() ) {
		$q->the_post();
		osa_render_product_card_compact( get_the_ID(), '16-9' );
	}
	wp_reset_postdata();
	echo '</div></section>';
	return;
}

$wrapper_attrs = [ 'class' => 'osa-catalog-grid osa-catalog-grid--cols-' . $cols ];
if ( '' !== $list ) {
	// Explicit GA4 list id (model landings: "model_landing_<page-slug>") —
	// osa-growth analytics auto-instruments [data-osa-list] containers.
	$wrapper_attrs['data-osa-list'] = $list;
} elseif ( $related ) {
	$wrapper_attrs['data-osa-list'] = 'same_floor';
}
$wrapper = get_block_wrapper_attributes( $wrapper_attrs );

// CollectionPage/ItemList JSON-LD for model landing pages: singular pages
// only, and never for the related/strip presentations (they are page
// furniture, not the page's collection). url+name+position ONLY — offers/
// price stay on the product pages' own Product schema (a copy here would
// drift the moment a price changes).
$emit_schema  = 'collection' === $schema && ! $strip && ! $related && is_singular();
$schema_items = [];

echo '<div ' . $wrapper . '>';
while ( $q->have_posts() ) {
	$q->the_post();
	if ( $emit_schema ) {
		$schema_items[] = [
			'@type'    => 'ListItem',
			'position' => count( $schema_items ) + 1,
			'url'      => get_permalink(),
			'name'     => get_the_title(),
		];
	}
	osa_render_product_card_compact( get_the_ID(), '16-9' );
}
wp_reset_postdata();
echo '</div>';

if ( $emit_schema && $schema_items ) {
	$page_id   = get_queried_object_id();
	$permalink = get_permalink( $page_id );
	echo '<script type="application/ld+json">' . wp_json_encode( [
		'@context'   => 'https://schema.org',
		'@type'      => 'CollectionPage',
		'@id'        => $permalink . '#collection',
		'url'        => $permalink,
		'name'       => get_the_title( $page_id ),
		'mainEntity' => [
			'@type'           => 'ItemList',
			'numberOfItems'   => count( $schema_items ),
			'itemListElement' => $schema_items,
		],
	] ) . '</script>';
}
