<?php
/**
 * CGA — California compliance chips.
 *
 * Renders a product's `pa_ca-status` attribute terms as the chip row used on
 * cards and the PDP. The terms are data, not decoration — they come from the
 * POS record via ffl_woo_sync (or manual tagging until that POS field ships);
 * nothing here invents a status at render time.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

const CGA_STATUS_TAX = 'pa_ca-status';

/**
 * Chip tone by term slug. ok = pine (sale can proceed as listed),
 * warn = clay (a rule that constrains the sale), solid = ink
 * (fulfilment fact). Unmapped slugs render as the neutral outline chip.
 */
function cga_status_chip_tone( $slug ) {
	$map = [
		'on-ca-roster'     => 'ok',
		'ca-compliant'     => 'ok',
		'pickup-only'      => 'warn',
		'ffl-required'     => 'warn',
		'ships-nationwide' => 'solid',
	];
	return $map[ $slug ] ?? '';
}

/**
 * The chip row for a product, or '' when it has no status terms.
 */
function cga_status_chips_html( $product_id = 0 ) {
	$product_id = $product_id ?: get_the_ID();
	if ( ! $product_id || ! taxonomy_exists( CGA_STATUS_TAX ) ) {
		return '';
	}
	$terms = get_the_terms( $product_id, CGA_STATUS_TAX );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$chips = '';
	foreach ( $terms as $term ) {
		$tone   = cga_status_chip_tone( $term->slug );
		$chips .= sprintf(
			'<span class="cga-tag%s">%s</span>',
			$tone ? ' cga-tag--' . $tone : '',
			esc_html( $term->name )
		);
	}
	return $chips ? '<div class="cga-tags">' . $chips . '</div>' : '';
}

/**
 * [cga_status_chips] — used from the single-product block template, where a
 * shortcode block is the reliable way to reach PHP from block markup.
 */
add_shortcode( 'cga_status_chips', function () {
	return cga_status_chips_html( get_the_ID() );
} );

/**
 * Card stock line ("4 in stock" / "1 left"), real inventory only.
 */
function cga_card_stock_line( $product ) {
	if ( ! $product || ! $product->is_in_stock() ) {
		return '';
	}
	$qty = $product->get_stock_quantity();
	if ( null === $qty ) {
		return __( 'In stock', 'osa-arsenal' );
	}
	if ( 1 === (int) $qty ) {
		return __( '1 left', 'osa-arsenal' );
	}
	/* translators: %d: units in stock */
	return sprintf( __( '%d in stock', 'osa-arsenal' ), (int) $qty );
}
