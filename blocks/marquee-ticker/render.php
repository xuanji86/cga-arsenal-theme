<?php
/**
 * OSA · Marquee Ticker — looping text strip with separator.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$items  = (array) ( $attributes['items'] ?? [] );
$sep    = isset( $attributes['sep'] ) ? trim( $attributes['sep'] ) : '·';
$invert = ! empty( $attributes['invert'] );

$items = array_values( array_filter( array_map( 'trim', $items ), 'strlen' ) );

// Fall back to Customizer setting (osa_marquee_items, one per line) when
// the block is inserted without explicit items.
if ( empty( $items ) && function_exists( 'osa_marquee_items' ) ) {
	$items = osa_marquee_items();
}

if ( empty( $items ) ) {
	return '';
}

$wrapper = get_block_wrapper_attributes( [
	'class' => 'osa-marquee' . ( $invert ? ' osa-marquee--invert' : '' ),
] );

// Single set of items, joined by separators (no separator after the last item).
$pieces = [];
foreach ( $items as $idx => $item ) {
	$pieces[] = '<span>' . esc_html( $item ) . '</span>';
	if ( $idx !== array_key_last( $items ) ) {
		$pieces[] = '<span class="osa-marquee__sep">' . esc_html( $sep ) . '</span>';
	}
}

echo '<div ' . $wrapper . '>';
echo '<div class="osa-marquee__track">' . implode( '', $pieces ) . '</div>';
echo '</div>';
