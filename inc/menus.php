<?php
/**
 * OSA — nav menu locations + walkers + seed.
 *
 *  Registering classic nav menu locations brings back the
 *  Appearance → Menus admin page in a block theme. (The page is hidden
 *  by default when no theme location is registered.)
 *
 *  Locations:
 *    - primary           : retail header (centered 6 items)
 *    - footer-shop       : footer column 1
 *    - footer-services   : footer column 2
 *    - footer-account    : footer column 3
 *    - footer-house      : footer column 4
 *
 *  On theme activation we seed a default menu for each location populated
 *  with the placeholder items — so the user can immediately go into the
 *  Menus admin and edit them.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	register_nav_menus( [
		'primary'         => __( 'Primary (header)',  'osa-arsenal' ),
		'footer-shop'     => __( 'Footer · Shop',     'osa-arsenal' ),
		'footer-services' => __( 'Footer · Services', 'osa-arsenal' ),
		'footer-account'  => __( 'Footer · Account',  'osa-arsenal' ),
		'footer-house'    => __( 'Footer · House',    'osa-arsenal' ),
	] );
}, 11 );

/**
 * Walker that emits flat <a class="osa-main-nav__link is-active?"> — no ul/li.
 * Matches the existing CSS for the centered brutalist header nav.
 */
class OSA_Header_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes    = [ 'osa-main-nav__link' ];
		$is_current = in_array( 'current-menu-item', (array) $item->classes, true );
		if ( $is_current || in_array( 'current-menu-parent', (array) $item->classes, true ) ) {
			$classes[] = 'is-active';
		}
		$output .= sprintf(
			'<a href="%s" class="%s"%s>%s</a>',
			esc_url( $item->url ),
			esc_attr( implode( ' ', $classes ) ),
			$is_current ? ' aria-current="page"' : '',
			esc_html( $item->title )
		);
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * Walker for footer columns — clean <a> inside <li>.
 */
class OSA_Footer_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= sprintf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * Seed default menus on theme activation.
 *
 *  - Only runs if the location currently has no menu assigned.
 *  - Idempotent: if a menu with the canonical slug already exists, reuse it.
 */
add_action( 'after_switch_theme', 'osa_seed_default_menus' );
function osa_seed_default_menus() {
	$locations = (array) get_theme_mod( 'nav_menu_locations', [] );

	$defaults = [
		'primary' => [
			'name'  => 'Primary',
			'items' => [
				[ 'Handguns',   '/product-category/handguns/' ],
				[ 'Long Guns',  '/product-category/long-guns/' ],
				[ 'Receivers',  '/product-category/receivers/' ],
				[ 'Ammunition', '/product-category/ammunition/' ],
				[ 'Optics',     '/product-category/optics/' ],
				[ 'Services',   '/services/' ],
				[ 'CA Guide',   '/ca-guide/' ],
			],
		],
		'footer-shop' => [
			'name'  => 'Footer · Shop',
			'items' => [
				[ 'All Inventory', '/shop/' ],
				[ 'Handguns',      '/product-category/handguns/' ],
				[ 'Long Guns',     '/product-category/long-guns/' ],
				[ 'Receivers',     '/product-category/receivers/' ],
				[ 'Ammunition',    '/product-category/ammunition/' ],
				[ 'Optics',        '/product-category/optics/' ],
			],
		],
		'footer-services' => [
			'name'  => 'Footer · Services',
			'items' => [
				[ 'FSC testing',        '/services/#fsc' ],
				[ 'Incoming transfers', '/services/#transfers' ],
				[ 'Layaway',            '/services/#layaway' ],
				[ 'Price match',        '/services/#price-match' ],
				[ 'Training',           '/services/#training' ],
			],
		],
		'footer-account' => [
			'name'  => 'Footer · Support',
			'items' => [
				[ 'Buying in California', '/ca-guide/' ],
				[ 'FAQ',                  '/faq/' ],
				[ 'Shipping policy',      '/policies/#shipping' ],
				[ 'Return policy',        '/policies/#returns' ],
				[ 'Contact us',           '/about/#contact' ],
			],
		],
		'footer-house' => [
			'name'  => 'Footer · Company',
			'items' => [
				[ 'About us',         '/about/' ],
				[ 'Membership',       '/membership/' ],
				// Guarded: activation can happen before WooCommerce is installed/active.
				[ 'Sign In',          function_exists( 'wc_get_page_permalink' ) ? ( wc_get_page_permalink( 'myaccount' ) ?: '/my-account/' ) : '/my-account/' ],
				[ 'Terms of service', '/policies/#terms' ],
				[ 'Privacy policy',   '/policies/#privacy' ],
			],
		],
	];

	foreach ( $defaults as $location => $spec ) {
		if ( ! empty( $locations[ $location ] ) ) {
			continue;
		}
		$menu = wp_get_nav_menu_object( $spec['name'] );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $spec['name'] );
			if ( is_wp_error( $menu_id ) ) {
				continue;
			}
			foreach ( $spec['items'] as [ $title, $url ] ) {
				wp_update_nav_menu_item( $menu_id, 0, [
					'menu-item-title'  => $title,
					'menu-item-url'    => esc_url_raw( $url ),
					'menu-item-status' => 'publish',
				] );
			}
		} else {
			$menu_id = (int) $menu->term_id;
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Fallback if the location is unassigned: render hardcoded defaults.
 */
function osa_header_nav_fallback() {
	$items = [
		[ '/product-category/handguns/',   'Handguns' ],
		[ '/product-category/long-guns/',  'Long Guns' ],
		[ '/product-category/receivers/',  'Receivers' ],
		[ '/product-category/ammunition/', 'Ammunition' ],
		[ '/product-category/optics/',     'Optics' ],
		[ '/services/',                    'Services' ],
		[ '/ca-guide/',                    'CA Guide' ],
	];
	echo '<nav id="osa-mobile-nav" class="osa-main-nav" aria-label="Primary">';
	foreach ( $items as [ $href, $label ] ) {
		printf( '<a class="osa-main-nav__link" href="%s">%s</a>', esc_url( $href ), esc_html( $label ) );
	}
	if ( is_user_logged_in() ) {
		$account_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : site_url( '/my-account/' );
		$account_label = __( 'Account', 'osa-arsenal' );
	} else {
		$account_url   = site_url( defined( 'OSA_LOGIN_PATH' ) ? OSA_LOGIN_PATH : '/account/login/' );
		$account_label = __( 'Sign In', 'osa-arsenal' );
	}
	printf(
		'<a class="osa-main-nav__account-link" href="%s">%s</a>',
		esc_url( $account_url ),
		esc_html( $account_label )
	);
	echo '</nav>';
}

/**
 * Footer column fallback. $heading is the rendered <h4>; items list.
 */
function osa_footer_nav_fallback( $items ) {
	echo '<ul class="osa-footer__list">';
	foreach ( $items as [ $href, $label ] ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $href ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Enqueue the mobile-nav drawer toggle JS.
 */
add_action( 'wp_enqueue_scripts', function () {
	$path = OSA_THEME_DIR . '/assets/js/mobile-nav.js';
	wp_enqueue_script(
		'osa-mobile-nav',
		OSA_THEME_URI . '/assets/js/mobile-nav.js',
		[],
		file_exists( $path ) ? (string) filemtime( $path ) : OSA_THEME_VERSION,
		true
	);
}, 25 );
