<?php
/**
 * MAP price hiding on the theme's own price surfaces (ffl-core PR #43).
 * CGA port of osa-arsenal-theme tests/test-map-price.php (no catalog-grid
 * ItemList offer here, and no LiteSpeed purge).
 *
 * ffl-core hides a below-MAP price wherever WooCommerce's price HTML goes. The
 * theme prints a price itself in two places:
 *
 *   inc/related.php       recently-viewed JSON → the page's label; no price once sold / out of stock
 *   inc/sold-archive.php  SOLD band figure     → dropped when THAT figure is below the MAP, date kept
 *
 * Neither may change anything while ffl-core lacks MapPrice or the product
 * carries no MAP.
 */
require __DIR__ . '/bootstrap.php';
require OSA_THEME_DIR . '/inc/map-price.php';

/** The one product double both surfaces accept. */
class WC_Product {
	public $id;
	public $price;
	public $in_stock;
	public function __construct( $id, $price, $in_stock = true ) {
		$this->id       = $id;
		$this->price    = $price;
		$this->in_stock = $in_stock;
	}
	public function get_id() {
		return $this->id;
	}
	public function get_price() {
		return $this->price;
	}
	public function is_in_stock() {
		return $this->in_stock;
	}
}
function is_product() {
	return true;
}
function get_queried_object_id() {
	return $GLOBALS['osa_test_queried_id'];
}
function wc_get_product( $id = null ) {
	return $GLOBALS['osa_test_products'][ (int) $id ] ?? null;
}
function get_post_meta( $id, $key, $single = false ) {
	return $GLOBALS['osa_test_meta'][ $id ][ $key ] ?? '';
}
function get_the_title( $id = null ) {
	return 'Product ' . $id;
}
function get_permalink( $id = null ) {
	return 'https://example.test/product/' . $id . '/';
}
function get_the_post_thumbnail_url( $id = null, $size = 'post-thumbnail' ) {
	return "https://example.test/img/{$id}-{$size}.jpg";
}
function wc_price( $amount ) {
	return '<span class="amount">$' . number_format( (float) $amount, 2 ) . '</span>';
}
function wp_strip_all_tags( $string ) {
	return trim( strip_tags( (string) $string ) );
}
function wp_json_encode( $data ) {
	return json_encode( $data );
}
function wp_kses_post( $html ) {
	return $html;
}

require OSA_THEME_DIR . '/inc/related.php';
require OSA_THEME_DIR . '/inc/sold-archive.php';

$hidden = new WC_Product( 101, '470.00' );
$plain  = new WC_Product( 102, '569' );
$GLOBALS['osa_test_products'] = [ 101 => $hidden, 102 => $plain ];

function osa_test_product_json( $id ) {
	$GLOBALS['osa_test_queried_id'] = $id;
	ob_start();
	osa_related_print_product_json();
	preg_match( '#<script type="application/json" id="osa-product-json">(.*)</script>#s', ob_get_clean(), $m );
	return json_decode( $m[1] ?? '', true );
}

/** A sold archive: out of stock + _osa_sold_at, band rendered for it. */
function osa_test_sold_band( $id, array $meta ) {
	$GLOBALS['osa_test_products'][ $id ]->in_stock = false;
	$GLOBALS['osa_test_meta'][ $id ]               = $meta + [ '_osa_sold_at' => '2026-09-01' ];
	$GLOBALS['osa_test_queried_id']                = $id;
	$block = [
		'blockName'   => 'core/group',
		'innerBlocks' => [ [ 'blockName' => 'woocommerce/product-price' ] ],
	];
	$band = osa_sold_price_row_block( '<original/>', $block );
	$GLOBALS['osa_test_products'][ $id ]->in_stock = true;
	$GLOBALS['osa_test_meta'][ $id ]               = [];
	return $band;
}

// ---- ffl-core missing / older than 0.11.0: everything exactly as before -----

osa_assert( null === osa_map_state( $hidden ), 'no MapPrice class → never hidden' );
osa_assert( '$470.00' === osa_test_product_json( 101 )['price'], 'no MapPrice → recently-viewed JSON keeps the real price' );
$band = osa_test_sold_band( 101, [ '_osa_sold_price' => '470', '_osa_map_price' => '499.99' ] );
osa_assert( false !== strpos( $band, '$470.00' ), 'no MapPrice → SOLD band keeps its figure even under a MAP, got ' . $band );
osa_pass( 'inert without ffl-core 0.11.0' );

// ---- ffl-core 0.11.0 present, product 101 listed under MAP ($499.99) ----------

require __DIR__ . '/ffl-core-map-price-stub.php';

$GLOBALS['osa_test_map'][ spl_object_id( $hidden ) ] = [ 'mode' => 'Show in cart', 'low' => 49999 ];

// Wrapper: state() once per product per request, label only on demand.
$calls = $GLOBALS['osa_test_map_calls'] ?? [];
$state = osa_map_state( $hidden );
osa_map_state( $hidden );
$state_calls = ( $GLOBALS['osa_test_map_calls']['state'] ?? 0 ) - ( $calls['state'] ?? 0 );
osa_assert( 1 === $state_calls, "osa_map_state() memoises state() per product, got {$state_calls} calls" );
osa_assert( is_array( $state ) && 49999 === $state['low'] && ! isset( $state['label'] ), 'osa_map_state() passes state() through and computes no label' );
osa_assert( ( $calls['label'] ?? 0 ) === ( $GLOBALS['osa_test_map_calls']['label'] ?? 0 ), 'osa_map_state() never calls label()' );
osa_assert( 'Add to cart to see price' === osa_map_label( $state ), 'osa_map_label() is MapPrice::label( mode )' );
osa_assert( null === osa_map_state( $plain ), 'a product with no MAP is not hidden' );
osa_assert( null === osa_map_state( null ), 'no product is not hidden' );
osa_pass( 'wrapper' );

// Recently viewed.
$json = osa_test_product_json( 101 );
osa_assert( 'Add to cart to see price' === $json['price'], 'recently-viewed JSON must carry the label, got ' . var_export( $json['price'], true ) );
osa_assert( false === strpos( wp_json_encode( $json ), '470' ), 'the real price must not appear anywhere in the JSON' );
osa_assert( '$569.00' === osa_test_product_json( 102 )['price'], 'a product without MAP keeps its price' );
$hidden->in_stock = false;
$json             = osa_test_product_json( 101 );
osa_assert( '' === $json['price'], 'out of stock under MAP: no add-to-cart label and no figure, got ' . var_export( $json['price'], true ) );
$GLOBALS['osa_test_meta'][101] = [ '_osa_sold_at' => '2026-09-01' ];
osa_assert( osa_is_sold_archived( $hidden ), 'fixture: 101 is a sold archive' );
$json = osa_test_product_json( 101 );
osa_assert( '' === $json['price'] && false === strpos( wp_json_encode( $json ), '470' ), 'sold archive under MAP: no label, no figure, got ' . var_export( $json['price'], true ) );
$GLOBALS['osa_test_meta'][101] = [];
$hidden->in_stock = true;
$plain->in_stock  = false;
osa_assert( '$569.00' === osa_test_product_json( 102 )['price'], 'out of stock without MAP keeps its price' );
$plain->in_stock = true;
$js = (string) file_get_contents( OSA_THEME_DIR . '/assets/js/osa-discovery.js' );
preg_match( "/var LS_RECENT = '([^']+)'/", $js, $m );
osa_assert( isset( $m[1] ) && 'osaRecent' !== $m[1], 'recently-viewed storage key must move past osaRecent (entries saved before MAP hold real prices), got ' . ( $m[1] ?? 'none' ) );
osa_pass( 'recently-viewed JSON' );

// SOLD band: the figure against the MAP, whatever today's price is.
$band = osa_test_sold_band( 102, [ '_osa_sold_price' => '470', '_osa_map_price' => '499.99', '_osa_map_mode' => 'Show in cart' ] );
osa_assert( false === strpos( $band, '470' ) && false !== strpos( $band, 'Sold On' ), 'sold below MAP while listed above it: no figure, date kept, got ' . $band );
$band = osa_test_sold_band( 101, [ '_osa_sold_price' => '520', '_osa_map_price' => '499.99' ] );
osa_assert( false !== strpos( $band, '$520.00' ), 'sold above MAP while listed below it: figure kept, got ' . $band );
$band = osa_test_sold_band( 102, [ '_osa_sold_price' => '470', '_osa_map_price' => '499.99' ] );
osa_assert( false === strpos( $band, '470' ) && false !== strpos( $band, 'Sold On' ), 'sold below MAP, no mode meta (= Floor): no figure, got ' . $band );
osa_assert( false !== strpos( osa_test_sold_band( 102, [ '_osa_sold_price' => '470', '_osa_map_price' => '499.99', '_osa_map_mode' => 'Ignore' ] ), '$470.00' ), 'mode Ignore keeps the figure' );
osa_assert( false !== strpos( osa_test_sold_band( 102, [ '_osa_sold_price' => '470' ] ), '$470.00' ), 'no MAP keeps the figure' );
osa_pass( 'SOLD band' );
