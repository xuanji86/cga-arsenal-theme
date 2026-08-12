<?php
/**
 * OSA · LOT Badge — renders the catalog lot pill.
 *
 * If the block is inside a post template (woocommerce_loop) and no `lot` attribute
 * is set, fall back to the current product's `_osa_lot_number`.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$lot = isset( $attributes['lot'] ) ? trim( $attributes['lot'] ) : '';

if ( '' === $lot && isset( $block->context['postId'] ) ) {
	$lot = osa_product_meta( 'lot_number', (int) $block->context['postId'] );
}

if ( '' === $lot ) {
	return; // Nothing to render.
}

echo osa_lot_badge( $lot );
