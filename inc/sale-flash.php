<?php
/**
 * OSA — suppress WooCommerce's default sale flash on the single product page.
 *
 * The single-product template places the theme-styled
 * woocommerce/product-sale-badge block over the gallery, but WooCommerce's
 * product-image-gallery block ALSO hardwires
 * woocommerce_show_product_sale_flash() (ProductImageGallery::render), so an
 * on-sale product page grows a second, unstyled `span.onsale` circle in the
 * gallery corner. Blank the core flash there; everywhere else it passes
 * through untouched (the catalog grid uses its own osa_card_tags corner tags
 * and never renders the core flash anyway).
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_sale_flash', function ( $html ) {
	return is_product() ? '' : $html;
} );
