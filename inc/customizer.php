<?php
/**
 * OSA — Customizer panel.
 *
 * Registering at least one Customizer setting forces the Appearance → Customize
 * link to reappear in a block theme (WP hides it otherwise). We expose the
 * highest-value editable copy:
 *   - Header utility strip text
 *   - Hero headline + subheadline (homepage)
 *   - Footer tagline
 *
 * Each field is round-tripped through get_theme_mod() with a sensible default,
 * so removing the override falls back to the hardcoded design copy.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

const OSA_HERO_HEADLINE_DEFAULT   = 'Stocked for California.';
const OSA_HERO_SUBHEAD_DEFAULT    = 'Roster-approved handguns, compliant rifles, and ammunition you can actually take home — on the shelf at 401 S Lincoln Ave. We run the DROS, proctor the FSC, and track the ten-day wait, so nothing surprises you at the counter.';
const OSA_HEADER_STRIP_DEFAULT    = 'Open today 11am–7pm · 401 S Lincoln Ave, Suite L · Corona, CA 92882';
const OSA_HEADER_STRIP_LINKS_DEFAULT = "(951) 547-4790 | tel:+19515474790\nBuying in California | /ca-guide/\nServices | /services/\nMembers | /membership/";
const OSA_FOOTER_TAGLINE_DEFAULT  = "Corona's local gun store — firearms, ammunition, training & FFL transfers.";
const OSA_FOOTER_LEGAL_DEFAULT    = "California Guns and Ammo\n401 S Lincoln Ave, Suite L, Corona, CA 92882\n(951) 547-4790 · info@caligunsandammo.com\nFFL 9-33-065-01-8G-09316 · CA DOJ CFD# 24307\nCA COE / ammunition vendor licence no. TBD";
const OSA_FOOTER_HEADINGS_DEFAULT = "Shop\nServices\nSupport\nCompany";
const OSA_FOOTER_COPYRIGHT_DEFAULT  = '© 2026 California Guns and Ammo. All rights reserved.';
const OSA_FOOTER_COMPLIANCE_DEFAULT = 'Must be 21+ to purchase a handgun · 18+ for long guns where permitted · All transfers through a licensed FFL';

const OSA_MARQUEE_DEFAULT_ITEMS   = "DROS $37.19 · ten-day wait\nFSC testing $26 · walk-in\nIncoming FFL transfers $75 · DROS included";

const OSA_FLOOR_HEADING_DEFAULT   = 'On the shelf this week';
const OSA_FLOOR_CTA_DEFAULT       = 'View all';

const OSA_CARD_BADGE_MODE_DEFAULT = 'auto';           // auto | on | off
const OSA_CARD_BADGE_TEXT_DEFAULT = 'Fourth of July'; // historical promo copy

// Model landing pages (/firearms/*) — first batch per the 2026-07 model-
// landing spec §8. One "Label|URL|ImageURL?|FilterSlugs?" per line; feeds the
// osa/model-links block (homepage cards + shop/landing chips) and
// osa/model-page-link (the product-page backlink). Labels are the model words
// on purpose: they double as SEO anchor text everywhere they are rendered.
//
// The 4th field is a CSV of pa_model term slugs (copied verbatim from the
// model-landing grid lines — never guessed from the display name). It powers
// the hub cards' linkTo=filter jump, rendered as array-style params
// /shop/?filter_model[]=a&filter_model[]=b (CSV would collide with WC core
// layered-nav — see render.php BUG-001 note), and it is what lets a PRODUCT
// page find its own landing (osa_model_link_for_product()). These
// defaults ship empty images (Label|URL||Slugs) so the slugs land in position
// 4 while the cards' Media-Library images stay owner-managed. Landing surfaces
// (homepage strip, shop chips, landing tails) ignore the slugs — only the
// hub block instance opts in with linkTo=filter (spec §B, B-plan).
// CGA has no shop-by-model hub yet (OSA's /firearms/ layer). Empty default =
// model-links / model-page-link blocks render nothing until rows are added.
const OSA_MODEL_LINKS_DEFAULT = '';

const OSA_DEALER_DROP_LABEL_DEFAULT   = 'Drop · This Thursday 09:00 CT';
const OSA_DEALER_DROP_HEADING_DEFAULT = 'Next Drop';
const OSA_DEALER_DROP_BODY_DEFAULT    = 'Dealer preview opens 24 hrs before public release. Trade pricing applied automatically.';
const OSA_DEALER_DROP_CTA_DEFAULT     = 'Preview Now →';

const OSA_STORY_LABEL_DEFAULT       = "Owner's Note";
const OSA_STORY_QUOTE_DEFAULT       = "Every item passes a bench inspection before it touches the catalog or shopfloor. If it isn't up to our standard, we don't list it.";
const OSA_STORY_ATTRIBUTION_DEFAULT = '— Shaun. G';

const OSA_AGE_GATE_EYEBROW_DEFAULT = 'Age Verification';
const OSA_AGE_GATE_TITLE_DEFAULT   = 'Are you 21 or older?';
const OSA_AGE_GATE_BODY_DEFAULT    = 'California Guns and Ammo sells handguns to buyers 21 and older, and long guns to buyers 18 and older where permitted. Federal and California law apply. By continuing, you confirm you meet the minimum age in your jurisdiction.';
const OSA_AGE_GATE_ENTER_DEFAULT   = 'Enter Store';
const OSA_AGE_GATE_LEAVE_DEFAULT   = 'Leave';
const OSA_AGE_GATE_LEAVE_URL_DEFAULT = 'https://www.google.com/';

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_panel( 'osa_site', [
		'title'       => __( 'CGA · Site Text & Chrome', 'osa-arsenal' ),
		'description' => __( 'Site-wide chrome and text: header, utility strip, store facts, shop cards, age gate. Homepage sections live in the CGA · Homepage panel.', 'osa-arsenal' ),
		'priority'    => 30,
	] );

	// --- Header ---
	$wp_customize->add_section( 'osa_header_section', [
		'title' => __( 'Header', 'osa-arsenal' ),
		'panel' => 'osa_site',
	] );

	$wp_customize->add_setting( 'osa_header_strip', [
		'default'           => OSA_HEADER_STRIP_DEFAULT,
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_header_strip', [
		'label'       => __( 'Utility strip text', 'osa-arsenal' ),
		'description' => __( 'Thin gray strip above the main header.', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'text',
	] );

	$wp_customize->add_setting( 'osa_header_strip_enabled', [
		'default'           => true,
		'sanitize_callback' => static function ( $v ) { return (bool) $v; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_header_strip_enabled', [
		'label'       => __( 'Show utility strip', 'osa-arsenal' ),
		'description' => __( 'Hide the thin strip entirely (message + links).', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'osa_header_strip_links', [
		'default'           => OSA_HEADER_STRIP_LINKS_DEFAULT,
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_header_strip_links', [
		'label'       => __( 'Utility strip links (one per line)', 'osa-arsenal' ),
		'description' => __( 'Format: Label | URL — add a third field "external" to open in a new tab with a ↗ arrow. Example: Dealers | /dealer/ | external', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'textarea',
	] );

	$wp_customize->add_setting( 'osa_sticky_header', [
		'default'           => false,
		'sanitize_callback' => static function ( $v ) { return (bool) $v; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_sticky_header', [
		'label'       => __( 'Sticky header', 'osa-arsenal' ),
		'description' => __( 'Keep the header pinned to the top of the viewport when the page scrolls. It compacts (hides the strip) once you scroll.', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'osa_back_to_top', [
		'default'           => true,
		'sanitize_callback' => static function ( $v ) { return (bool) $v; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_back_to_top', [
		'label'       => __( 'Back-to-top button', 'osa-arsenal' ),
		'description' => __( 'Show a button that scrolls to the top after the visitor scrolls down.', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'osa_header_height', [
		'default'           => 'normal',
		'sanitize_callback' => static function ( $v ) { return in_array( $v, [ 'compact', 'normal', 'tall' ], true ) ? $v : 'normal'; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_header_height', [
		'label'   => __( 'Header height', 'osa-arsenal' ),
		'section' => 'osa_header_section',
		'type'    => 'select',
		'choices' => [
			'compact' => __( 'Compact', 'osa-arsenal' ),
			'normal'  => __( 'Normal', 'osa-arsenal' ),
			'tall'    => __( 'Tall', 'osa-arsenal' ),
		],
	] );

	$wp_customize->add_setting( 'osa_logo_height', [
		'default'           => 56,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_logo_height', [
		'label'       => __( 'Logo height (px)', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 24, 'max' => 120, 'step' => 1 ],
	] );

	$wp_customize->add_setting( 'osa_nav_align', [
		'default'           => 'center',
		'sanitize_callback' => static function ( $v ) { return in_array( $v, [ 'left', 'center', 'right' ], true ) ? $v : 'center'; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_nav_align', [
		'label'   => __( 'Navigation alignment', 'osa-arsenal' ),
		'section' => 'osa_header_section',
		'type'    => 'select',
		'choices' => [
			'left'   => __( 'Left', 'osa-arsenal' ),
			'center' => __( 'Center', 'osa-arsenal' ),
			'right'  => __( 'Right', 'osa-arsenal' ),
		],
	] );

	foreach ( [
		'osa_show_search'  => __( 'Show search icon', 'osa-arsenal' ),
		'osa_show_account' => __( 'Show account icon', 'osa-arsenal' ),
		'osa_show_cart'    => __( 'Show cart icon', 'osa-arsenal' ),
	] as $key => $label ) {
		$wp_customize->add_setting( $key, [
			'default'           => true,
			'sanitize_callback' => static function ( $v ) { return (bool) $v; },
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( $key, [
			'label'   => $label,
			'section' => 'osa_header_section',
			'type'    => 'checkbox',
		] );
	}

	$wp_customize->add_setting( 'osa_dark_header', [
		'default'           => false,
		'sanitize_callback' => static function ( $v ) { return (bool) $v; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_dark_header', [
		'label'       => __( 'Dark header', 'osa-arsenal' ),
		'description' => __( 'Dark main row with light nav/icons; the logo switches to a light variant.', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'osa_header_logo', [
		'default'           => 'custom',
		'sanitize_callback' => static function ( $v ) { return in_array( $v, [ 'custom', 'inline', 'stacked', 'mark', 'banner' ], true ) ? $v : 'custom'; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_header_logo', [
		'label'       => __( 'Header logo', 'osa-arsenal' ),
		'description' => __( 'Use the WordPress Custom Logo, or an OSA logo lockup (light/dark picked automatically).', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'select',
		'choices'     => [
			'custom'  => __( 'WordPress Custom Logo', 'osa-arsenal' ),
			'inline'  => __( 'CGA — Inline lockup', 'osa-arsenal' ),
			'stacked' => __( 'CGA — Stacked lockup', 'osa-arsenal' ),
			'banner'  => __( 'CGA — Banner lockup', 'osa-arsenal' ),
			'mark'    => __( 'CGA — Mark only', 'osa-arsenal' ),
		],
	] );

	$wp_customize->add_setting( 'osa_nav_font_size', [
		'default'           => 12,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_nav_font_size', [
		'label'       => __( 'Nav font size (px)', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 9, 'max' => 20, 'step' => 1 ],
	] );

	$wp_customize->add_setting( 'osa_nav_gap', [
		'default'           => 20,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_nav_gap', [
		'label'       => __( 'Nav item spacing (px)', 'osa-arsenal' ),
		'section'     => 'osa_header_section',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 8, 'max' => 48, 'step' => 1 ],
	] );

	// Footer content (tagline/legal/headings/copyright/compliance) is now edited
	// directly in the Site Editor footer template part (round 2a blockify), so
	// the former "Footer" Customizer section was removed.

	// --- Shop / Catalog ---
	$wp_customize->add_section( 'osa_shop_section', [
		'title'       => __( 'Shop / Catalog', 'osa-arsenal' ),
		'description' => __( 'Products per row and rows per page are set under WooCommerce → Product Catalog. This section controls the card hover effect and the product-card corner badge.', 'osa-arsenal' ),
		'panel'       => 'osa_site',
	] );

	// Catalog columns ("Products per row") and rows ("Rows per page") are
	// WooCommerce-native settings (Customize → WooCommerce → Product Catalog).
	// The theme honors them directly (inc/shop.php), so OSA does not duplicate
	// them here — this section only carries the card hover effect.

	$wp_customize->add_setting( 'osa_card_hover', [
		'default'           => 'zoom',
		'sanitize_callback' => static function ( $v ) { return in_array( $v, [ 'none', 'zoom', 'lift' ], true ) ? $v : 'zoom'; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_card_hover', [
		'label'   => __( 'Card hover effect', 'osa-arsenal' ),
		'section' => 'osa_shop_section',
		'type'    => 'select',
		'choices' => [
			'none' => __( 'None', 'osa-arsenal' ),
			'zoom' => __( 'Image zoom', 'osa-arsenal' ),
			'lift' => __( 'Lift', 'osa-arsenal' ),
		],
	] );

	// Product-card corner badge (top-right). Mode switch + text; the render
	// lives in osa_card_badge_tags() below (osa_card_tags filter).
	$wp_customize->add_setting( 'osa_card_badge_mode', [
		'default'           => OSA_CARD_BADGE_MODE_DEFAULT,
		'sanitize_callback' => static function ( $v ) { return in_array( $v, [ 'auto', 'on', 'off' ], true ) ? $v : OSA_CARD_BADGE_MODE_DEFAULT; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_card_badge_mode', [
		'label'       => __( 'Product card badge', 'osa-arsenal' ),
		'description' => __( 'Corner badge on product cards. Auto follows the seasonal promo window; Always show ignores it; Hidden turns it off. Out-of-stock cards always keep their corner for the "Sold" mark.', 'osa-arsenal' ),
		'section'     => 'osa_shop_section',
		'type'        => 'select',
		'choices'     => [
			'auto' => __( 'Auto — follow promo window', 'osa-arsenal' ),
			'on'   => __( 'Always show', 'osa-arsenal' ),
			'off'  => __( 'Hidden', 'osa-arsenal' ),
		],
	] );

	$wp_customize->add_setting( 'osa_card_badge_text', [
		'default'           => OSA_CARD_BADGE_TEXT_DEFAULT,
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_card_badge_text', [
		'label'       => __( 'Product card badge text', 'osa-arsenal' ),
		'description' => __( 'Text shown in the corner badge (used by Auto and Always show).', 'osa-arsenal' ),
		'section'     => 'osa_shop_section',
		'type'        => 'text',
	] );

	// --- Age Gate ---
	$wp_customize->add_section( 'osa_age_gate_section', [
		'title'       => __( 'Age Gate', 'osa-arsenal' ),
		'panel'       => 'osa_site',
		'description' => __( 'First-visit age-verification modal. Once a visitor confirms, a 30-day cookie + localStorage entry suppresses the modal on subsequent loads.', 'osa-arsenal' ),
	] );

	$wp_customize->add_setting( 'osa_age_gate_enabled', [
		'default'           => true,
		'sanitize_callback' => static function ( $v ) { return (bool) $v; },
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'osa_age_gate_enabled', [
		'label'       => __( 'Show age-gate modal', 'osa-arsenal' ),
		'description' => __( 'Off = no modal anywhere; existing visitor cookies are ignored.', 'osa-arsenal' ),
		'section'     => 'osa_age_gate_section',
		'type'        => 'checkbox',
	] );

	foreach ( [
		'osa_age_gate_eyebrow'   => [ __( 'Eyebrow (small caps line)', 'osa-arsenal' ), OSA_AGE_GATE_EYEBROW_DEFAULT,   'text' ],
		'osa_age_gate_title'     => [ __( 'Heading', 'osa-arsenal' ),                   OSA_AGE_GATE_TITLE_DEFAULT,     'text' ],
		'osa_age_gate_body'      => [ __( 'Body copy', 'osa-arsenal' ),                 OSA_AGE_GATE_BODY_DEFAULT,      'textarea' ],
		'osa_age_gate_enter'     => [ __( 'Enter button label', 'osa-arsenal' ),        OSA_AGE_GATE_ENTER_DEFAULT,     'text' ],
		'osa_age_gate_leave'     => [ __( 'Leave button label', 'osa-arsenal' ),        OSA_AGE_GATE_LEAVE_DEFAULT,     'text' ],
		'osa_age_gate_leave_url' => [ __( 'Leave button URL', 'osa-arsenal' ),          OSA_AGE_GATE_LEAVE_URL_DEFAULT, 'url' ],
	] as $key => $spec ) {
		$wp_customize->add_setting( $key, [
			'default'           => $spec[1],
			'sanitize_callback' => 'url' === $spec[2] ? 'esc_url_raw' : ( 'textarea' === $spec[2] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( $key, [
			'label'   => $spec[0],
			'section' => 'osa_age_gate_section',
			'type'    => $spec[2],
		] );
	}

} );

/**
 * Real-time count of IN-STOCK, published products ("available lots").
 *
 * Counts only products whose stock_status is 'instock', so the homepage
 * "View All {count} Lots" button reflects actual available inventory rather
 * than every published listing (a published-but-sold/out-of-stock product no
 * longer counts). Falls back to the published-post count if WooCommerce is
 * inactive. Cached in a transient (the query pulls every in-stock ID, which
 * grows with the catalog) and per request; invalidated on product changes
 * below, with a 1h TTL as a safety net.
 */
function osa_published_product_count() {
	static $count = null;
	if ( null !== $count ) {
		return $count;
	}
	$cached = get_transient( 'osa_instock_product_count' );
	if ( false !== $cached ) {
		$count = (int) $cached;
		return $count;
	}
	if ( function_exists( 'wc_get_products' ) ) {
		$ids   = wc_get_products( array(
			'status'       => 'publish',
			'stock_status' => 'instock',
			'limit'        => -1,
			'return'       => 'ids',
		) );
		$count = is_array( $ids ) ? count( $ids ) : 0;
	} else {
		$c     = wp_count_posts( 'product' );
		$count = (int) ( $c->publish ?? 0 );
	}
	set_transient( 'osa_instock_product_count', $count, HOUR_IN_SECONDS );
	return $count;
}

/**
 * Drop the cached in-stock count whenever a product's publish/stock state
 * can change.
 */
function osa_flush_product_count() {
	delete_transient( 'osa_instock_product_count' );
}
add_action( 'save_post_product', 'osa_flush_product_count' );
add_action( 'woocommerce_product_set_stock_status', 'osa_flush_product_count' );
add_action( 'deleted_post', function ( $post_id ) {
	if ( 'product' === get_post_type( $post_id ) ) {
		osa_flush_product_count();
	}
} );

/**
 * Substitute {count} in a label with the live product count.
 */
function osa_format_label_with_count( $label ) {
	return str_replace(
		[ '{count}', '{n}' ],
		[ number_format_i18n( osa_published_product_count() ), number_format_i18n( osa_published_product_count() ) ],
		(string) $label
	);
}

/**
 * Marquee items resolver — Customizer textarea split by newlines.
 *
 * @return string[]
 */
function osa_marquee_items() {
	$raw = osa_text( 'osa_marquee_items', OSA_MARQUEE_DEFAULT_ITEMS );
	$items = array_values( array_filter( array_map( 'trim', preg_split( "/\r?\n/", (string) $raw ) ), 'strlen' ) );
	return $items;
}

/**
 * Header utility-strip links, parsed from the "Label | URL | external" textarea.
 *
 * @return array<int,array{label:string,url:string,external:bool}>
 */
function osa_header_strip_links() {
	$raw   = osa_text( 'osa_header_strip_links', OSA_HEADER_STRIP_LINKS_DEFAULT );
	$out   = [];
	foreach ( preg_split( "/\r?\n/", (string) $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', (string) $line ) );
		$label = $parts[0] ?? '';
		$url   = $parts[1] ?? '';
		if ( '' === $label || '' === $url ) {
			continue;
		}
		$flag = isset( $parts[2] ) ? strtolower( $parts[2] ) : '';
		$out[] = [
			'label'    => $label,
			'url'      => $url,
			'external' => in_array( $flag, [ 'external', 'new', '↗' ], true ),
		];
	}
	return $out;
}

/**
 * Model landing links, parsed from the "Label | URL | ImageURL? | FilterSlugs?"
 * textarea (Customize → OSA · Model Pages). Lines missing a label or URL are
 * skipped. Consumed by blocks/model-links/render.php and
 * blocks/model-page-link/render.php.
 *
 * Fields are positional: a row with slugs but no image is stored as
 * `Label|URL||slugs` (empty 3rd field). Legacy 2- and 3-field rows parse
 * unchanged — the extra field defaults to '' — so nothing that pre-dates the
 * filter feature changes behavior.
 *
 * @return array<int,array{label:string,url:string,img:string,slugs:string}>
 */
function osa_model_links_items() {
	$raw = osa_text( 'osa_model_links', OSA_MODEL_LINKS_DEFAULT );
	$out = [];
	foreach ( preg_split( "/\r?\n/", (string) $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', (string) $line ) );
		$label = $parts[0] ?? '';
		$url   = $parts[1] ?? '';
		if ( '' === $label || '' === $url ) {
			continue;
		}
		$out[] = [
			'label' => $label,
			'url'   => $url,
			'img'   => $parts[2] ?? '',
			'slugs' => $parts[3] ?? '',
		];
	}
	return $out;
}

/**
 * The pa_model term slugs a row carries, normalized: trimmed, lower-cased,
 * empties dropped.
 *
 * ONE reader for the 4th field, used by both consumers — the /firearms/ hub
 * card's `filter_model[]=` jump (blocks/model-links/render.php) and the product
 * backlink's row lookup (osa_model_link_for_product()). They were briefly two
 * implementations with two normalizations: the backlink lower-cased, the hub
 * link did not, so an owner who typed a slug in capitals would get a working
 * product backlink and a hub filter that silently returned nothing (term slugs
 * are lower-case). The shipped rows are all lower-case, so it never fired —
 * which is exactly how two copies drift apart unnoticed.
 *
 * @param array $item Row from osa_model_links_items().
 * @return string[]
 */
function osa_model_link_row_slugs( array $item ) {
	$out = [];
	foreach ( explode( ',', (string) ( $item['slugs'] ?? '' ) ) as $slug ) {
		$slug = strtolower( trim( $slug ) );
		if ( '' !== $slug ) {
			$out[] = $slug;
		}
	}
	return $out;
}

/**
 * A product's pa_model term slugs, lower-cased and de-padded.
 *
 * Slug-level sibling of osa_product_term() (inc/helpers.php, which returns the
 * display NAME of the first term). It lives here, next to the rows it is
 * matched against, so the whole model-links resolution story is one file.
 *
 * @param int $product_id Product ID; 0 = current post.
 * @return string[]
 */
function osa_model_link_product_slugs( $product_id = 0 ) {
	$product_id = $product_id ?: get_the_ID();
	if ( ! $product_id || ! function_exists( 'wc_get_product_terms' ) ) {
		return [];
	}
	$slugs = wc_get_product_terms( $product_id, 'pa_model', [ 'fields' => 'slugs' ] );
	if ( ! is_array( $slugs ) ) {
		return [];
	}
	$out = [];
	foreach ( $slugs as $slug ) {
		$slug = strtolower( trim( (string) $slug ) );
		if ( '' !== $slug ) {
			$out[] = $slug;
		}
	}
	return $out;
}

/**
 * The model-links row that owns a product, matched on pa_model term slugs
 * against the row's 4th field — the same slug set the /firearms/ hub card uses
 * to filter the shop, so a product and its hub card can never disagree about
 * which landing page a model belongs to.
 *
 * First matching row wins: with several pa_model terms on one product the
 * choice follows the owner's Customizer row order, which is deterministic and
 * editable without code. A row carrying NO slugs matches nothing — otherwise
 * the first slug-less row would silently swallow every product on the site.
 *
 * @param int $product_id Product ID; 0 = current post.
 * @return array|null Row array from osa_model_links_items(), or null.
 */
function osa_model_link_for_product( $product_id = 0 ) {
	$slugs = osa_model_link_product_slugs( $product_id );
	if ( ! $slugs ) {
		return null;
	}
	foreach ( osa_model_links_items() as $item ) {
		if ( array_intersect( osa_model_link_row_slugs( $item ), $slugs ) ) {
			return $item;
		}
	}
	return null;
}

/**
 * Category nouns a model label may end in. NOT a slug table and not a taxonomy:
 * it is English grammar for one sentence. Labels are written for standalone use
 * ("SKS Rifles" on a chip), but the product backlink puts the label inside a
 * sentence that already supplies the noun — "More SKS Rifles rifles in stock"
 * is what you get without this, and "More Luger P08 rifles in stock" is what you
 * get if the noun is hardcoded. So the noun comes off the label and goes back in
 * lower-case, and a label that ends in no noun at all keeps every word.
 *
 * ⚠ This is an ENGLISH GRAMMAR TABLE, not a translatable resource: the words are
 * matched against author-written labels, and the sentence they feed
 * ("More %1$s %2$s in stock") only parses in a language that orders and inflects
 * this way. Localizing the theme means redoing the sentence and this split
 * together, not translating these five strings.
 */
const OSA_MODEL_LABEL_NOUNS = [ 'rifles', 'pistols', 'carbines', 'revolvers', 'shotguns' ];

/**
 * Split a model label into its model word and its category noun.
 *
 *   "Mosin-Nagant Rifles"          → [ 'Mosin-Nagant', 'rifles' ]
 *   "Luger P08 Pistols"            → [ 'Luger P08', 'pistols' ]
 *   "West German SIG P226 & P228"  → [ 'West German SIG P226 & P228', '' ]
 *
 * @param string $label Row label.
 * @return array{0:string,1:string} [ model word, lower-case noun or '' ]
 */
function osa_model_label_parts( $label ) {
	$label = trim( (string) $label );
	$words = preg_split( '/\s+/', $label );
	if ( count( $words ) < 2 ) {
		return [ $label, '' ];
	}
	$last = strtolower( (string) end( $words ) );
	if ( ! in_array( $last, OSA_MODEL_LABEL_NOUNS, true ) ) {
		return [ $label, '' ];
	}
	array_pop( $words );
	return [ implode( ' ', $words ), $last ];
}

/**
 * Footer column headings (one per line), in column order.
 *
 * @return string[]
 */
function osa_footer_col_headings() {
	$raw = osa_text( 'osa_footer_col_headings', OSA_FOOTER_HEADINGS_DEFAULT );
	return array_values( array_filter( array_map( 'trim', preg_split( "/\r?\n/", (string) $raw ) ), 'strlen' ) );
}

/**
 * Public read accessors with defaults.
 */
function osa_text( $key, $default = '' ) {
	return get_theme_mod( $key, $default );
}

/**
 * Add a body class when the sticky-header Customizer toggle is on so
 * CSS can pin the header.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( get_theme_mod( 'osa_sticky_header' ) ) {
		$classes[] = 'osa-has-sticky-header';
	}
	return $classes;
} );

/**
 * Header layout body classes (height + nav alignment) and the logo-height CSS
 * variable, all driven from the "OSA · Header" Customizer controls.
 */
add_filter( 'body_class', function ( $classes ) {
	$height = get_theme_mod( 'osa_header_height', 'normal' );
	$align  = get_theme_mod( 'osa_nav_align', 'center' );
	$classes[] = 'osa-header-h-' . sanitize_html_class( in_array( $height, [ 'compact', 'normal', 'tall' ], true ) ? $height : 'normal' );
	$classes[] = 'osa-nav-' . sanitize_html_class( in_array( $align, [ 'left', 'center', 'right' ], true ) ? $align : 'center' );
	if ( get_theme_mod( 'osa_dark_header' ) ) {
		$classes[] = 'osa-dark-header';
	}
	return $classes;
} );

add_action( 'wp_head', function () {
	$vars = [];
	$logo = (int) get_theme_mod( 'osa_logo_height', 56 );
	$fs   = (int) get_theme_mod( 'osa_nav_font_size', 12 );
	$gap  = (int) get_theme_mod( 'osa_nav_gap', 20 );
	if ( $logo > 0 && 56 !== $logo ) {
		$vars[] = '--osa-logo-h:' . $logo . 'px';
	}
	if ( $fs > 0 && 12 !== $fs ) {
		$vars[] = '--osa-nav-fs:' . $fs . 'px';
	}
	if ( $gap >= 0 && 20 !== $gap ) {
		$vars[] = '--osa-nav-gap:' . $gap . 'px';
	}
	if ( $vars ) {
		printf( '<style id="osa-header-vars">:root{%s}</style>', esc_html( implode( ';', $vars ) ) );
	}
}, 20 );

/**
 * Card hover effect (Customizer → Shop / Catalog): emit the body class the CSS
 * hooks. Only on the catalog archives. Column count is WooCommerce-native and
 * handled in inc/shop.php.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return $classes;
	}
	$hover = sanitize_html_class( (string) get_theme_mod( 'osa_card_hover', 'zoom' ) );
	$classes[] = 'osa-card-hover-' . ( '' !== $hover ? $hover : 'zoom' );
	return $classes;
} );

/**
 * Product-card corner badge (Customizer → Shop / Catalog).
 *
 * Injects the badge text into the single top-right corner slot via the
 * `osa_card_tags` filter (rendered in inc/helpers.php). Three modes:
 *
 *   auto — follow the seasonal promo window (osa_promo_active()). This is the
 *          historical behavior: the badge shows only while the July-4 window is
 *          open. If the seasonal promo module (inc/promo.php) has been removed,
 *          osa_promo_active() is undefined and auto degrades to hidden.
 *   on   — always show, independent of any promo window.
 *   off  — never show.
 *
 * The in-stock-only rule is preserved in every mode: an out-of-stock card keeps
 * its top-right corner for the "Sold" marker, so the badge never collides.
 *
 * NOTE: this replaces the hardcoded 'Fourth of July' filter that used to live in
 * inc/promo.php — the badge is now Customizer-driven and lives here (permanent),
 * so "Always show" keeps working even between seasonal windows.
 *
 * @param array       $tags    Existing corner tags for this card.
 * @param int         $post_id Product post ID (unused; kept for the filter signature).
 * @param mixed|null  $product WC_Product or null when there is no product context.
 * @return array
 */
function osa_card_badge_tags( $tags, $post_id = 0, $product = null ) {
	$mode = get_theme_mod( 'osa_card_badge_mode', OSA_CARD_BADGE_MODE_DEFAULT );
	if ( ! in_array( $mode, [ 'auto', 'on', 'off' ], true ) ) {
		$mode = OSA_CARD_BADGE_MODE_DEFAULT; // Normalize any stray value to auto.
	}

	if ( 'off' === $mode ) {
		return $tags;
	}
	// Auto follows the promo window; without the seasonal module it hides.
	if ( 'auto' === $mode && ( ! function_exists( 'osa_promo_active' ) || ! osa_promo_active() ) ) {
		return $tags;
	}

	// In-stock only — never claim the corner an out-of-stock card needs for "Sold".
	if ( $product && ! $product->is_in_stock() ) {
		return $tags;
	}

	$text = trim( (string) get_theme_mod( 'osa_card_badge_text', OSA_CARD_BADGE_TEXT_DEFAULT ) );
	if ( '' !== $text ) {
		$tags[] = $text;
	}

	return $tags;
}
add_filter( 'osa_card_tags', 'osa_card_badge_tags', 10, 3 );

/**
 * Re-expose the Customizer under Appearance.
 *
 * WordPress hides the "Customize" link from the Appearance menu for block
 * themes (it points owners at the Site Editor instead). OSA still uses the
 * Customizer for editable text (hero, header strip, shop options, age gate, …),
 * so add the link back — otherwise the "OSA · Text & Headlines" panel is
 * reachable only by typing /wp-admin/customize.php by hand.
 */
add_action( 'admin_menu', function () {
	if ( function_exists( 'wp_is_block_theme' ) && ! wp_is_block_theme() ) {
		return; // classic themes already have the link.
	}
	add_submenu_page(
		'themes.php',
		__( 'Customize', 'osa-arsenal' ),
		__( 'Customize', 'osa-arsenal' ),
		'customize',
		'customize.php'
	);
}, 20 );

/**
 * Live preview: flip the main text settings to postMessage so they update
 * instantly in the preview (no full reload). Runs after the settings register.
 */
add_action( 'customize_register', function ( $wp_customize ) {
	// Exactly the ids bound in assets/js/osa-customize-preview.js — a flipped
	// setting the JS doesn't handle would preview as a silent no-op.
	$live = [
		'osa_header_strip',
		'cga_hero_eyebrow',
		'cga_hero_headline',
		'cga_hero_lede',
		'cga_rail_heading',
		'cga_cats_heading',
		'cga_steps_heading',
		'cga_svc_heading',
		'cga_train_heading',
		'cga_store_heading',
		'cga_band_eyebrow',
		'cga_band_title',
		'cga_band_body',
		'cga_store_blurb',
	];
	foreach ( $live as $id ) {
		$setting = $wp_customize->get_setting( $id );
		if ( $setting ) {
			$setting->transport = 'postMessage';
		}
	}
}, 20 );

add_action( 'customize_preview_init', function () {
	$rel  = '/assets/js/osa-customize-preview.js';
	$path = OSA_THEME_DIR . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'osa-customize-preview',
		OSA_THEME_URI . $rel,
		[ 'customize-preview' ],
		(string) filemtime( $path ),
		true
	);
} );

/**
 * Controls-pane assets for the "Shop by Model" repeater (OSA_Model_Links_Control).
 *
 * Runs only in the Customizer controls frame. wp_enqueue_media() loads the WP
 * Media Library (wp.media) frame used by the row image picker; the script pulls
 * jquery-ui-sortable (drag reorder) and customize-controls (the settings API).
 * All WP-bundled — no external libraries.
 */
add_action( 'customize_controls_enqueue_scripts', function () {
	$js_rel  = '/assets/js/customize-model-links.js';
	$css_rel = '/assets/css/customize-model-links.css';
	$js_path  = OSA_THEME_DIR . $js_rel;
	$css_path = OSA_THEME_DIR . $css_rel;
	if ( ! file_exists( $js_path ) ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'osa-customize-model-links',
		OSA_THEME_URI . $js_rel,
		[ 'jquery', 'jquery-ui-sortable', 'customize-controls' ],
		(string) filemtime( $js_path ),
		true
	);
	wp_localize_script( 'osa-customize-model-links', 'osaModelLinksL10n', [
		'label'            => __( 'Label', 'osa-arsenal' ),
		'url'              => __( 'URL', 'osa-arsenal' ),
		'labelPlaceholder' => __( 'e.g. SKS Rifles', 'osa-arsenal' ),
		'urlPlaceholder'   => '/firearms/model-slug/',
		'filterSlugs'      => __( 'Filter slugs', 'osa-arsenal' ),
		'slugsPlaceholder' => __( 'e.g. p226,p228 — optional, for hub filter jump', 'osa-arsenal' ),
		'selectImage'      => __( 'Select image', 'osa-arsenal' ),
		'changeImage'      => __( 'Change', 'osa-arsenal' ),
		'removeImage'      => __( 'Remove image', 'osa-arsenal' ),
		'remove'           => __( 'Remove model', 'osa-arsenal' ),
		'reorder'          => __( 'Reorder (drag, or focus and use the arrow keys)', 'osa-arsenal' ),
		'mediaTitle'       => __( 'Select model image', 'osa-arsenal' ),
		'mediaButton'      => __( 'Use this image', 'osa-arsenal' ),
	] );

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'osa-customize-model-links',
			OSA_THEME_URI . $css_rel,
			[],
			(string) filemtime( $css_path )
		);
	}
} );
