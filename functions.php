<?php
/**
 * OSA Arsenal — block theme bootstrap.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

define( 'OSA_THEME_VERSION', '0.1.0' );
// Parent-theme dir/URI: use the TEMPLATE (parent) location, not the stylesheet
// location, so the parent's own inc/ modules and base CSS still load when a
// child theme (e.g. osa-arsenal-dealer) is active. On the retail site (no child)
// these resolve identically.
define( 'OSA_THEME_DIR', get_template_directory() );
define( 'OSA_THEME_URI', get_template_directory_uri() );

/**
 * Theme support: WooCommerce, post thumbnails, title tag, HTML5.
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );

	// WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_editor_style( 'assets/css/osa-base.css' );
} );

/**
 * Front-end assets.
 */
add_action( 'wp_enqueue_scripts', function () {
	$css_path = OSA_THEME_DIR . '/assets/css/osa-base.css';
	wp_enqueue_style(
		'osa-base',
		OSA_THEME_URI . '/assets/css/osa-base.css',
		[],
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : OSA_THEME_VERSION
	);
}, 20 );

/**
 * Theme modules.
 */
require_once OSA_THEME_DIR . '/inc/product-meta.php';
require_once OSA_THEME_DIR . '/inc/blocks.php';
require_once OSA_THEME_DIR . '/inc/patterns.php';
require_once OSA_THEME_DIR . '/inc/helpers.php';
require_once OSA_THEME_DIR . '/inc/menus.php';
require_once OSA_THEME_DIR . '/inc/customizer.php';
require_once OSA_THEME_DIR . '/inc/age-gate.php';
require_once OSA_THEME_DIR . '/inc/login-page.php';
require_once OSA_THEME_DIR . '/inc/account-page.php';
require_once OSA_THEME_DIR . '/inc/search.php';
require_once OSA_THEME_DIR . '/inc/shop.php';
require_once OSA_THEME_DIR . '/inc/cart-ajax.php';
require_once OSA_THEME_DIR . '/inc/ux.php';
require_once OSA_THEME_DIR . '/inc/related.php';
require_once OSA_THEME_DIR . '/inc/promo.php';
require_once OSA_THEME_DIR . '/inc/sale-flash.php';
require_once OSA_THEME_DIR . '/inc/sold-archive.php';

/*
 * SEO: stop advertising per-post/product comment feeds (/product/x/feed/) in
 * <head> — Google crawls them as junk ("Crawled - currently not indexed").
 * Keeps the main site feed link (feed_links, priority 2).
 */
remove_action( 'wp_head', 'feed_links_extra', 3 );

/*
 * SEO: the order-status plugin registers its `order_status` CPT as public, so
 * internal workflow entities (e.g. /order_status/ffl-required/) leak into
 * wp-sitemap.xml and render publicly. Drop them from the sitemap and noindex
 * the pages themselves.
 */
add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {
	unset( $post_types['order_status'] );
	return $post_types;
} );
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_singular( 'order_status' ) || is_post_type_archive( 'order_status' ) ) {
		$robots['noindex'] = true;
		$robots['nofollow'] = true;
	}
	return $robots;
} );

/*
 * SEO: Google Merchant listings rejects the store's compound
 * "<label>::<serial>" WooCommerce SKUs (GSC: "Invalid sku"). Emit just the
 * serial half in the Product structured data. Never touches the real SKUs —
 * they drive ffl_woo_sync / FastBound A&D bookkeeping.
 */
add_filter( 'woocommerce_structured_data_product', function ( $markup ) {
	if ( isset( $markup['sku'] ) && is_string( $markup['sku'] ) && false !== strpos( $markup['sku'], '::' ) ) {
		$serial = trim( substr( $markup['sku'], strrpos( $markup['sku'], '::' ) + 2 ) );
		if ( '' !== $serial ) {
			$markup['sku'] = $serial;
		}
	}
	return $markup;
}, 20 );
