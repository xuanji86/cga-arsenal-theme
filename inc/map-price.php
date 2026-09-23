<?php
/**
 * OSA — ffl-core's MAP verdict for the prices the theme prints itself.
 *
 * ffl-core (>= 0.11.0, FflCore\Catalog\MapPrice) hides a product's price on the
 * storefront while it is below the manufacturer's MAP. Anything that goes
 * through get_price_html() is already covered there; this is for the few places
 * the theme reads a price directly (recently-viewed JSON, the SOLD band). They
 * must not publish what the product page hides.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * The MAP state of a product whose price the storefront hides, or null.
 *
 * null = not hidden, or ffl-core missing / older than 0.11.0: print exactly as
 * before. Otherwise MapPrice::state() (low/high = the advertised MAP, in cents)
 * plus 'label', the text the product page shows instead of the price.
 *
 * @param mixed $product WC_Product|null.
 * @return array|null
 */
function osa_map_state( $product ) {
	// The only lines that name the ffl-core API: a rename there is a rename here.
	$state_of = [ 'FflCore\Catalog\MapPrice', 'state' ];
	$label_of = [ 'FflCore\Catalog\MapPrice', 'label' ];
	if ( ! is_callable( $state_of ) || ! is_callable( $label_of ) ) {
		return null;
	}
	$state = call_user_func( $state_of, $product );
	if ( empty( $state['hidden'] ) ) {
		return null;
	}
	$state['label'] = (string) call_user_func( $label_of, (string) ( $state['mode'] ?? '' ) );
	return $state;
}
