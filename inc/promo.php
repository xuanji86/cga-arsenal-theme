<?php
/**
 * OSA — seasonal promo window (Independence Day · July 4, 2026).
 *
 * One switchboard for the time-boxed promotion. While the window is open:
 *   - the homepage teaser block (osa/promo-teaser) and the landing block
 *     (osa/promo-landing, /independence-day/) render;
 *   - the utility-strip message swaps to the coupon copy;
 *   - a "Independence Day" link is injected into the Footer · Shop menu.
 * Outside the window every hook is a no-op and the landing page 302s to the
 * shop, so the site reverts on its own after the end date — no deploy needed.
 *
 * Config lives in the `osa_promo_july4` option (merged over the defaults
 * below, dates read in the site timezone). Kill switch:
 *   wp option update osa_promo_july4 '{"enabled":0}' --format=json
 * then purge LiteSpeed. The JULY4 coupon itself is a normal WooCommerce
 * coupon (its own expiry matches the window) and is not managed here.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

const OSA_PROMO_PAGE_SLUG = 'independence-day';

/**
 * Promo configuration: option-over-defaults.
 *
 * @return array{enabled:int|bool,start:string,end:string,msg:string}
 */
function osa_promo_config() {
	$defaults = [
		'enabled' => 1,
		'start'   => '2026-07-03 00:00:00',
		'end'     => '2026-07-15 00:00:00', // through July 14, site tz (extended +3 days on 2026-07-11).
		'msg'     => 'Use Code JULY4 · 10% Off Through July 14',
	];
	$opt = get_option( 'osa_promo_july4', [] );

	return wp_parse_args( is_array( $opt ) ? $opt : [], $defaults );
}

/**
 * Promo end as a Unix timestamp (config end parsed in the site timezone).
 *
 * @return int
 */
function osa_promo_end_ts() {
	$end = date_create_immutable( osa_promo_config()['end'], wp_timezone() );

	return $end ? $end->getTimestamp() : 0;
}

/**
 * Is the promo window open right now?
 *
 * @return bool
 */
function osa_promo_active() {
	$c = osa_promo_config();
	if ( empty( $c['enabled'] ) ) {
		return false;
	}
	$start = date_create_immutable( $c['start'], wp_timezone() );
	if ( ! $start ) {
		return false;
	}
	$now = time();

	return $now >= $start->getTimestamp() && $now < osa_promo_end_ts();
}

/**
 * Utility-strip message: coupon copy while the window is open. Front end
 * only — the Customizer keeps showing (and saving) the owner's own text.
 */
add_filter( 'theme_mod_osa_header_strip', function ( $value ) {
	if ( is_admin() || is_customize_preview() || ! osa_promo_active() ) {
		return $value;
	}

	return osa_promo_config()['msg'];
} );

/**
 * Footer · Shop menu: slot an "Independence Day" link under All Inventory
 * while the window is open (no DB menu item — vanishes with the window).
 */
add_filter( 'wp_nav_menu_objects', function ( $items, $args ) {
	if ( 'footer-shop' !== ( $args->theme_location ?? '' ) || ! osa_promo_active() ) {
		return $items;
	}

	$link = (object) [
		'ID'                    => -1,
		'db_id'                 => 0,
		'menu_item_parent'      => 0,
		'object_id'             => 0,
		'object'                => 'custom',
		'type'                  => 'custom',
		'type_label'            => 'Custom Link',
		'title'                 => 'Independence Day',
		'url'                   => home_url( '/' . OSA_PROMO_PAGE_SLUG . '/' ),
		'target'                => '',
		'attr_title'            => '',
		'description'           => '',
		'classes'               => [],
		'xfn'                   => '',
		'current'               => false,
		'current_item_ancestor' => false,
		'current_item_parent'   => false,
	];
	array_splice( $items, min( 1, count( $items ) ), 0, [ $link ] );

	return $items;
}, 10, 2 );

/*
 * Product-card corner badge moved to inc/customizer.php
 * (osa_card_badge_tags(), the osa_card_tags filter). It is now Customizer-
 * driven (Auto follows this promo window; Always show / Hidden override it), so
 * the badge survives even when this seasonal module is removed. In "Auto" mode
 * with the default text it reproduces the old 'Fourth of July' behavior exactly
 * — following osa_promo_active() below.
 */

/**
 * Landing page outside the window: bounce to the shop instead of showing an
 * empty shell (the block renders nothing once the window closes).
 */
add_action( 'template_redirect', function () {
	if ( ! is_page( OSA_PROMO_PAGE_SLUG ) || osa_promo_active() ) {
		return;
	}
	wp_safe_redirect( home_url( '/shop/' ), 302 );
	exit;
} );

/*
 * Robots/meta for the landing page are left to the ffl-core SEO engine
 * (per-page _ffl_seo_* fields on the page post). The page stays INDEXABLE
 * on purpose: /independence-day/ is a recurring seasonal URL — it 302s to
 * the shop between windows (temporary by design; do not "fix" it to 301)
 * and accumulates search equity year over year.
 */

/**
 * LiteSpeed keeps serving the cached teaser/landing markup past the end
 * date; schedule one purge right after the window flips shut.
 */
add_action( 'init', function () {
	if ( ! osa_promo_active() || wp_next_scheduled( 'osa_promo_end_purge' ) ) {
		return;
	}
	wp_schedule_single_event( osa_promo_end_ts() + 120, 'osa_promo_end_purge' );
} );
add_action( 'osa_promo_end_purge', function () {
	do_action( 'litespeed_purge_all' );
} );
