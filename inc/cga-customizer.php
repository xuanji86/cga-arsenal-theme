<?php
/**
 * CGA — Customizer wiring for the CGA homepage patterns + shared store facts.
 *
 * Built for a NON-TECHNICAL owner: one field per thing on screen, labeled by
 * what it is ("Title", "Body", "Button text", "Button link"), grouped in
 * sections that follow the page top-to-bottom. No field encodes structure
 * (no pipe syntax, no line-order rules) except the headline textarea where a
 * new line simply means a line break on screen.
 *
 * "Store Facts" (address / hours / phone / email / FFL / CFD) are the single
 * source of truth: patterns read them via {tokens}, page content (About) and
 * the footer read them via shortcodes — edit once, changes land everywhere.
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

const CGA_HERO_DEFAULTS = [
	'cga_hero_eyebrow'    => 'Est. 2023 · Corona, California · FFL {ffl} · CFD# {cfd}',
	'cga_hero_headline'   => "Stocked for\nCalifornia.",
	'cga_hero_lede'       => 'Roster-approved handguns, compliant rifles, and ammunition you can actually take home — on the shelf at 401 S Lincoln Ave. We run the DROS, proctor the FSC, and track the ten-day wait, so nothing surprises you at the counter.',
	'cga_hero_cta1_label' => 'Shop in-stock firearms',
	'cga_hero_cta1_url'   => '/shop/',
	'cga_hero_cta2_label' => 'How to buy in California →',
	'cga_hero_cta2_url'   => '/ca-guide/',
	'cga_hero_feat_tag'   => 'Featured · New arrival',
];

/** value / label pairs, rendered left to right. */
const CGA_HERO_STATS = [
	[ '{count}', 'Items on the shelf' ],
	[ '11–7', 'Open every day' ],
	[ '10 days', 'CA waiting period' ],
	[ '$26', 'FSC test, walk-in' ],
];

const CGA_RAIL_HEADING_DEFAULT = 'How your order gets to you';
const CGA_RAIL_ASIDE_DEFAULT   = 'Chosen at checkout';
/** small label / title / body / fee note. */
const CGA_RAIL_CELLS = [
	[ '01 / California buyers', 'Pick up in Corona', "Buy online, finish at the counter. Bring your CA driver's license or ID and your Firearm Safety Certificate; we submit the DROS the same day and the state's ten-day wait starts from that submission.", 'DROS $37.19 · dealer fee $ TBD · non-refundable' ],
	[ '02 / Out-of-state buyers', 'Ship to your FFL', 'Pick your local dealer at checkout and we verify their license against the ATF listing before the firearm leaves the building. Tracking goes to you and to them; the transfer finishes on their counter.', 'Flat $35 · Ships in 1–2 business days' ],
	[ '03 / In-store services', 'Ammo background check & FFL transfer', 'Every ammunition sale includes the CA DOJ eligibility check, run face-to-face at the counter. Bought a firearm elsewhere? We accept incoming FFL transfers — we handle the DROS and hold it through the ten-day wait.', 'Ammo check $1 · FFL transfer $75, DROS included' ],
];

const CGA_CATS_HEADING_DEFAULT  = 'Shop the counter';
const CGA_CATS_ASIDE_DEFAULT    = 'Live inventory from the POS';
const CGA_SHELF_HEADING_DEFAULT = 'On the shelf this week';
const CGA_SHELF_ASIDE_DEFAULT   = 'Updated as the POS receives · View all {count} →';

const CGA_STEPS_HEADING_DEFAULT     = 'Buying a firearm in California';
const CGA_STEPS_ASIDE_LABEL_DEFAULT = 'Full guide →';
const CGA_STEPS_ASIDE_URL_DEFAULT   = '/ca-guide/';
/** title / body; the 01–04 numbers render automatically. */
const CGA_STEPS = [
	[ 'Reserve online', 'Pay for any in-stock firearm and it comes off the floor under your name the moment the order lands.' ],
	[ 'Paperwork at the counter', 'Bring your CA DL or ID, your FSC, and — for handguns — proof of residency. We submit the DROS to CA DOJ that day.' ],
	[ 'Ten-day wait', "State law, not shop policy. We text you the release date the moment the DROS clears so you don't have to count." ],
	[ 'Pick up & demo', 'Come back, pass the safe-handling demonstration, and walk out. Your paperwork stays on file with us.' ],
];

const CGA_SVC_HEADING_DEFAULT     = 'At the counter';
const CGA_SVC_ASIDE_LABEL_DEFAULT = 'All services & pricing →';
const CGA_SVC_ASIDE_URL_DEFAULT   = '/services/';
/** price tag / title / body. */
const CGA_SVCS = [
	[ '$26 · Walk-in', 'FSC test', 'Two attempts included, $1 for the laminate. Fail the first and you retake after 24 hours — no new fee.' ],
	[ '$75 · DROS included', 'Incoming transfers', 'Bought elsewhere online? Send the seller our FFL. Once tracking shows delivered, come in for the background check.' ],
	[ '20% down', 'Layaway', 'Firearms only. Twenty percent holds it; the balance is due in full before the DROS is submitted.' ],
	[ 'Local shops', 'Price match', "Show us a local shop's advertised price on the same item in stock and we'll match it at the counter." ],
];

const CGA_TRAIN_HEADING_DEFAULT = 'Training';
const CGA_TRAIN_ASIDE_DEFAULT   = 'Schedule & pricing TBD';
/** title / body / details line / button text / button link. */
const CGA_COURSES = [
	[ 'Firearm safety fundamentals', 'First-time owners: safe handling, storage, transport under California law, and what the FSC actually tests you on.', 'Duration TBD · Price TBD · Next date TBD', 'Reserve a seat', '/services/#training' ],
	[ 'Shooting techniques', 'Grip, stance, sight picture and dry-fire practice for shooters who own the gun and want to actually run it well.', 'Duration TBD · Price TBD · Next date TBD', 'Reserve a seat', '/services/#training' ],
];

const CGA_BAND_STORE_DEFAULTS = [
	'cga_band_eyebrow'    => 'Membership',
	'cga_band_title'      => 'Member pricing on every order',
	'cga_band_body'       => 'Silver takes 3% off everything and Gold takes 5%, applied the moment you sign in — plus free DROS fees, FFL transfers and cleanings sitting as credits on your account.',
	'cga_band_cta_label'  => 'See membership',
	'cga_band_cta_url'    => '/membership/',
	'cga_store_heading'   => 'Visit the shop',
	'cga_store_aside'     => 'Open seven days',
	'cga_store_blurb'     => "Established 2022, open on Lincoln Ave since July 4th, 2023. We don't buy firearms from individuals and we don't take trade-ins — what's listed is what's on the shelf.",
	'cga_store_cta_label' => 'Get directions',
	'cga_store_cta_url'   => '/about/',
	'cga_store_map_note'  => "Map embed\n401 S Lincoln Ave, Suite L",
];

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
 * Render a multiline value as <br>-joined, per-line-escaped HTML.
 */
function cga_multiline_html( $text ) {
	$lines = array_filter( array_map( 'trim', preg_split( "/\r?\n/", (string) $text ) ), 'strlen' );
	return implode( '<br />', array_map( 'esc_html', $lines ) );
}

/**
 * The hero / shelf featured product: the Customizer pick when set (product
 * ID from the dropdown), else the newest in-stock product. WC_Product|null.
 */
function cga_featured_product() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return null;
	}
	$pid = (int) osa_text( 'cga_hero_featured_id', 0 );
	if ( $pid ) {
		$product = wc_get_product( $pid );
		if ( $product && 'publish' === $product->get_status() ) {
			return $product;
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
 * [cga_fact key="phone"]          → escaped fact ('br' joins multiline)
 * [cga_fact key="hours" line="2"] → a single line of a multiline fact
 * [cga_footer_legal]              → the footer legal block, assembled
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
 * Customizer controls pane: WooCommerce's selectWoo product-search assets
 * for the featured-product picker. PHP-rendered controls are in the DOM at
 * load, so WC's ready-time init catches them; the extra trigger on the
 * customizer 'ready' event is a harmless belt-and-braces re-init.
 * ---------------------------------------------------------------- */

add_action( 'customize_controls_enqueue_scripts', function () {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	wp_enqueue_script( 'wc-enhanced-select' );
	// WC's admin stylesheet bundle isn't registered on customize.php, so pull
	// the selectWoo skin straight from the plugin.
	wp_enqueue_style( 'cga-select2', WC()->plugin_url() . '/assets/css/select2.css', [], defined( 'WC_VERSION' ) ? WC_VERSION : null );
	wp_add_inline_style( 'cga-select2', '.customize-control .select2-container{width:100%!important}' );
	// Customizer controls embed just-in-time (on section expand, WP 4.7+), so a
	// document.ready init would miss them. Re-trigger WC's enhancer when the
	// control lands in the DOM and whenever its section opens — the init skips
	// selects it already enhanced, so repeat triggers are safe.
	wp_add_inline_script(
		'wc-enhanced-select',
		'jQuery(function($){if(!window.wp||!wp.customize){return;}'
		. 'var kick=function(){$(document.body).trigger("wc-enhanced-select-init");};'
		. 'wp.customize.control("cga_hero_featured_id",function(c){c.deferred.embedded.done(function(){setTimeout(kick,0);});});'
		. 'wp.customize.section("cga_hero_section",function(s){s.expanded.bind(function(open){if(open){setTimeout(kick,0);}});});'
		. '});'
	);
} );

/* ---------------------------------------------------------------- *
 * Customizer registration.
 * ---------------------------------------------------------------- */

add_action( 'customize_register', function ( $wp_customize ) {

	// One plain field. $type: text | textarea | url | select.
	$add = static function ( $section, $key, $label, $default, $type = 'text', $extra = [] ) use ( $wp_customize ) {
		$sanitize = 'sanitize_text_field';
		if ( 'textarea' === $type ) {
			$sanitize = 'sanitize_textarea_field';
		} elseif ( 'url' === $type ) {
			$sanitize = 'esc_url_raw';
		} elseif ( 'select' === $type ) {
			$sanitize = 'sanitize_text_field';
		}
		$wp_customize->add_setting( $key, [
			'default'           => $default,
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( $key, array_merge( [
			'label'   => $label,
			'section' => $section,
			'type'    => 'url' === $type ? 'text' : $type,
		], $extra ) );
	};

	/* --- Store facts (site-wide; panel: osa_site) --- */
	$wp_customize->add_section( 'cga_facts_section', [
		'title'       => __( 'Store Facts (edit once, updates everywhere)', 'osa-arsenal' ),
		'description' => __( 'The store\'s contact and licence details. Changing a value here updates the homepage, the footer, and the About page all at once.', 'osa-arsenal' ),
		'panel'       => 'osa_site',
		'priority'    => 5,
	] );
	$add( 'cga_facts_section', 'cga_fact_address', __( 'Address — street on the first line, city/state/ZIP on the second', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_address'], 'textarea' );
	$add( 'cga_facts_section', 'cga_fact_hours', __( 'Hours — days on the first line, times on the second', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_hours'], 'textarea' );
	$add( 'cga_facts_section', 'cga_fact_phone', __( 'Phone', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_phone'] );
	$add( 'cga_facts_section', 'cga_fact_email', __( 'Email', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_email'] );
	$add( 'cga_facts_section', 'cga_fact_ffl', __( 'FFL number', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_ffl'] );
	$add( 'cga_facts_section', 'cga_fact_cfd', __( 'CA DOJ CFD number', 'osa-arsenal' ), CGA_FACT_DEFAULTS['cga_fact_cfd'] );
	$add( 'cga_facts_section', 'cga_footer_legal_top', __( 'Footer — opening lines (above the address)', 'osa-arsenal' ), CGA_FOOTER_TOP_DEFAULT, 'textarea' );
	$add( 'cga_facts_section', 'cga_footer_legal_extra', __( 'Footer — last line (COE / ammo licence)', 'osa-arsenal' ), CGA_FOOTER_EXTRA_DEFAULT );

	/* --- Homepage panel --- */
	$wp_customize->add_panel( 'cga_home', [
		'title'       => __( 'CGA · Homepage', 'osa-arsenal' ),
		'description' => __( 'Everything written on the homepage, in page order. Click a section, type into a field, and the preview updates. Where you see {ffl}, {cfd} or {count}, the real value is filled in automatically (from Store Facts / live inventory) — you can leave those as they are.', 'osa-arsenal' ),
		'priority'    => 25,
	] );

	/* 1 · Hero */
	$wp_customize->add_section( 'cga_hero_section', [
		'title'       => __( '1 · Top of page (hero)', 'osa-arsenal' ),
		'description' => __( 'The opening block: headline, intro, the two buttons, the four quick numbers, and the featured product on the right.', 'osa-arsenal' ),
		'panel'       => 'cga_home',
	] );
	$add( 'cga_hero_section', 'cga_hero_eyebrow', __( 'Small line above the headline', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_eyebrow'] );
	$add( 'cga_hero_section', 'cga_hero_headline', __( 'Headline (press Enter for a new line)', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_headline'], 'textarea' );
	$add( 'cga_hero_section', 'cga_hero_lede', __( 'Intro paragraph', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_lede'], 'textarea' );
	$add( 'cga_hero_section', 'cga_hero_cta1_label', __( 'Green button — text', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_cta1_label'] );
	$add( 'cga_hero_section', 'cga_hero_cta1_url', __( 'Green button — link', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_cta1_url'], 'url' );
	$add( 'cga_hero_section', 'cga_hero_cta2_label', __( 'Outline button — text', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_cta2_label'] );
	$add( 'cga_hero_section', 'cga_hero_cta2_url', __( 'Outline button — link', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_cta2_url'], 'url' );
	foreach ( CGA_HERO_STATS as $i => [ $value, $label ] ) {
		$n = $i + 1;
		$add( 'cga_hero_section', "cga_hero_stat{$n}_value", sprintf( __( 'Quick number %d — value', 'osa-arsenal' ), $n ), $value );
		$add( 'cga_hero_section', "cga_hero_stat{$n}_label", sprintf( __( 'Quick number %d — caption', 'osa-arsenal' ), $n ), $label );
	}

	// Featured product: WooCommerce's own search-as-you-type product picker
	// (same selectWoo/AJAX control as in the Woo admin, searches the whole
	// catalog by name). Empty = Automatic (newest in stock). The card's photo,
	// name, price, stock and tags come from the product record itself
	// (Products in the dashboard) — here you only choose WHICH product shows.
	if ( ! class_exists( 'CGA_Product_Search_Control' ) ) {
		class CGA_Product_Search_Control extends WP_Customize_Control {
			public $type = 'cga-product-search';
			public function render_content() {
				$value   = (int) $this->value();
				$current = '';
				if ( $value && function_exists( 'wc_get_product' ) ) {
					$p = wc_get_product( $value );
					if ( $p ) {
						$current = $p->get_name() . ( $p->get_sku() ? ' (' . $p->get_sku() . ')' : '' );
					}
				}
				?>
				<label class="customize-control-title" for="<?php echo esc_attr( '_customize-input-' . $this->id ); ?>"><?php echo esc_html( $this->label ); ?></label>
				<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
				<select id="<?php echo esc_attr( '_customize-input-' . $this->id ); ?>" <?php $this->link(); ?>
					class="wc-product-search" style="width:100%"
					data-placeholder="<?php esc_attr_e( 'Automatic — newest in stock (type a name to search)', 'osa-arsenal' ); ?>"
					data-action="woocommerce_json_search_products"
					data-security="<?php echo esc_attr( wp_create_nonce( 'search-products' ) ); ?>"
					data-allow_clear="true">
					<option value=""></option>
					<?php if ( $value && '' !== $current ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" selected="selected"><?php echo esc_html( $current ); ?></option>
					<?php endif; ?>
				</select>
				<?php
			}
		}
	}
	$wp_customize->add_setting( 'cga_hero_featured_id', [
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new CGA_Product_Search_Control( $wp_customize, 'cga_hero_featured_id', [
		'label'       => __( 'Featured product', 'osa-arsenal' ),
		'description' => __( 'Type a product name and pick from the matches. Clear the box (×) to go back to Automatic — the newest product in stock. The card\'s photo, name, price and stock come from the product itself: edit those under Products.', 'osa-arsenal' ),
		'section'     => 'cga_hero_section',
	] ) );
	$add( 'cga_hero_section', 'cga_hero_feat_tag', __( 'Featured card — corner tag', 'osa-arsenal' ), CGA_HERO_DEFAULTS['cga_hero_feat_tag'] );

	/* 2 · Fulfilment rail */
	$wp_customize->add_section( 'cga_rail_section', [
		'title'       => __( '2 · How your order gets to you', 'osa-arsenal' ),
		'description' => __( 'The three boxes explaining pickup, FFL shipping and in-store services.', 'osa-arsenal' ),
		'panel'       => 'cga_home',
	] );
	$add( 'cga_rail_section', 'cga_rail_heading', __( 'Section heading', 'osa-arsenal' ), CGA_RAIL_HEADING_DEFAULT );
	$add( 'cga_rail_section', 'cga_rail_aside', __( 'Small note on the right of the heading', 'osa-arsenal' ), CGA_RAIL_ASIDE_DEFAULT );
	foreach ( CGA_RAIL_CELLS as $i => [ $kicker, $title, $body, $note ] ) {
		$n = $i + 1;
		$add( 'cga_rail_section', "cga_rail_cell{$n}_kicker", sprintf( __( 'Box %d — small green label', 'osa-arsenal' ), $n ), $kicker );
		$add( 'cga_rail_section', "cga_rail_cell{$n}_title", sprintf( __( 'Box %d — title', 'osa-arsenal' ), $n ), $title );
		$add( 'cga_rail_section', "cga_rail_cell{$n}_body", sprintf( __( 'Box %d — body', 'osa-arsenal' ), $n ), $body, 'textarea' );
		$add( 'cga_rail_section', "cga_rail_cell{$n}_note", sprintf( __( 'Box %d — fee note at the bottom', 'osa-arsenal' ), $n ), $note );
	}

	/* 3 · Categories & shelf */
	$wp_customize->add_section( 'cga_catalog_section', [
		'title'       => __( '3 · Categories & product grid', 'osa-arsenal' ),
		'description' => __( 'Headings only — the category tiles and the product grid fill themselves from live inventory.', 'osa-arsenal' ),
		'panel'       => 'cga_home',
	] );
	$add( 'cga_catalog_section', 'cga_cats_heading', __( 'Categories — heading', 'osa-arsenal' ), CGA_CATS_HEADING_DEFAULT );
	$add( 'cga_catalog_section', 'cga_cats_aside', __( 'Categories — small note on the right', 'osa-arsenal' ), CGA_CATS_ASIDE_DEFAULT );
	$add( 'cga_catalog_section', 'cga_shelf_heading', __( 'Product grid — heading', 'osa-arsenal' ), CGA_SHELF_HEADING_DEFAULT );
	$add( 'cga_catalog_section', 'cga_shelf_aside', __( 'Product grid — note on the right (links to the shop)', 'osa-arsenal' ), CGA_SHELF_ASIDE_DEFAULT );

	/* 4 · CA four-step */
	$wp_customize->add_section( 'cga_steps_section', [
		'title'       => __( '4 · Buying in California (4 steps)', 'osa-arsenal' ),
		'description' => __( 'The four numbered steps. The 01–04 numbers appear automatically.', 'osa-arsenal' ),
		'panel'       => 'cga_home',
	] );
	$add( 'cga_steps_section', 'cga_steps_heading', __( 'Section heading', 'osa-arsenal' ), CGA_STEPS_HEADING_DEFAULT );
	$add( 'cga_steps_section', 'cga_steps_aside_label', __( 'Link on the right — text', 'osa-arsenal' ), CGA_STEPS_ASIDE_LABEL_DEFAULT );
	$add( 'cga_steps_section', 'cga_steps_aside_url', __( 'Link on the right — link', 'osa-arsenal' ), CGA_STEPS_ASIDE_URL_DEFAULT, 'url' );
	foreach ( CGA_STEPS as $i => [ $title, $body ] ) {
		$n = $i + 1;
		$add( 'cga_steps_section', "cga_step{$n}_title", sprintf( __( 'Step %d — title', 'osa-arsenal' ), $n ), $title );
		$add( 'cga_steps_section', "cga_step{$n}_body", sprintf( __( 'Step %d — body', 'osa-arsenal' ), $n ), $body, 'textarea' );
	}

	/* 5 · Services + training */
	$wp_customize->add_section( 'cga_svc_section', [
		'title'       => __( '5 · Counter services & training', 'osa-arsenal' ),
		'description' => __( 'The four service boxes, then the two training courses.', 'osa-arsenal' ),
		'panel'       => 'cga_home',
	] );
	$add( 'cga_svc_section', 'cga_svc_heading', __( 'Services — heading', 'osa-arsenal' ), CGA_SVC_HEADING_DEFAULT );
	$add( 'cga_svc_section', 'cga_svc_aside_label', __( 'Services — link on the right, text', 'osa-arsenal' ), CGA_SVC_ASIDE_LABEL_DEFAULT );
	$add( 'cga_svc_section', 'cga_svc_aside_url', __( 'Services — link on the right, link', 'osa-arsenal' ), CGA_SVC_ASIDE_URL_DEFAULT, 'url' );
	foreach ( CGA_SVCS as $i => [ $kicker, $title, $body ] ) {
		$n = $i + 1;
		$add( 'cga_svc_section', "cga_svc{$n}_kicker", sprintf( __( 'Service %d — price tag (green)', 'osa-arsenal' ), $n ), $kicker );
		$add( 'cga_svc_section', "cga_svc{$n}_title", sprintf( __( 'Service %d — title', 'osa-arsenal' ), $n ), $title );
		$add( 'cga_svc_section', "cga_svc{$n}_body", sprintf( __( 'Service %d — body', 'osa-arsenal' ), $n ), $body, 'textarea' );
	}
	$add( 'cga_svc_section', 'cga_train_heading', __( 'Training — heading', 'osa-arsenal' ), CGA_TRAIN_HEADING_DEFAULT );
	$add( 'cga_svc_section', 'cga_train_aside', __( 'Training — small note on the right', 'osa-arsenal' ), CGA_TRAIN_ASIDE_DEFAULT );
	foreach ( CGA_COURSES as $i => [ $title, $body, $meta, $btn_label, $btn_url ] ) {
		$n = $i + 1;
		$add( 'cga_svc_section', "cga_course{$n}_title", sprintf( __( 'Course %d — name', 'osa-arsenal' ), $n ), $title );
		$add( 'cga_svc_section', "cga_course{$n}_body", sprintf( __( 'Course %d — description', 'osa-arsenal' ), $n ), $body, 'textarea' );
		$add( 'cga_svc_section', "cga_course{$n}_meta", sprintf( __( 'Course %d — details line (duration · price · date)', 'osa-arsenal' ), $n ), $meta );
		$add( 'cga_svc_section', "cga_course{$n}_btn_label", sprintf( __( 'Course %d — button text', 'osa-arsenal' ), $n ), $btn_label );
		$add( 'cga_svc_section', "cga_course{$n}_btn_url", sprintf( __( 'Course %d — button link', 'osa-arsenal' ), $n ), $btn_url, 'url' );
	}

	/* 6 · Member band + store card */
	$wp_customize->add_section( 'cga_band_store_section', [
		'title'       => __( '6 · Membership banner & visit-us card', 'osa-arsenal' ),
		'description' => __( 'The green membership banner, then the store card. The address / hours / phone / email on the card come from Store Facts.', 'osa-arsenal' ),
		'panel'       => 'cga_home',
	] );
	$add( 'cga_band_store_section', 'cga_band_eyebrow', __( 'Banner — small label', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_band_eyebrow'] );
	$add( 'cga_band_store_section', 'cga_band_title', __( 'Banner — title', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_band_title'] );
	$add( 'cga_band_store_section', 'cga_band_body', __( 'Banner — body', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_band_body'], 'textarea' );
	$add( 'cga_band_store_section', 'cga_band_cta_label', __( 'Banner — button text', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_band_cta_label'] );
	$add( 'cga_band_store_section', 'cga_band_cta_url', __( 'Banner — button link', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_band_cta_url'], 'url' );
	$add( 'cga_band_store_section', 'cga_store_heading', __( 'Store card — heading', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_store_heading'] );
	$add( 'cga_band_store_section', 'cga_store_aside', __( 'Store card — small note on the right', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_store_aside'] );
	$add( 'cga_band_store_section', 'cga_store_blurb', __( 'Store card — paragraph', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_store_blurb'], 'textarea' );
	$add( 'cga_band_store_section', 'cga_store_cta_label', __( 'Store card — button text', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_store_cta_label'] );
	$add( 'cga_band_store_section', 'cga_store_cta_url', __( 'Store card — button link', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_store_cta_url'], 'url' );
	$add( 'cga_band_store_section', 'cga_store_map_note', __( 'Map area — placeholder text (until a real map goes in)', 'osa-arsenal' ), CGA_BAND_STORE_DEFAULTS['cga_store_map_note'], 'textarea' );
} );
