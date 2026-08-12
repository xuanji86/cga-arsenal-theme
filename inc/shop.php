<?php
/**
 * OSA — shop / catalog archive grid tuning.
 *
 * Columns and rows are WooCommerce-native settings, edited under
 * Customize → WooCommerce → Product Catalog ("Products per row" /
 * "Rows per page"). The theme honors them directly:
 *
 *   - Per-page count: we deliberately do NOT filter `loop_shop_per_page`, so
 *     WooCommerce's own default applies — products-per-row × rows-per-page —
 *     and every full page is complete rows.
 *   - Column count: the shop grid (templates/archive-product.html →
 *     core/post-template) is laid out by CSS. We publish the live
 *     `woocommerce_catalog_columns` value as the `--osa-shop-cols` custom
 *     property on the catalog archives, and assets/css/osa-base.css consumes it
 *     (tablet steps down to 2, phones to 1). That is what makes "Products per
 *     row" actually change the desktop column count.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Publish WooCommerce's "Products per row" as a CSS custom property on the shop
 * + product-taxonomy archives, so the catalog grid renders that many desktop
 * columns. Falls back to 3 if WooCommerce can't be queried.
 */
add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
			return;
		}
		$cols = function_exists( 'wc_get_default_products_per_row' )
			? (int) wc_get_default_products_per_row()
			: (int) get_option( 'woocommerce_catalog_columns', 3 );
		$cols = max( 1, min( 8, $cols ) );
		printf( '<style id="osa-shop-cols">:root{--osa-shop-cols:%d}</style>', $cols );
	},
	20
);

/**
 * Mobile filter collapse.
 *
 * The catalog filter sidebar (fcpf/product-filter-sidebar, ffl-core plugin)
 * stacks above the product grid below 900px and renders every section fully
 * expanded. assets/js/osa-shop-filter.js injects a "Filters" toggle that
 * collapses it on mobile. Loaded only where the filter renders — the shop page
 * and product taxonomy archives, which share templates/archive-product.html.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! function_exists( 'is_shop' ) ) {
			return; // WooCommerce inactive — no catalog, no filter.
		}
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}
		$rel  = '/assets/js/osa-shop-filter.js';
		$path = OSA_THEME_DIR . $rel;
		if ( ! file_exists( $path ) ) {
			return;
		}
		wp_enqueue_script(
			'osa-shop-filter',
			OSA_THEME_URI . $rel,
			[],
			(string) filemtime( $path ),
			true // in footer — runs after the filter markup is in the DOM.
		);
	},
	20
);
