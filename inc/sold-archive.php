<?php
/**
 * OSA — SOLD archive state on the single product page.
 *
 * A per-serial gun that sold is not delisted: ffl_woo_sync leaves it at
 * status=publish with zero stock and stamps `_osa_sold_at` (spec §4.1), so the
 * URL keeps its rankings and the page becomes a record of the sale. This module
 * is everything that changes on such a page (design-spec §0–§3):
 *
 *   1. SOLD chip in the gallery badge slot, replacing any sale badge
 *   2. the price row replaced wholesale by the .osa-sold-record band
 *   3. the rescue strip moved BELOW the band, gaining a model-hub CTA
 *
 * ── The rule to not get wrong (design-spec §0 + Addendum 5) ─────────────────
 * SOLD is `_osa_sold_at` non-empty AND the product out of stock. Both clauses
 * carry weight and neither alone is the rule:
 *
 *  - The META is what separates SOLD from merely out-of-stock. A one-off
 *    collectible that is out of stock sold; a uniform-price lot that is out of
 *    stock is awaiting restock (spec §4.1 leaves those untouched). Every other
 *    out-of-stock affordance in this theme hangs off is_in_stock(), and keying
 *    SOLD off that alone would brand restockable inventory permanently "Sold".
 *  - The STOCK clause (Addendum 5) settles what happens when the two disagree,
 *    which only inconsistent data produces — the sync always zeroes stock when
 *    it archives. If someone restocks by hand without clearing the meta, we
 *    fail OPEN: the gun stays purchasable rather than sitting behind a SOLD
 *    band nobody can buy through. It also keeps this layer identical to
 *    FflCore\SEO\Frontend\Schema::is_sold_archived(), so the page and its
 *    structured data can never disagree about whether the gun is gone.
 *
 * And the meta test is NON-EMPTY, not exists: ffl_woo_sync's relist path writes
 * an EMPTY STRING rather than deleting the key (mapping.py:378-379), so an
 * exists() check would keep a returned gun marked sold forever.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

// ─── Pure decision layer (unit-tested in tests/test-sold-archive.php) ────────

/**
 * Is this product a SOLD archive? PURE.
 *
 * Signature mirrors FflCore\SEO\Frontend\Schema::is_sold_archived() exactly, so
 * the UI and the structured data cannot drift apart (Addendum 5).
 *
 * ⚠ KEEP IN STEP WITH ffl-core seo/frontend/Schema.php:214. Deliberately a copy
 * rather than a call: the theme must not fatal if the plugin is deactivated.
 * The cost of that decoupling is that nothing enforces the two stay identical —
 * change one, change the other.
 *
 * @param mixed $sold_at     Raw meta (ISO date string, '' once relisted, or false).
 * @param bool  $is_in_stock WC_Product::is_in_stock().
 */
function osa_sold_is_archived( $sold_at, $is_in_stock = false ) {
	return is_string( $sold_at ) && '' !== trim( $sold_at ) && ! $is_in_stock;
}

/**
 * Timestamp for a BARE CALENDAR DATE, anchored at midnight in $tz. PURE.
 * Returns null when the value is missing or not a real Y-m-d date.
 *
 * Why this exists rather than strtotime() (review R2 MAJOR-3): `_osa_sold_at`
 * is a plain date with no time — ffl_woo_sync writes it with nowdate()
 * (sync.py:583). strtotime('2026-07-09') resolves to midnight UTC, and
 * wp_date() then renders that instant in the store's timezone (America/Chicago,
 * UTC-5/6) — which is still the previous evening. Every sold page in production
 * would have shown the sale one day early. It was invisible locally because
 * osa-local runs UTC, where the two coincide; that is exactly why the guard
 * below pins a NON-UTC zone.
 *
 * Anchoring the parse in the site timezone keeps a calendar date a calendar
 * date: no instant is ever computed in a zone the reader does not live in.
 *
 * The strict re-format check rejects what createFromFormat() would otherwise
 * silently repair ('2026-13-45' → 2027-02-14), so junk falls through to
 * variant C instead of printing a confidently wrong date.
 *
 * @param mixed        $sold_at Raw `_osa_sold_at` meta.
 * @param DateTimeZone $tz      Site timezone.
 * @return int|null
 */
function osa_sold_date_timestamp( $sold_at, DateTimeZone $tz ) {
	$trimmed = is_string( $sold_at ) ? trim( $sold_at ) : '';

	if ( '' === $trimmed ) {
		return null;
	}

	// '!' zeroes every field the format does not set, so the result is local
	// midnight rather than "midnight-ish, plus whatever time it is now".
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $trimmed, $tz );

	if ( false === $date || $date->format( 'Y-m-d' ) !== $trimmed ) {
		return null;
	}

	return $date->getTimestamp();
}

/**
 * Which band variant applies? PURE. (design-spec §2.3)
 *
 *   A — realized price AND date (web fulfillment passes the order line total)
 *   B — date only (POS / reconcile archive: no exact figure exists)
 *   C — neither (only the §4.4 backfill can produce this)
 *
 * Note "0.00" is a REAL realized price (write-off, trade credit) and must not
 * be swallowed — hence a string-emptiness test rather than empty().
 *
 * @param mixed $sold_price Raw `_osa_sold_price` meta.
 * @param mixed $sold_at    Raw `_osa_sold_at` meta.
 */
function osa_sold_record_variant( $sold_price, $sold_at ) {
	$has_date  = is_string( $sold_at ) && '' !== trim( $sold_at );
	$has_price = is_string( $sold_price ) && '' !== trim( $sold_price );

	if ( ! $has_date ) {
		return 'C';
	}

	return $has_price ? 'A' : 'B';
}

/**
 * Interpret a pa_model `_osa_hub_page` term-meta value. PURE.
 *
 * Hand-authored per model term (spec §4.5), so both shapes a human would
 * plausibly store are accepted — a page ID, or a path/full URL. Anything else
 * yields 'none' and NO CTA renders: design-spec §3.2 is explicit that a
 * category archive must never stand in for a hub, because that would point the
 * link equity at the wrong page.
 *
 * Mirrors FflCore\SEO\Frontend\Redirects::hub_meta_kind() so the CTA and the
 * §4.2 redirect always agree on what counts as a hub. Deliberately duplicated
 * rather than called: the theme must not hard-depend on plugin internals.
 *
 * ⚠ KEEP IN STEP WITH ffl-core seo/frontend/Redirects.php:134. Drift here is
 * silent and asymmetric — a CTA that renders for a hub the redirect will not
 * honour, or vice versa.
 *
 * @param mixed $value Raw term meta.
 * @return string 'id'|'url'|'none'
 */
function osa_sold_hub_meta_kind( $value ) {
	if ( is_int( $value ) ) {
		return $value > 0 ? 'id' : 'none';
	}

	if ( ! is_string( $value ) ) {
		return 'none';
	}

	$trimmed = trim( $value );

	if ( '' === $trimmed ) {
		return 'none';
	}

	if ( ctype_digit( $trimmed ) ) {
		return (int) $trimmed > 0 ? 'id' : 'none';
	}

	foreach ( [ '/', 'http://', 'https://' ] as $prefix ) {
		if ( 0 === strpos( $trimmed, $prefix ) ) {
			return 'url';
		}
	}

	return 'none';
}

/**
 * What should the rescue strip render? PURE. (design-spec §3.3)
 *
 * The 'cta-only' row is the gap this closes: today the strip returns early
 * when nothing comparable is in stock, which leaves a SOLD page with no way
 * out at all.
 *
 * @param int  $card_count How many comparable in-stock products were found.
 * @param bool $has_hub    Whether the model maps to a hub page.
 * @return string 'cards+cta'|'cards'|'cta-only'|'none'
 */
function osa_sold_rescue_shape( $card_count, $has_hub ) {
	if ( (int) $card_count > 0 ) {
		return $has_hub ? 'cards+cta' : 'cards';
	}

	return $has_hub ? 'cta-only' : 'none';
}

/**
 * Build the .osa-sold-record band. PURE — callers pass already-escaped
 * fragments.
 *
 * Single root element by design: osa-base.css:2446 restacks space-between flex
 * groups vertically on mobile, which would tear the band's two clusters apart
 * if the band were several siblings (design-spec §5).
 *
 * @param string $variant      'A'|'B'|'C'
 * @param string $price_html   Formatted price (variant A only), already escaped.
 * @param string $date_label   Localized date (variants A/B), already escaped.
 */
function osa_sold_record_markup( $variant, $price_html, $date_label ) {
	$left = ( 'A' === $variant )
		? '<span class="osa-sold-record__label">Sold For</span>'
			. '<span class="osa-sold-record__figure">' . $price_html . '</span>'
		// Variants B and C state the fact and stop. No label it cannot fill, no
		// placeholder, and never the asking price dressed as a realized one
		// (spec §5).
		: '<span class="osa-sold-record__figure osa-sold-record__figure--word">Sold</span>';

	// The right cluster carries its own modifier rather than relying on
	// :last-child — in variant C the LEFT cluster is the last child, and a
	// positional selector would right-align the word SOLD.
	$right = ( 'C' === $variant )
		? ''
		: '<span class="osa-sold-record__side osa-sold-record__side--right">'
			. '<span class="osa-sold-record__label">Sold On</span>'
			. '<span class="osa-sold-record__date">' . $date_label . '</span>'
			. '</span>';

	return '<div class="osa-sold-record">'
		. '<span class="osa-sold-record__side">' . $left . '</span>'
		. $right
		. '</div>';
}

// ─── WordPress-coupled layer ────────────────────────────────────────────────

/**
 * Is the product currently being viewed a SOLD archive?
 *
 * @param int|WC_Product|null $product Product or ID; defaults to the queried product.
 */
function osa_is_sold_archived( $product = null ) {
	$post_id = 0;

	if ( $product instanceof WC_Product ) {
		$post_id = $product->get_id();
	} elseif ( is_numeric( $product ) ) {
		$post_id = (int) $product;
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$post_id = get_queried_object_id();
	}

	if ( ! $post_id ) {
		return false;
	}

	// Resolve the product when we were handed an ID or read the queried object:
	// the stock clause (Addendum 5) needs a WC_Product, and without one we must
	// fail OPEN — no product, no SOLD state, page renders as normal.
	if ( ! $product instanceof WC_Product ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $post_id ) : null;
	}

	if ( ! $product instanceof WC_Product ) {
		return false;
	}

	return osa_sold_is_archived(
		get_post_meta( $post_id, '_osa_sold_at', true ),
		(bool) $product->is_in_stock()
	);
}

/**
 * Model-hub URL for a product, or '' when its model has no hub.
 *
 * @return array{url:string,model:string} Empty url when there is no mapping.
 */
function osa_sold_hub_link( $post_id ) {
	$empty = [ 'url' => '', 'model' => '' ];
	$terms = get_the_terms( (int) $post_id, 'pa_model' );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $empty;
	}

	foreach ( $terms as $term ) {
		$meta = get_term_meta( $term->term_id, '_osa_hub_page', true );

		switch ( osa_sold_hub_meta_kind( $meta ) ) {
			case 'id':
				$link = get_permalink( (int) $meta );
				if ( is_string( $link ) && '' !== $link ) {
					return [ 'url' => $link, 'model' => $term->name ];
				}
				break;

			case 'url':
				return [ 'url' => trim( (string) $meta ), 'model' => $term->name ];
		}
	}

	return $empty;
}

/**
 * §1 — SOLD chip takes over the gallery badge slot.
 *
 * Filtering the sale-badge block rather than adding a new element guarantees
 * design-spec's "one chip, always" rule: an on-sale gun that sold cannot show
 * both (AC-03), and the existing slot CSS (osa-base.css:388) already positions
 * it at top-left 12/12.
 */
add_filter( 'render_block_woocommerce/product-sale-badge', 'osa_sold_badge_block', 10, 1 );
function osa_sold_badge_block( $content ) {
	if ( ! osa_is_sold_archived() ) {
		return $content;
	}

	return '<div class="wp-block-woocommerce-product-sale-badge"><span class="osa-sold-badge">Sold</span></div>';
}

/**
 * §2 — the price row group is replaced wholesale by the band.
 *
 * Replacing the GROUP rather than its children is what makes one edit satisfy
 * AC-10/11/12 at once: the group's 1px rules and 20px padding, the price, the
 * add-to-cart form with its stock copy, AND ffl-core's injected tax/shipping
 * chips (PriceBadges filters the price block, which never renders) all leave
 * together. Nothing on a sold page should promise anything about buying.
 *
 * The group is found STRUCTURALLY — a core/group whose inner blocks include
 * woocommerce/product-price — not by matching its inline styles, which a
 * designer could legitimately change tomorrow.
 */
add_filter( 'render_block', 'osa_sold_price_row_block', 10, 2 );
function osa_sold_price_row_block( $content, $block ) {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return $content;
	}

	$wraps_price = false;
	foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $inner ) {
		if ( 'woocommerce/product-price' === ( $inner['blockName'] ?? '' ) ) {
			$wraps_price = true;
			break;
		}
	}

	if ( ! $wraps_price || ! osa_is_sold_archived() ) {
		return $content;
	}

	$post_id = get_queried_object_id();
	$sold_at = get_post_meta( $post_id, '_osa_sold_at', true );
	$price   = get_post_meta( $post_id, '_osa_sold_price', true );
	$variant = osa_sold_record_variant( $price, $sold_at );

	$price_html = '';
	if ( 'A' === $variant ) {
		$price_html = function_exists( 'wc_price' )
			? wp_kses_post( wc_price( (float) $price ) )
			: esc_html( (string) $price );
	}

	// The ISO value is the machine-readable source; the page shows a localized
	// date in the site's timezone (design-spec §2.3). Uppercasing is CSS's job.
	$date_label = '';
	if ( 'C' !== $variant ) {
		$stamp = osa_sold_date_timestamp( $sold_at, function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' ) );

		if ( null === $stamp ) {
			$variant = 'C'; // unparseable date → drop the cluster rather than print junk
		} else {
			// wp_date() renders in the site timezone, and the stamp is already
			// anchored to midnight THERE, so the calendar day survives the trip.
			$date_label = esc_html( function_exists( 'wp_date' ) ? wp_date( 'F j, Y', $stamp ) : gmdate( 'F j, Y', $stamp ) );
		}
	}

	return osa_sold_record_markup( $variant, $price_html, $date_label );
}

/**
 * §3.1 — the rescue strip renders BELOW the band on a sold page.
 *
 * It normally rides woocommerce_single_product_summary:32, which WooCommerce's
 * block-template compatibility layer hoists ABOVE the price row — so a reader
 * would meet "comparable pieces, ready today" before learning this one sold.
 * That is the story backwards.
 *
 * The fix is a REAL DOM move, not CSS order (design-spec §3.1): visual order
 * and reading order must not diverge for screen readers or crawlers. The strip
 * is appended inside the info column, after its last block, by inserting before
 * the column's closing tag — the same splice ffl-core's PriceBadges uses.
 *
 * Only sold pages are affected; an ordinary out-of-stock product keeps today's
 * summary-hook placement exactly (design-spec §0).
 */
add_filter( 'render_block', 'osa_sold_rescue_after_column', 10, 2 );
function osa_sold_rescue_after_column( $content, $block ) {
	if ( 'core/column' !== ( $block['blockName'] ?? '' ) || ! osa_is_sold_archived() ) {
		return $content;
	}

	if ( ! osa_sold_column_wraps_price( $block ) ) {
		return $content;
	}

	$rescue = osa_sold_rescue_html( get_queried_object_id() );

	if ( '' === $rescue ) {
		return $content;
	}

	$pos = strrpos( $content, '</div>' );

	return false === $pos ? $content . $rescue : substr_replace( $content, $rescue, $pos, 0 );
}

/**
 * Does this column contain the price block anywhere beneath it?
 *
 * @param array $block Parsed block.
 */
function osa_sold_column_wraps_price( $block ) {
	foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $inner ) {
		if ( 'woocommerce/product-price' === ( $inner['blockName'] ?? '' ) ) {
			return true;
		}
		if ( osa_sold_column_wraps_price( $inner ) ) {
			return true;
		}
	}

	return false;
}

/**
 * §3.2/§3.3 — rescue strip markup for a sold product: comparable stock,
 * then the hub CTA as the page's single way forward.
 *
 * The CTA is deliberately outlined rather than solid (design-spec §3.2): the
 * solid ink on this page belongs to the sale record, so the action ranks one
 * step below it.
 */
function osa_sold_rescue_html( $post_id ) {
	$post_id = (int) $post_id;
	$ids     = function_exists( 'osa_get_related_product_ids' ) ? osa_get_related_product_ids( $post_id, 3 ) : [];
	$hub     = osa_sold_hub_link( $post_id );
	$shape   = osa_sold_rescue_shape( count( $ids ), '' !== $hub['url'] );

	if ( 'none' === $shape ) {
		return '';
	}

	$title = ( 'cta-only' === $shape )
		? sprintf( 'More %s on the floor', esc_html( $hub['model'] ) )
		: 'Comparable pieces, ready today';

	ob_start();
	echo '<section class="osa-related-rescue osa-related-rescue--sold" data-osa-list="comparable_oos">';
	echo '<p class="osa-mono-caps osa-related-kicker">Still in the Case</p>';
	echo '<h2 class="osa-related-title">' . $title . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	if ( 'cta-only' !== $shape ) {
		echo '<div class="osa-catalog-grid osa-catalog-grid--cols-3 osa-related-rescue__grid">';
		foreach ( $ids as $id ) {
			osa_render_product_card_compact( $id, '16-9' );
		}
		echo '</div>';
	}

	if ( '' !== $hub['url'] ) {
		printf(
			'<a class="osa-btn osa-btn--outline osa-btn--block osa-sold-hub-cta" href="%s">%s</a>',
			esc_url( $hub['url'] ),
			sprintf( 'See all %s in stock &rarr;', esc_html( $hub['model'] ) )
		);
	}

	echo '</section>';

	return (string) ob_get_clean();
}
