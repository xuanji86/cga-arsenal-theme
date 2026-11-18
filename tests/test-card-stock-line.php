<?php
/**
 * Card stock line: a pre-order (`_osa_preorder` = "1") has no inventory — its
 * stock is the pre-order slots left — so the card shows no count; any other
 * in-stock product keeps its line; a sold-out card keeps showing nothing.
 */
require __DIR__ . '/bootstrap.php';

$GLOBALS['t_meta'] = [];
function get_the_ID() { return 7; }
function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['t_meta'][ $id ][ $key ] ?? ''; }

require_once OSA_THEME_DIR . '/inc/helpers.php';
require_once OSA_THEME_DIR . '/inc/cga-compliance.php';

class Fake_Stock_Product {
	public function __construct( private ?int $qty, private bool $in_stock = true ) {}
	public function get_id() { return 7; }
	public function is_in_stock() { return $this->in_stock; }
	public function get_stock_quantity() { return $this->qty; }
}

function t_line( $meta, ?int $qty, bool $in_stock = true ) {
	$GLOBALS['t_meta'] = [ 7 => $meta ];
	return cga_card_stock_line( new Fake_Stock_Product( $qty, $in_stock ) );
}

osa_assert( '' === t_line( [ '_osa_preorder' => '1' ], 3 ), 'pre-order with slots left: no count' );
osa_assert( '' === t_line( [ '_osa_preorder' => '1' ], 1 ), 'pre-order with one slot left: no "1 left"' );
osa_assert( '' === t_line( [ '_osa_preorder' => '1' ], null ), 'pre-order without a managed quantity: no line' );
osa_assert( '3 in stock' === t_line( [], 3 ), 'no meta: count as before' );
osa_assert( '3 in stock' === t_line( [ '_osa_preorder' => '' ], 3 ), 'cleared meta: count as before' );
osa_assert( '3 in stock' === t_line( [ '_osa_preorder' => '0' ], 3 ), 'zero: count as before' );
osa_assert( '1 left' === t_line( [], 1 ), 'one unit: "1 left" as before' );
osa_assert( 'In stock' === t_line( [], null ), 'unmanaged stock: "In stock" as before' );
osa_assert( '' === t_line( [], 0, false ), 'sold out: no line' );
osa_assert( '' === t_line( [ '_osa_preorder' => '1' ], 3, false ), 'sold-out pre-order: no line' );
osa_pass( 'card stock line is dropped for pre-order products only' );
