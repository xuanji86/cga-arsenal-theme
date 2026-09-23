<?php
/**
 * OSA — ffl-core's MAP verdict for the prices the theme prints itself.
 *
 * ffl-core (>= 0.11.0, FflCore\Catalog\MapPrice) hides a product's price on the
 * storefront while it is below the manufacturer's MAP. Anything that goes
 * through get_price_html() is already covered there; this is for the places
 * the theme prints a price itself (recently-viewed JSON, the SOLD band). They
 * must not publish what the product page hides.
 *
 * Without MapPrice (ffl-core missing or older) every function here is inert:
 * the theme prints exactly what it did before.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

use FflCore\Catalog\MapPrice;

/**
 * ffl-core's verdict for a product whose price the storefront hides, or null.
 *
 * null = not hidden, or no MapPrice. Otherwise MapPrice::state(): mode, and
 * low/high = the advertised MAP in cents. Memoised per request by product ID
 * (a variable product's verdict loads every child); nothing a page view does
 * changes a price.
 *
 * @param mixed $product WC_Product|null.
 * @return array|null
 */
function osa_map_state( $product ) {
	static $memo = [];
	if ( ! $product instanceof WC_Product || ! class_exists( MapPrice::class ) ) {
		return null;
	}
	$id = $product->get_id();
	if ( ! array_key_exists( $id, $memo ) ) {
		$state       = MapPrice::state( $product );
		$memo[ $id ] = empty( $state['hidden'] ) ? null : $state;
	}
	return $memo[ $id ];
}

/**
 * The text the product page shows in place of a hidden price.
 *
 * @param array $state A non-null osa_map_state().
 * @return string
 */
function osa_map_label( array $state ) {
	return MapPrice::label( (string) $state['mode'] );
}

/**
 * Would the product's MAP hide $amount, were it the price? For a figure that is
 * not the current price (the SOLD band's realized price), judged by MapPrice's
 * own rule on the MAP meta the POS pushes.
 *
 * @param int   $post_id Product ID.
 * @param mixed $amount  Decimal string.
 * @return bool
 */
function osa_map_hides_amount( $post_id, $amount ) {
	if ( ! class_exists( MapPrice::class ) ) {
		return false;
	}
	return MapPrice::hides(
		MapPrice::to_cents( get_post_meta( $post_id, '_osa_map_price', true ) ),
		MapPrice::normalize_mode( get_post_meta( $post_id, '_osa_map_mode', true ) ),
		MapPrice::to_cents( $amount )
	);
}
