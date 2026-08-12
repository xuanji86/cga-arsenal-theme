<?php
/**
 * OSA · Product Card (Compact)
 *
 * Thin wrapper around osa_render_product_card_compact(). Designed to be used
 * inside a core/query loop where postId context is set.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
if ( ! $post_id ) {
	return;
}

$ar = isset( $attributes['ar'] ) ? $attributes['ar'] : '5-4';

// Wrap with block wrapper so editor styling/spacing controls work.
echo '<div ' . get_block_wrapper_attributes() . '>';
osa_render_product_card_compact( $post_id, $ar );
echo '</div>';
