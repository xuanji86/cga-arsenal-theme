<?php
/**
 * Pre-order corner tag on product cards: `_osa_preorder` = "1" on an in-stock
 * product adds "Pre-order" as the first corner tag; anything else leaves the
 * tags exactly as they were; a sold-out card keeps its corner for "Sold".
 */
require __DIR__ . '/bootstrap.php';

$GLOBALS['t_meta'] = [];
function get_the_ID() { return 7; }
function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['t_meta'][ $id ][ $key ] ?? ''; }

require_once OSA_THEME_DIR . '/inc/helpers.php';

class Fake_Card_Product {
	public function __construct( private bool $in_stock = true ) {}
	public function is_in_stock() { return $this->in_stock; }
}

function t_tags( $meta, $in_stock = true, $before = [] ) {
	$GLOBALS['t_meta'] = [ 7 => $meta ];
	return apply_filters( 'osa_card_tags', $before, 7, new Fake_Card_Product( $in_stock ) );
}

osa_assert( [ 'Pre-order' ] === t_tags( [ '_osa_preorder' => '1' ] ), 'flagged in-stock product gets the tag' );
osa_assert( [ 'Pre-order', 'Free Shipping' ] === t_tags( [ '_osa_preorder' => '1' ], true, [ 'Free Shipping' ] ), 'pre-order takes the first slot, others follow' );
osa_assert( [] === t_tags( [] ), 'no meta: no tag' );
osa_assert( [] === t_tags( [ '_osa_preorder' => '' ] ), 'cleared meta: no tag' );
osa_assert( [] === t_tags( [ '_osa_preorder' => '0' ] ), 'zero: no tag' );
osa_assert( [ 'Free Shipping' ] === t_tags( [], true, [ 'Free Shipping' ] ), 'non-preorder leaves other tags untouched' );
osa_assert( [] === t_tags( [ '_osa_preorder' => '1' ], false ), 'sold-out card keeps its corner for Sold' );
osa_assert( [] === apply_filters( 'osa_card_tags', [], 7, null ), 'no product context: no tag' );
osa_pass( 'pre-order corner tag follows _osa_preorder on in-stock cards only' );
