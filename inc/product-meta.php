<?php
/**
 * OSA specs table — frontend renderer for the single product summary.
 *
 * The OSA Catalog Details metabox + save handler + REST exposure moved
 * into FFL Core (includes/catalog/class-catalog-details.php). Only the
 * presentation half (this file) lives in the theme because it depends
 * on `osa-specs` CSS and the woocommerce_single_product_summary hook
 * priority that places the table between Add-to-Cart and the description.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_single_product_summary', 'osa_render_specs_table', 27 );
function osa_render_specs_table() {
	$post_id = get_the_ID();

	$rows = [
		'Category'     => '',
		'Manufacturer' => osa_product_term( 'pa_manufacturer', $post_id ),
		'Model'        => osa_product_term( 'pa_model', $post_id ),
		'Caliber'      => osa_get_product_caliber( $post_id ),
		'UPC'          => osa_product_gtin( $post_id ),
		'Barrel'       => osa_product_meta( 'barrel', $post_id ),
		'Mfg. Year'    => osa_product_meta( 'year', $post_id ),
		'Serial #'     => current_user_can( 'edit_products' ) ? osa_product_meta( 'serial', $post_id ) : '',
		'Finish'       => '',
		'Bore'         => osa_product_meta( 'bore', $post_id ),
		'Provenance'   => osa_product_meta( 'provenance', $post_id ),
	];

	$cats = get_the_terms( $post_id, 'product_cat' );
	if ( $cats && ! is_wp_error( $cats ) ) {
		$rows['Category'] = $cats[0]->name;
	}

	$pct = (int) osa_product_meta( 'finish_pct', $post_id );
	if ( $pct ) {
		$rows['Finish'] = $pct . '% original';
	}

	// Not plain 'strlen': meta could come back as a non-scalar (corrupt or
	// serialized value), which would fatal strlen() on PHP 8.
	$rows = array_filter( $rows, static function ( $value ) {
		return is_scalar( $value ) && '' !== (string) $value;
	} );
	if ( ! $rows ) {
		return;
	}

	echo '<table class="osa-specs" style="margin-top:24px"><tbody>';
	foreach ( $rows as $label => $value ) {
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $label ), esc_html( $value ) );
	}
	echo '</tbody></table>';
}
