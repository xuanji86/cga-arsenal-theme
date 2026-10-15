<?php
/**
 * Shared render-layer stubs for block render.php tests.
 *
 * Include AFTER tests/bootstrap.php. Provides the WP surface that dynamic
 * block render files touch (WP_Query, wrapper attrs, loop tags, kses, …),
 * driven by globals so each test process can stage its own scenario:
 *
 *   $GLOBALS['osa_test_posts']          id => [ 'title' => …, 'permalink' => … ]
 *                                       lookup map for get_permalink()/get_the_title()
 *                                       (products AND singular pages live here)
 *   $GLOBALS['osa_test_query_posts']    ordered post ids every WP_Query yields
 *   $GLOBALS['osa_test_query_queue']    optional: one id list per WP_Query, in
 *                                       call order (falls back to query_posts)
 *   $GLOBALS['osa_test_is_singular']    is_singular() with no arg
 *   $GLOBALS['osa_test_is_singular_types']  [ 'product' => bool, … ]
 *   $GLOBALS['osa_test_queried_id']     get_queried_object_id()
 *   $GLOBALS['osa_test_query_log']      every WP_Query $args, in call order
 *   $GLOBALS['osa_test_kses_calls']     every wp_kses() [ html, allowed ] call
 *
 * The WP_Query fake does NOT interpret tax_query — tests assert on the
 * recorded $args instead, and stage the result set explicitly.
 *
 * @package OSA_Arsenal
 */

$GLOBALS['osa_test_posts']             = [];
$GLOBALS['osa_test_query_posts']       = [];
$GLOBALS['osa_test_is_singular']       = false;
$GLOBALS['osa_test_is_singular_types'] = [];
$GLOBALS['osa_test_queried_id']        = 0;
$GLOBALS['osa_test_query_log']         = [];
$GLOBALS['osa_test_kses_calls']        = [];
$GLOBALS['osa_test_current_id']        = 0;

// Theme helpers render files call; functions.php loads them in prod.
require_once OSA_THEME_DIR . '/inc/map-price.php';

/**
 * Mimics core get_block_wrapper_attributes(): attr="esc'd value" pairs.
 *
 * The block-supports layer core computes from the block instance's own
 * attributes (align → alignwide/alignfull class, spacing → inline style,
 * plus the wp-block-<name> class) is STAGED, not computed:
 *
 *   $GLOBALS['osa_test_block_supports'] = [
 *       'class' => 'wp-block-osa-model-links alignwide',
 *       'style' => 'margin-bottom:40px',
 *   ];
 *
 * Unset (the default) → identical output to a harness with no supports layer,
 * so every existing byte-identity fence keeps its meaning. Merge order copies
 * core verbatim: style then class first, each as "<extra> <supports>", then the
 * remaining extra attributes in insertion order.
 */
function get_block_wrapper_attributes( $extra = [] ) {
	$supports = (array) ( $GLOBALS['osa_test_block_supports'] ?? [] );
	$extra    = (array) $extra;
	$attrs    = [];
	foreach ( [ 'style', 'class' ] as $name ) {
		$parts = array_filter( [ (string) ( $extra[ $name ] ?? '' ), (string) ( $supports[ $name ] ?? '' ) ], 'strlen' );
		if ( $parts ) {
			$attrs[ $name ] = implode( ' ', $parts );
		}
	}
	foreach ( $extra as $key => $value ) {
		if ( 'style' !== $key && 'class' !== $key ) {
			$attrs[ $key ] = $value;
		}
	}
	$pairs = [];
	foreach ( $attrs as $key => $value ) {
		$pairs[] = $key . '="' . esc_attr( $value ) . '"';
	}
	return implode( ' ', $pairs );
}

class WP_Query {
	public $args;
	public $posts = [];
	private $ids = [];
	private $i   = 0;

	public function __construct( $args = [] ) {
		$this->args                     = $args;
		$GLOBALS['osa_test_query_log'][] = $args;
		// A render that runs several queries stages one result set per query,
		// in call order, in $GLOBALS['osa_test_query_queue'].
		$this->ids                      = array_values(
			! empty( $GLOBALS['osa_test_query_queue'] )
				? array_shift( $GLOBALS['osa_test_query_queue'] )
				: ( $GLOBALS['osa_test_query_posts'] ?? [] )
		);
		$this->posts                    = $this->ids;
	}
	public function have_posts() {
		return $this->i < count( $this->ids );
	}
	public function the_post() {
		$GLOBALS['osa_test_current_id'] = $this->ids[ $this->i ];
		$this->i++;
	}
}

function wp_reset_postdata() {
	$GLOBALS['osa_test_current_id'] = 0;
}
function get_the_ID() {
	return $GLOBALS['osa_test_current_id'];
}
function get_permalink( $post_id = null ) {
	$id = null === $post_id ? $GLOBALS['osa_test_current_id'] : $post_id;
	return $GLOBALS['osa_test_posts'][ $id ]['permalink'] ?? '';
}
function get_the_title( $post_id = null ) {
	$id = null === $post_id ? $GLOBALS['osa_test_current_id'] : $post_id;
	return $GLOBALS['osa_test_posts'][ $id ]['title'] ?? '';
}
function get_queried_object_id() {
	return $GLOBALS['osa_test_queried_id'];
}
function is_singular( $type = null ) {
	if ( null === $type ) {
		return ! empty( $GLOBALS['osa_test_is_singular'] );
	}
	return ! empty( $GLOBALS['osa_test_is_singular_types'][ $type ] );
}
/**
 * Minimal WC_Product double: only what the blocks actually ask a product.
 */
class OSA_Test_Product {
	private $price;
	private $in_stock;
	private $short;
	private $long;
	public function __construct( $price = '', $in_stock = true, $short = '', $long = '' ) {
		$this->price    = $price;
		$this->in_stock = $in_stock;
		$this->short    = $short;
		$this->long     = $long;
	}
	public function get_short_description() {
		return $this->short;
	}
	public function get_description() {
		return $this->long;
	}
	public function get_price() {
		return $this->price;
	}
	public function is_in_stock() {
		return $this->in_stock;
	}
}
/**
 * Returns a double only for ids a test explicitly registered in
 * $GLOBALS['osa_test_products']; every other id stays null, which is what the
 * blocks see when WooCommerce has no product (and what strip mode, out of
 * scope for these tests, relied on before doubles existed).
 */
function wc_get_product( $id = null ) {
	return $GLOBALS['osa_test_products'][ (int) $id ] ?? null;
}
/**
 * Thumbnail URL double: ids listed in $GLOBALS['osa_test_no_thumb'] have none.
 */
function get_the_post_thumbnail_url( $post_id = null, $size = 'post-thumbnail' ) {
	$id = (int) $post_id;
	if ( in_array( $id, $GLOBALS['osa_test_no_thumb'] ?? [], true ) ) {
		return false;
	}
	return "https://example.test/img/{$id}-{$size}.jpg";
}
function wp_strip_all_tags( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
	return trim( strip_tags( $string ) );
}
function get_woocommerce_currency() {
	return $GLOBALS['osa_test_currency'] ?? 'USD';
}
function wc_get_price_decimals() {
	return 2;
}
function wp_json_encode( $data, $options = 0, $depth = 512 ) {
	return json_encode( $data, $options, $depth );
}

/**
 * Deterministic product-card marker so grid output is byte-comparable.
 */
function osa_render_product_card_compact( $post_id, $ar = '5-4' ) {
	printf( '<card:%d:%s>', $post_id, $ar );
}

/**
 * Recording wp_kses approximation: strips disallowed tags (keeps inner text,
 * like real kses) and drops attributes not whitelisted for the tag. Faithful
 * enough for the test vectors; the REAL guarantee in prod is core wp_kses —
 * tests additionally assert the exact $allowed array handed to it.
 */
function wp_kses( $string, $allowed_html, $allowed_protocols = [] ) {
	$GLOBALS['osa_test_kses_calls'][] = [
		'html'    => (string) $string,
		'allowed' => $allowed_html,
	];
	$out = strip_tags( (string) $string, array_keys( (array) $allowed_html ) );
	$out = preg_replace_callback(
		'/<(\w+)([^>]*?)(\/?)>/',
		static function ( $m ) use ( $allowed_html ) {
			$tag           = strtolower( $m[1] );
			$allowed_attrs = (array) ( $allowed_html[ $tag ] ?? [] );
			$kept          = '';
			if ( $allowed_attrs && preg_match_all( '/([a-zA-Z-]+)\s*=\s*("[^"]*"|\'[^\']*\')/', $m[2], $am, PREG_SET_ORDER ) ) {
				foreach ( $am as $a ) {
					if ( ! empty( $allowed_attrs[ strtolower( $a[1] ) ] ) ) {
						$kept .= ' ' . $a[1] . '=' . $a[2];
					}
				}
			}
			return '<' . $tag . $kept . $m[3] . '>';
		},
		$out
	);
	return $out;
}

/**
 * Render a block file the way core does: include with $attributes/$content/
 * $block in scope, output buffered. Early `return`s in the file behave the
 * same as in core's render-callback wrapper.
 *
 * @return string
 */
function osa_render_block_file( $file, array $attributes, $content = '', $block = null ) {
	ob_start();
	include $file;
	return ob_get_clean();
}
