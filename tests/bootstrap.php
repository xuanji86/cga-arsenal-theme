<?php
/**
 * OSA test harness — minimal WordPress stubs, no WP install required.
 *
 * Each test-*.php runs in its OWN php process (tests/run.php), defines any
 * scenario-specific stubs (e.g. WooCommerce present/absent), requires this
 * bootstrap, then includes the theme file under test and asserts.
 *
 * Deliberately tiny: just enough surface for the files under test. The point
 * is regression coverage for behavior that broke or nearly broke (see git
 * history), not a WP simulation.
 *
 * @package OSA_Arsenal
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'OSA_THEME_DIR', dirname( __DIR__ ) );
define( 'OSA_THEME_URI', 'https://example.test/wp-content/themes/osa-arsenal' );
define( 'OSA_THEME_VERSION', 'test' );
define( 'HOUR_IN_SECONDS', 3600 );

$GLOBALS['osa_hooks']      = [];
$GLOBALS['osa_theme_mods'] = [];
$GLOBALS['osa_options']    = [];
$GLOBALS['osa_transients'] = [];

// ---- Assertion helpers ----------------------------------------------------

function osa_assert( $cond, $msg ) {
	if ( ! $cond ) {
		echo "FAIL: {$msg}\n";
		exit( 1 );
	}
}

function osa_pass( $msg ) {
	echo "PASS: {$msg}\n";
}

// ---- Hooks (captured; fired only by an apply_filters/do_action call) --------

function add_action( $hook, $cb = null, $priority = 10, $args = 1 ) {
	$GLOBALS['osa_hooks'][ $hook ][] = $cb;
}
function add_filter( $hook, $cb = null, $priority = 10, $args = 1 ) {
	$GLOBALS['osa_hooks'][ $hook ][] = $cb;
}
function osa_hook_callbacks( $hook ) {
	return $GLOBALS['osa_hooks'][ $hook ] ?? [];
}
// Registration order, priorities ignored; every callback gets every argument.
function apply_filters( $hook, $value, ...$args ) {
	foreach ( osa_hook_callbacks( $hook ) as $cb ) {
		$value = call_user_func( $cb, $value, ...$args );
	}
	return $value;
}
// Also recorded in $GLOBALS['osa_actions_done'], so a test can see an action
// nothing in the harness listens to (litespeed_purge_all).
function do_action( $hook, ...$args ) {
	$GLOBALS['osa_actions_done'][] = $hook;
	foreach ( osa_hook_callbacks( $hook ) as $cb ) {
		call_user_func( $cb, ...$args );
	}
}
function add_shortcode( $tag, $cb ) {}
function register_nav_menus( $menus ) {}
function register_block_pattern_category( $slug, $args ) {}

// ---- i18n / escaping -------------------------------------------------------

function __( $text, $domain = null ) {
	return $text;
}
function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES );
}
function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES );
}
function esc_attr_e( $text, $domain = null ) {
	echo htmlspecialchars( (string) $text, ENT_QUOTES );
}
function esc_url( $url ) {
	// Recorded so tests can prove a URL was routed through esc_url (the stub
	// passes through; scheme-stripping is core esc_url's job in prod).
	$GLOBALS['osa_esc_url_calls'][] = (string) $url;
	return (string) $url;
}
function esc_url_raw( $url ) {
	// Recorded like esc_url: this is the NON-HTML sanitizer (JSON-LD payloads,
	// DB writes, redirects) — it must not HTML-encode, so a test can only prove
	// the mechanism, not the outcome. Core rejects hostile schemes in prod.
	$GLOBALS['osa_esc_url_raw_calls'][] = (string) $url;
	return (string) $url;
}
function sanitize_file_name( $name ) {
	return preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $name );
}
function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}
function sanitize_text_field( $str ) {
	return trim( (string) $str );
}
function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}
function number_format_i18n( $number ) {
	return number_format( (float) $number );
}

// ---- Options / theme mods / transients -------------------------------------

function get_option( $key, $default = false ) {
	return $GLOBALS['osa_options'][ $key ] ?? $default;
}
function get_theme_mod( $key, $default = false ) {
	return $GLOBALS['osa_theme_mods'][ $key ] ?? $default;
}
function set_theme_mod( $key, $value ) {
	$GLOBALS['osa_theme_mods'][ $key ] = $value;
}
function get_transient( $key ) {
	return array_key_exists( $key, $GLOBALS['osa_transients'] ) ? $GLOBALS['osa_transients'][ $key ] : false;
}
function set_transient( $key, $value, $ttl = 0 ) {
	$GLOBALS['osa_transients'][ $key ] = $value;
	return true;
}
function delete_transient( $key ) {
	unset( $GLOBALS['osa_transients'][ $key ] );
	return true;
}

// ---- Misc WP surface --------------------------------------------------------

function is_admin() {
	return false;
}
function is_user_logged_in() {
	return false;
}
function site_url( $path = '' ) {
	return 'https://example.test' . $path;
}
function home_url( $path = '' ) {
	return 'https://example.test' . $path;
}
function set_url_scheme( $url, $scheme = null ) {
	// Faithful enough for the fixtures: core replaces the scheme (or supplies
	// one for a protocol-relative URL) from is_ssl()/the home URL. The harness
	// has no request, so the scheme comes from the stubbed home_url().
	if ( null === $scheme ) {
		$scheme = parse_url( home_url(), PHP_URL_SCHEME ) ?: 'http';
	}
	return $scheme . '://' . preg_replace( '#^(\w+:)?//#', '', trim( (string) $url ) );
}
function is_wp_error( $thing ) {
	return false;
}
function wp_get_nav_menu_object( $menu ) {
	return false;
}
function wp_create_nav_menu( $name ) {
	static $id = 100;
	return ++$id;
}
function wp_update_nav_menu_item( $menu_id, $item_id, $args ) {
	// Recorded so seed tests can assert on menu contents.
	$GLOBALS['osa_menu_items'][ $menu_id ][] = $args;
	return 1;
}
function wp_nav_menu( $args = [] ) {
	// No menus exist in the harness — behave like WP with an unassigned
	// location: hand off to fallback_cb when it's callable (fallbacks echo).
	$fallback = $args['fallback_cb'] ?? false;
	if ( $fallback && is_callable( $fallback ) ) {
		call_user_func( $fallback, $args );
	}
	return '';
}
function wp_enqueue_script( ...$args ) {}
function wp_enqueue_style( ...$args ) {}
function wp_localize_script( ...$args ) {}

class Walker_Nav_Menu {}
