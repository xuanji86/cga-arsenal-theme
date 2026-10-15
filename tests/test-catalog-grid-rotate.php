<?php
/**
 * catalog-grid `rotation` + `pinned` (ported from OSA's homepage floor): the
 * first `pinned` slots keep the newest arrivals and the rest are a seeded
 * shuffle of the remaining stock, so older listings get front-page time. One
 * lineup per store day (or hour); a LiteSpeed TTL ends at the next boundary.
 */
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/block-render-stubs.php';

function wp_timezone() {
	return new DateTimeZone( 'America/Los_Angeles' );
}
function wp_date( $format ) {
	return [ 'Ymd' => '20261014', 'YmdH' => '2026101419' ][ $format ] ?? '';
}

$GLOBALS['osa_ttl_calls'] = [];
add_action( 'litespeed_control_set_ttl', static function ( $ttl ) {
	$GLOBALS['osa_ttl_calls'][] = $ttl;
} );

function osa_rotate_render( array $attributes, array $queue ) {
	$GLOBALS['osa_test_query_queue'] = $queue;
	$GLOBALS['osa_test_query_posts'] = [];
	$GLOBALS['osa_test_query_log']   = [];
	$GLOBALS['osa_ttl_calls']        = [];
	return osa_render_block_file( OSA_THEME_DIR . '/blocks/catalog-grid/render.php', $attributes );
}

$shelf_attrs = [ 'cols' => 4, 'limit' => 8, 'instock' => true, 'category' => 'pistol,revolver,rifle,shotgun', 'rotation' => 'daily', 'pinned' => 4, 'exclude' => [ 99 ] ];

// Full pool: 4 newest pinned, 4 rotated, rendered in that order.
$out = osa_rotate_render( $shelf_attrs, [ [ 1, 2, 3, 4 ], [ 15, 8, 11, 6 ], [ 1, 2, 3, 4, 15, 8, 11, 6 ] ] );
osa_assert( 3 === count( $GLOBALS['osa_test_query_log'] ), 'pinned + rotated + render = 3 queries' );
[ $q_pin, $q_rot, $q_show ] = $GLOBALS['osa_test_query_log'];
osa_assert( 'ids' === $q_pin['fields'] && 4 === $q_pin['posts_per_page'] && 'date' === $q_pin['orderby'], 'pinned slots = newest by the grid orderby' );
osa_assert( [ 'pistol', 'revolver', 'rifle', 'shotgun' ] === $q_pin['tax_query'][1]['terms'] && in_array( 'outofstock', $q_pin['tax_query'][0]['terms'], true ), 'pinned query keeps the in-stock + gun filters' );
osa_assert( 4 === $q_rot['posts_per_page'] && 'RAND(20261014)' === $q_rot['orderby'], 'rotation fills the rest, seeded by the store date' );
osa_assert( [ 99, 1, 2, 3, 4 ] === $q_rot['post__not_in'], 'rotation skips the pinned products and the hero exclude' );
osa_assert( $q_pin['tax_query'] === $q_rot['tax_query'], 'rotation draws from the same pool' );
osa_assert( [ 1, 2, 3, 4, 15, 8, 11, 6 ] === $q_show['post__in'] && 'post__in' === $q_show['orderby'], 'render keeps pinned-then-rotated order' );
osa_assert( false !== strpos( $out, '<card:1:16-9><card:2:16-9><card:3:16-9><card:4:16-9><card:15:16-9>' ), 'newest row renders first, then the rotation' );
$expected = ( new DateTimeImmutable( 'tomorrow', wp_timezone() ) )->getTimestamp() - time();
osa_assert( 1 === count( $GLOBALS['osa_ttl_calls'] ) && abs( $GLOBALS['osa_ttl_calls'][0] - max( 60, $expected ) ) <= 2, 'daily TTL ends at local midnight' );

// Hourly: date + hour seed, TTL to the next local hour.
osa_rotate_render( [ 'limit' => 8, 'rotation' => 'hourly', 'pinned' => 2 ], [ [ 1, 2 ], [ 5 ], [ 1, 2, 5 ] ] );
osa_assert( 'RAND(2026101419)' === $GLOBALS['osa_test_query_log'][1]['orderby'] && 6 === $GLOBALS['osa_test_query_log'][1]['posts_per_page'], 'hourly seed; rotation asks for limit - pinned found' );
$now      = new DateTimeImmutable( 'now', wp_timezone() );
$expected = $now->setTime( (int) $now->format( 'G' ), 0 )->getTimestamp() + 3600 - time();
osa_assert( abs( $GLOBALS['osa_ttl_calls'][0] - max( 60, $expected ) ) <= 2, 'hourly TTL ends at the next hour' );

// Every slot pinned: no rotation query; pinned 0: the whole grid rotates.
osa_rotate_render( [ 'limit' => 4, 'pinned' => 4, 'rotation' => 'daily' ], [ [ 1, 2, 3, 4 ], [ 1, 2, 3, 4 ] ] );
osa_assert( 2 === count( $GLOBALS['osa_test_query_log'] ), 'pinned ≥ limit → no rotation query' );
osa_rotate_render( [ 'limit' => 6, 'rotation' => 'daily' ], [ [ 7, 3 ], [ 7, 3 ] ] );
osa_assert( 'RAND(20261014)' === $GLOBALS['osa_test_query_log'][0]['orderby'] && 6 === $GLOBALS['osa_test_query_log'][0]['posts_per_page'], 'pinned 0 → all slots rotate' );

// Empty pool → empty block; rotation off or unknown → one untouched query.
$out = osa_rotate_render( $shelf_attrs, [ [], [] ] );
osa_assert( '' === trim( (string) $out ) && 2 === count( $GLOBALS['osa_test_query_log'] ), 'empty pool → empty block' );
foreach ( [ [], [ 'rotation' => 'weekly' ] ] as $attrs ) {
	osa_rotate_render( $attrs + [ 'limit' => 8 ], [ [ 1, 2 ] ] );
	osa_assert( 1 === count( $GLOBALS['osa_test_query_log'] ) && 'date' === $GLOBALS['osa_test_query_log'][0]['orderby'] && [] === $GLOBALS['osa_ttl_calls'], 'no rotation → single newest-first query' );
}

osa_pass( 'catalog-grid rotation: newest pinned, rest rotates daily/hourly' );
