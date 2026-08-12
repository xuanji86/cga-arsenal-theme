<?php
/**
 * OSA — single-product AJAX add-to-cart.
 *
 * Progressive enhancement: assets/js/osa-add-to-cart.js intercepts the single
 * product add-to-cart form and posts to WooCommerce's wc-ajax endpoint, so the
 * buyer stays on the page and the header cart badge updates live. ANY
 * uncertainty (variable products, disabled button, network/parse error) falls
 * back to a normal form submit — the conversion path can never be worse than
 * today's reload.
 *
 * The header badge is registered as a WC add-to-cart fragment so its markup is
 * returned with the AJAX response and swapped in by the script.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the header cart-count badge as a fragment keyed by its selector.
 */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	$fragments['span.osa-cart-count'] = osa_cart_count_badge( $count );
	return $fragments;
} );

/**
 * Enqueue the interceptor on single product pages only.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$rel  = '/assets/js/osa-add-to-cart.js';
	$path = OSA_THEME_DIR . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'osa-add-to-cart',
		OSA_THEME_URI . $rel,
		[],
		(string) filemtime( $path ),
		true
	);
	wp_localize_script( 'osa-add-to-cart', 'osaCart', [
		'ajaxUrl'   => home_url( '/?wc-ajax=add_to_cart' ),
		'addedText' => __( 'In cart', 'osa-arsenal' ),
		'cartText'  => __( 'View cart', 'osa-arsenal' ),
		'cartUrl'   => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ),
	] );
}, 20 );
