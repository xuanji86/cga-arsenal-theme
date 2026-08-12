<?php
/**
 * CGA — Customizer wiring for the CGA homepage patterns + shared store facts.
 *
 * Everything the owner can say on the homepage lives here as theme mods with
 * the shipped copy as defaults, grouped under the "CGA · Homepage" panel.
 * "Store facts" (address / hours / phone / email / FFL / CFD) are the single
 * source of truth: patterns read them via {tokens}, page content (About) and
 * the footer read them via shortcodes — edit once in Customize, changes land
 * everywhere, with live preview.
 *
 * Conventions (same as inc/customizer.php): plain get_theme_mod via
 * osa_text(); multi-field cells use one textarea with one field per line.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------- *
 * Defaults — the approved comp copy (UTF-8 literals, not entities).
 * ---------------------------------------------------------------- */

const CGA_FACT_DEFAULTS = [
	'cga_fact_address' => "401 S Lincoln Ave, Suite L\nCorona, CA 92882",
	'cga_fact_hours'   => "Monday – Sunday\n11:00 AM – 7:00 PM",
	'cga_fact_phone'   => '(951) 547-4790',
	'cga_fact_email'   => 'info@gunsammo.club',
	'cga_fact_ffl'     => '9-33-065-01-8G-09316',
	'cga_fact_cfd'     => '24307',
];

const CGA_FOOTER_TOP_DEFAULT   = "Corona's local gun store — firearms, ammunition, training & FFL transfers.\nCalifornia Guns and Ammo";
const CGA_FOOTER_EXTRA_DEFAULT = 'CA COE / ammunition vendor licence no. TBD';

const CGA_HERO_EYEBROW_DEFAULT  = 'Est. 2023 · Corona, California · FFL {ffl} · CFD# {cfd}';
const CGA_HERO_HEADLINE_DEFAULT = "Stocked for\nCalifornia.";
const CGA_HERO_LEDE_DEFAULT     = 'Roster-approved handguns, compliant rifles, and ammunition you can actually take home — on the shelf at 401 S Lincoln Ave. We run the DROS, proctor the FSC, and track the ten-day wait, so nothing surprises you at the counter.';
const CGA_HERO_CTA1_DEFAULT     = 'Shop in-stock firearms | /shop/';
const CGA_HERO_CTA2_DEFAULT     = 'How to buy in California → | /ca-guide/';
const CGA_HERO_STATS_DEFAULT    = "{count} | Items on the shelf\n11–7 | Open every day\n10 days | CA waiting period\n$26 | FSC test, walk-in";

const CGA_RAIL_HEADING_DEFAULT = 'How your order gets to you';
const CGA_RAIL_ASIDE_DEFAULT   = 'Chosen at checkout';
const CGA_RAIL_CELL_DEFAULTS   = [
	"01 / California buyers\nPick up in Corona\nBuy online, finish at the counter. Bring your CA driver's license or ID and your Firearm Safety Certificate; we submit the DROS the same day and the state's ten-day wait starts from that submission.\nDROS $37.19 · dealer fee $ TBD · non-refundable",
	"02 / Out-of-state buyers\nShip to your FFL\nPick your local dealer at checkout and we verify their license against the ATF listing before the firearm leaves the building. Tracking goes to you and to them; the transfer finishes on their counter.\nFlat $35 · Ships in 1–2 business days",
	"03 / In-store services\nAmmo background check & FFL transfer\nEvery ammunition sale includes the CA DOJ eligibility check, run face-to-face at the counter. Bought a firearm elsewhere? We accept incoming FFL transfers — we handle the DROS and hold it through the ten-day wait.\nAmmo check $1 · FFL transfer $75, DROS included",
];

const CGA_CATS_HEADING_DEFAULT  = 'Shop the counter';
const CGA_CATS_ASIDE_DEFAULT    = 'Live inventory from the POS';
const CGA_SHELF_HEADING_DEFAULT = 'On the shelf this week';
const CGA_SHELF_ASIDE_DEFAULT   = 'Updated as the POS receives · View all {count} →';

const CGA_STEPS_HEADING_DEFAULT = 'Buying a firearm in California';
const CGA_STEPS_ASIDE_DEFAULT   = 'Full guide → | /ca-guide/';
const CGA_STEP_DEFAULTS         = [
	"Reserve online\nPay for any in-stock firearm and it comes off the floor under your name the moment the order lands.",
	"Paperwork at the counter\nBring your CA DL or ID, your FSC, and — for handguns — proof of residency. We submit the DROS to CA DOJ that day.",
	"Ten-day wait\nState law, not shop policy. We text you the release date the moment the DROS clears so you don't have to count.",
	"Pick up & demo\nCome back, pass the safe-handling demonstration, and walk out. Your paperwork stays on file with us.",
];

const CGA_SVC_HEADING_DEFAULT = 'At the counter';
const CGA_SVC_ASIDE_DEFAULT   = 'All services & pricing → | /services/';
const CGA_SVC_CELL_DEFAULTS   = [
	"$26 · Walk-in\nFSC test\nTwo attempts included, $1 for the laminate. Fail the first and you retake after 24 hours — no new fee.",
	"$75 · DROS included\nIncoming transfers\nBought elsewhere online? Send the seller our FFL. Once tracking shows delivered, come in for the background check.",
	"20% down\nLayaway\nFirearms only. Twenty percent holds it; the balance is due in full before the DROS is submitted.",
	"Local shops\nPrice match\nShow us a local shop's advertised price on the same item in stock and we'll match it at the counter.",
];

const CGA_TRAIN_HEADING_DEFAULT = 'Training';
const CGA_TRAIN_ASIDE_DEFAULT   = 'Schedule & pricing TBD';
const CGA_TRAIN_CELL_DEFAULTS   = [
	"Firearm safety fundamentals\nFirst-time owners: safe handling, storage, transport under California law, and what the FSC actually tests you on.\nDuration TBD · Price TBD · Next date TBD\nReserve a seat | /services/#training",
	"Shooting techniques\nGrip, stance, sight picture and dry-fire practice for shooters who own the gun and want to actually run it well.\nDuration TBD · Price TBD · Next date TBD\nReserve a seat | /services/#training",
];

const CGA_BAND_EYEBROW_DEFAULT = 'Membership';
const CGA_BAND_TITLE_DEFAULT   = 'Member pricing on every order';
const CGA_BAND_BODY_DEFAULT    = 'Silver takes 3% off everything and Gold takes 5%, applied the moment you sign in — plus free DROS fees, FFL transfers and cleanings sitting as credits on your account.';
const CGA_BAND_CTA_DEFAULT     = 'See membership | /membership/';

const CGA_STORE_HEADING_DEFAULT = 'Visit the shop';
const CGA_STORE_ASIDE_DEFAULT   = 'Open seven days';
const CGA_STORE_BLURB_DEFAULT   = "Established 2022, open on Lincoln Ave since July 4th, 2023. We don't buy firearms from individuals and we don't take trade-ins — what's listed is what's on the shelf.";
const CGA_STORE_CTA_DEFAULT     = 'Get directions | /about/';
const CGA_STORE_MAP_DEFAULT     = "Map embed\n401 S Lincoln Ave, Suite L";

/* ---------------------------------------------------------------- *
 * Read helpers.
 * ---------------------------------------------------------------- */

/**
 * A store fact by short key (address/hours/phone/email/ffl/cfd), raw.
 */
function cga_fact( $key ) {
	$mod = 'cga_fact_' . $key;
	if ( ! array_key_exists( $mod, CGA_FACT_DEFAULTS ) ) {
		return '';
	}
	return (string) osa_text( $mod, CGA_FACT_DEFAULTS[ $mod ] );
}

/**
 * Expand {tokens} in Customizer copy: store facts (single-line forms) plus
 * {count} = live published-product count.
 */
function cga_expand_tokens( $text ) {
	static $map = null;
	if ( null === $map ) {
		$one_line = static function ( $v ) {
			return implode( ', ', array_filter( array_map( 'trim', preg_split( "/\r?\n/", $v ) ), 'strlen' ) );
		};
		$map = [
			'{address}' => $one_line( cga_fact( 'address' ) ),
			'{hours}'   => $one_line( cga_fact( 'hours' ) ),
			'{phone}'   => cga_fact( 'phone' ),
			'{email}'   => cga_fact( 'email' ),
			'{ffl}'     => cga_fact( 'ffl' ),
			'{cfd}'     => cga_fact( 'cfd' ),
			'{count}'   => (string) ( function_exists( 'osa_published_product_count' ) ? osa_published_product_count() : '' ),
		];
	}
	return strtr( (string) $text, $map );
}

/**
 * Customizer text with tokens expanded. NOT escaped — escape at render site.
 */
function cga_text( $key, $default = '' ) {
	return cga_expand_tokens( osa_text( $key, $default ) );
}

/**
 * Multi-line setting → array of trimmed lines, padded to $min entries.
 */
function cga_lines( $key, $default = '', $min = 0 ) {
	$lines = preg_split( "/\r?\n/", cga_text( $key, $default ) );
	$lines = array_map( 'trim', $lines );
	while ( count( $lines ) < $min ) {
		$lines[] = '';
	}
	return $lines;
}

/**
 * "Label | URL" setting → [label, url]. Missing URL falls back to '#'.
 */
function cga_link( $key, $default = '' ) {
	$parts = array_map( 'trim', explode( '|', cga_text( $key, $default ), 2 ) );
	return [ $parts[0] ?? '', ( $parts[1] ?? '' ) !== '' ? $parts[1] : '#' ];
}

/**
 * Render a "value | label" / "field per line" cell body as <br>-joined HTML.
 */
function cga_multiline_html( $text ) {
	$lines = array_filter( array_map( 'trim', preg_split( "/\r?\n/", (string) $text ) ), 'strlen' );
	return implode( '<br />', array_map( 'esc_html', $lines ) );
}

/**
 * The hero / shelf featured product: explicit SKU when set, else the newest
 * in-stock product. Returns WC_Product|null.
 */
function cga_featured_product() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return null;
	}
	$sku = trim( (string) osa_text( 'cga_hero_featured_sku', '' ) );
	if ( '' !== $sku && function_exists( 'wc_get_product_id_by_sku' ) ) {
		$pid = wc_get_product_id_by_sku( $sku );
		if ( $pid ) {
			$product = wc_get_product( $pid );
			if ( $product && 'publish' === $product->get_status() ) {
				return $product;
			}
		}
	}
	$found = wc_get_products( [
		'status'       => 'publish',
		'limit'        => 1,
		'orderby'      => 'date',
		'order'        => 'DESC',
		'stock_status' => 'instock',
	] );
	return $found ? $found[0] : null;
}

/* ---------------------------------------------------------------- *
 * Shortcodes — how PAGE CONTENT and the footer consume store facts.
 * [cga_fact key="phone"]        → escaped fact ('br' joins multiline)
 * [cga_footer_legal]            → the footer legal block, assembled
 * ---------------------------------------------------------------- */

add_shortcode( 'cga_fact', function ( $atts ) {
	$atts = shortcode_atts( [ 'key' => '', 'line' => 0 ], $atts );
	$raw  = cga_fact( $atts['key'] );
	if ( '' === $raw ) {
		return '';
	}
	$line = (int) $atts['line'];
	if ( $line > 0 ) {
		$lines = array_values( array_filter( array_map( 'trim', preg_split( "/\r?\n/", $raw ) ), 'strlen' ) );
		return isset( $lines[ $line - 1 ] ) ? esc_html( $lines[ $line - 1 ] ) : '';
	}
	return cga_multiline_html( $raw );
} );

add_shortcode( 'cga_footer_legal', function () {
	$one_line = implode( ', ', array_filter( array_map( 'trim', preg_split( "/\r?\n/", cga_fact( 'address' ) ) ), 'strlen' ) );
	$lines    = array_filter( array_map( 'trim', preg_split( "/\r?\n/", cga_text( 'cga_footer_legal_top', CGA_FOOTER_TOP_DEFAULT ) ) ), 'strlen' );
	$lines[]  = $one_line;
	$lines[]  = cga_fact( 'phone' ) . ' · ' . cga_fact( 'email' );
	$lines[]  = 'FFL ' . cga_fact( 'ffl' ) . ' · CA DOJ CFD# ' . cga_fact( 'cfd' );
	$extra    = trim( cga_text( 'cga_footer_legal_extra', CGA_FOOTER_EXTRA_DEFAULT ) );
	if ( '' !== $extra ) {
		$lines[] = $extra;
	}
	return '<p class="osa-footer__legal">' . implode( '<br />', array_map( 'esc_html', $lines ) ) . '</p>';
} );

/* ---------------------------------------------------------------- *
 * Customizer registration.
 * ---------------------------------------------------------------- */

add_action( 'customize_register', function ( $wp_customize ) {

	// Small helper mirroring inc/customizer.php's loop idiom.
	$add = static function ( $section, $fields ) use ( $wp_customize ) {
		foreach ( $fields as $key => [ $label, $default, $type ] ) {
			$wp_customize->add_setting( $key, [
				'default'           => $default,
				'sanitize_callback' => 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			] );
			$wp_customize->add_control( $key, [
				'label'   => $label,
				'section' => $section,
				'type'    => $type,
			] );
		}
	};

	/* --- Store facts (site-wide; panel: osa_site) --- */
	$wp_customize->add_section( 'cga_facts_section', [
		'title'       => __( 'Store Facts (site-wide)', 'osa-arsenal' ),
		'description' => __( 'The single source of truth for the store\'s contact + licence facts. Used by the homepage, the footer legal block, and the About page — and available anywhere as {tokens} in these controls or [cga_fact key="…"] in page content.', 'osa-arsenal' ),
		'panel'       => 'osa_site',
		'priority'    => 5,
	] );
	$add( 'cga_facts_section', [
		'cga_fact_address'      => [ __( 'Address (two lines)', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_address'], 'textarea' ],
		'cga_fact_hours'        => [ __( 'Hours (days / times)', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_hours'], 'textarea' ],
		'cga_fact_phone'        => [ __( 'Phone', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_phone'], 'text' ],
		'cga_fact_email'        => [ __( 'Email', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_email'], 'text' ],
		'cga_fact_ffl'          => [ __( 'FFL number', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_ffl'], 'text' ],
		'cga_fact_cfd'          => [ __( 'CA DOJ CFD number', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_cfd'], 'text' ],
		'cga_footer_legal_top'  => [ __( 'Footer legal — opening lines', 'osa-arsenal' ), CGA_FOOTER_TOP_DEFAULT, 'textarea' ],
		'cga_footer_legal_extra' => [ __( 'Footer legal — extra line (COE etc.)', 'osa-arsenal' ), CGA_FOOTER_EXTRA_DEFAULT, 'text' ],
	] );

	/* --- Homepage panel --- */
	$wp_customize->add_panel( 'cga_home', [
		'title'       => __( 'CGA · Homepage', 'osa-arsenal' ),
		'description' => __( 'Every text block on the homepage, section by section. Multi-field cells use one field per line, in the order shown by the current text. {tokens} available: {address} {hours} {phone} {email} {ffl} {cfd} {count}.', 'osa-arsenal' ),
		'priority'    => 25,
	] );

	$wp_customize->add_section( 'cga_hero_section', [ 'title' => __( '1 · Hero', 'osa-arsenal' ), 'panel' => 'cga_home' ] );
	$add( 'cga_hero_section', [
		'cga_hero_eyebrow'      => [ __( 'Eyebrow', 'osa-arsenal' ), CGA_HERO_EYEBROW_DEFAULT, 'text' ],
		'cga_hero_headline'     => [ __( 'Headline (line break = new line)', 'osa-arsenal' ), CGA_HERO_HEADLINE_DEFAULT, 'textarea' ],
		'cga_hero_lede'         => [ __( 'Lede', 'osa-arsenal' ), CGA_HERO_LEDE_DEFAULT, 'textarea' ],
		'cga_hero_cta1'         => [ __( 'Primary button (Label | URL)', 'osa-arsenal' ), CGA_HERO_CTA1_DEFAULT, 'text' ],
		'cga_hero_cta2'         => [ __( 'Secondary button (Label | URL)', 'osa-arsenal' ), CGA_HERO_CTA2_DEFAULT, 'text' ],
		'cga_hero_stats'        => [ __( 'Stats (one per line: Value | Label)', 'osa-arsenal' ), CGA_HERO_STATS_DEFAULT, 'textarea' ],
		'cga_hero_featured_sku' => [ __( 'Featured product SKU (empty = newest in stock)', 'osa-arsenal' ), '', 'text' ],
	] );

	$wp_customize->add_section( 'cga_rail_section', [ 'title' => __( '2 · How your order gets to you', 'osa-arsenal' ), 'panel' => 'cga_home' ] );
	$rail_fields = [
		'cga_rail_heading' => [ __( 'Section heading', 'osa-arsenal' ), CGA_RAIL_HEADING_DEFAULT, 'text' ],
		'cga_rail_aside'   => [ __( 'Section aside', 'osa-arsenal' ), CGA_RAIL_ASIDE_DEFAULT, 'text' ],
	];
	foreach ( CGA_RAIL_CELL_DEFAULTS as $i => $default ) {
		$rail_fields[ 'cga_rail_cell_' . ( $i + 1 ) ] = [
			sprintf( __( 'Cell %d (kicker / title / body / note — one per line)', 'osa-arsenal' ), $i + 1 ),
			$default,
			'textarea',
		];
	}
	$add( 'cga_rail_section', $rail_fields );

	$wp_customize->add_section( 'cga_catalog_section', [ 'title' => __( '3 · Categories & shelf', 'osa-arsenal' ), 'panel' => 'cga_home' ] );
	$add( 'cga_catalog_section', [
		'cga_cats_heading'  => [ __( 'Categories heading', 'osa-arsenal' ), CGA_CATS_HEADING_DEFAULT, 'text' ],
		'cga_cats_aside'    => [ __( 'Categories aside', 'osa-arsenal' ), CGA_CATS_ASIDE_DEFAULT, 'text' ],
		'cga_shelf_heading' => [ __( 'Shelf heading', 'osa-arsenal' ), CGA_SHELF_HEADING_DEFAULT, 'text' ],
		'cga_shelf_aside'   => [ __( 'Shelf aside (links to /shop/)', 'osa-arsenal' ), CGA_SHELF_ASIDE_DEFAULT, 'text' ],
	] );

	$wp_customize->add_section( 'cga_steps_section', [ 'title' => __( '4 · California four-step', 'osa-arsenal' ), 'panel' => 'cga_home' ] );
	$steps_fields = [
		'cga_steps_heading' => [ __( 'Section heading', 'osa-arsenal' ), CGA_STEPS_HEADING_DEFAULT, 'text' ],
		'cga_steps_aside'   => [ __( 'Aside link (Label | URL)', 'osa-arsenal' ), CGA_STEPS_ASIDE_DEFAULT, 'text' ],
	];
	foreach ( CGA_STEP_DEFAULTS as $i => $default ) {
		$steps_fields[ 'cga_step_' . ( $i + 1 ) ] = [
			sprintf( __( 'Step %02d (title / body)', 'osa-arsenal' ), $i + 1 ),
			$default,
			'textarea',
		];
	}
	$add( 'cga_steps_section', $steps_fields );

	$wp_customize->add_section( 'cga_svc_section', [ 'title' => __( '5 · At the counter + training', 'osa-arsenal' ), 'panel' => 'cga_home' ] );
	$svc_fields = [
		'cga_svc_heading' => [ __( 'Services heading', 'osa-arsenal' ), CGA_SVC_HEADING_DEFAULT, 'text' ],
		'cga_svc_aside'   => [ __( 'Services aside link (Label | URL)', 'osa-arsenal' ), CGA_SVC_ASIDE_DEFAULT, 'text' ],
	];
	foreach ( CGA_SVC_CELL_DEFAULTS as $i => $default ) {
		$svc_fields[ 'cga_svc_cell_' . ( $i + 1 ) ] = [
			sprintf( __( 'Service %d (kicker / title / body)', 'osa-arsenal' ), $i + 1 ),
			$default,
			'textarea',
		];
	}
	$svc_fields['cga_train_heading'] = [ __( 'Training heading', 'osa-arsenal' ), CGA_TRAIN_HEADING_DEFAULT, 'text' ];
	$svc_fields['cga_train_aside']   = [ __( 'Training aside', 'osa-arsenal' ), CGA_TRAIN_ASIDE_DEFAULT, 'text' ];
	foreach ( CGA_TRAIN_CELL_DEFAULTS as $i => $default ) {
		$svc_fields[ 'cga_train_cell_' . ( $i + 1 ) ] = [
			sprintf( __( 'Course %d (title / body / meta / button Label | URL)', 'osa-arsenal' ), $i + 1 ),
			$default,
			'textarea',
		];
	}
	$add( 'cga_svc_section', $svc_fields );

	$wp_customize->add_section( 'cga_band_store_section', [ 'title' => __( '6 · Member band + store card', 'osa-arsenal' ), 'panel' => 'cga_home' ] );
	$add( 'cga_band_store_section', [
		'cga_band_eyebrow'   => [ __( 'Band eyebrow', 'osa-arsenal' ), CGA_BAND_EYEBROW_DEFAULT, 'text' ],
		'cga_band_title'     => [ __( 'Band title', 'osa-arsenal' ), CGA_BAND_TITLE_DEFAULT, 'text' ],
		'cga_band_body'      => [ __( 'Band body', 'osa-arsenal' ), CGA_BAND_BODY_DEFAULT, 'textarea' ],
		'cga_band_cta'       => [ __( 'Band button (Label | URL)', 'osa-arsenal' ), CGA_BAND_CTA_DEFAULT, 'text' ],
		'cga_store_heading'  => [ __( 'Store heading', 'osa-arsenal' ), CGA_STORE_HEADING_DEFAULT, 'text' ],
		'cga_store_aside'    => [ __( 'Store aside', 'osa-arsenal' ), CGA_STORE_ASIDE_DEFAULT, 'text' ],
		'cga_store_blurb'    => [ __( 'Store blurb', 'osa-arsenal' ), CGA_STORE_BLURB_DEFAULT, 'textarea' ],
		'cga_store_cta'      => [ __( 'Store button (Label | URL)', 'osa-arsenal' ), CGA_STORE_CTA_DEFAULT, 'text' ],
		'cga_store_map_note' => [ __( 'Map placeholder note', 'osa-arsenal' ), CGA_STORE_MAP_DEFAULT, 'textarea' ],
	] );
} );
