<?php
/**
 * OSA · Model Links — navigation links to the model landing pages.
 *
 * Data source is the `osa_model_links` theme mod (Customize → OSA · Model
 * Pages, one "Label|URL|ImageURL?|FilterSlugs?" per line, parsed by
 * osa_model_links_items() in inc/customizer.php) so the shop owner can add
 * a landing page site-wide without touching code — the model-landing SOP's
 * single list feeds the homepage cards, shop chips and landing cross-links.
 *
 * Variants (CSS contract, styles live in assets/css/osa-base.css — fe-owned):
 *   cards — image + label tiles (homepage "Shop by Model" strip)
 *   chips — no-image pill row (shop header / landing "More from the collection")
 *
 * linkTo (spec §B, B-plan) controls where a card points:
 *   landing (default) — the /firearms/ landing page (every legacy instance)
 *   filter            — the product archive filtered to the row's pa_model
 *                       slugs (/shop/?filter_model[]=<slug>&filter_model[]=…,
 *                       array-style — see the loop note). Only rows that
 *                       carry slugs are redirected; slug-less rows fall back to
 *                       their landing URL. Applied ONLY to the /firearms/ hub
 *                       instance, so the homepage strip / shop chips / landing
 *                       tails keep their landing links unchanged.
 *                       Redirected rows are then re-listed after the nav in a
 *                       plain-text index (__index) so the parent hub keeps
 *                       linking its own children — see the index note at the
 *                       bottom of this file (run 2026-07-30).
 *
 * The card/chip markup itself is IDENTICAL in every mode — linkTo only ever
 * changes an href value. The index is a sibling AFTER </nav>, never inside it;
 * when it renders, nav + index are enclosed by ONE wrapper (__group) which takes
 * the block-supports attributes, so the instance's spacing/align applies to the
 * whole block. Without an index the nav IS the wrapper, exactly as before.
 *
 * An explicitly emptied theme mod renders nothing: no empty shells.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$variant = (string) ( $attributes['variant'] ?? 'chips' );
if ( ! in_array( $variant, [ 'cards', 'chips' ], true ) ) {
	$variant = 'chips';
}
$heading = trim( (string) ( $attributes['heading'] ?? '' ) );

$link_to = (string) ( $attributes['linkTo'] ?? 'landing' );
if ( ! in_array( $link_to, [ 'landing', 'filter' ], true ) ) {
	$link_to = 'landing';
}

$items = function_exists( 'osa_model_links_items' ) ? osa_model_links_items() : [];
if ( ! $items ) {
	return '';
}

// Product-archive base for filter links — the same source fcpf resolves the
// shop URL from. Computed once; fallback to /shop/ if the archive is missing.
$archive_base = '';
if ( 'filter' === $link_to ) {
	$archive_base = get_post_type_archive_link( 'product' );
	if ( ! is_string( $archive_base ) || '' === $archive_base ) {
		$archive_base = '/shop/';
	}
}

// Rows whose card was actually redirected away from their landing page. Those
// landing URLs would otherwise appear nowhere on the page, so they are re-listed
// in the index below the nav. A row that still points at its own landing is NOT
// collected — it is already linked, and a second anchor would be a duplicate.
$orphaned = [];

// Hrefs are resolved in one pass BEFORE anything is echoed: whether an index is
// owed decides which element carries the block wrapper, and that element opens
// first.
$rows = [];
foreach ( $items as $item ) {
	// Default: the landing URL (byte-identical to legacy). Only a filter block
	// with a slug-bearing row swaps in the /shop/ filter URL.
	$href = $item['url'];
	if ( 'filter' === $link_to && '' !== ( $item['slugs'] ?? '' ) ) {
		// Array-style params — one filter_model[]=<slug> per slug, matching the
		// sidebar checkbox format. This is DELIBERATE and must NOT be "optimized"
		// back to a CSV (?filter_model=a,b): WooCommerce core layered-nav also
		// consumes ?filter_{attr}=<csv>, injecting an operator=AND clause that
		// empties any multi-slug result (BUG-001). The array form is invisible to
		// WC core, so only ffl-core's OR query applies. Single slug uses the same
		// array form to avoid WC's double-processing.
		//
		// Read through the shared normalizer so this link and the product backlink
		// can never disagree about what the field says — and so a slug typed in
		// capitals still matches, term slugs being lower-case (review r1 minor-2).
		$slugs = osa_model_link_row_slugs( $item );
		if ( $slugs ) {
			$pairs = [];
			foreach ( $slugs as $slug ) {
				$pairs[] = 'filter_model[]=' . rawurlencode( $slug );
			}
			$sep  = ( false === strpos( $archive_base, '?' ) ) ? '?' : '&';
			$href = $archive_base . $sep . implode( '&', $pairs );

			$orphaned[] = $item;
		}
	}
	$rows[] = [ 'item' => $item, 'href' => $href ];
}

// One root element whenever an index is owed. get_block_wrapper_attributes()
// carries what the page author set on the block instance — the hub's
// {"spacing":{"margin":{"bottom":"40px"}}} and the align: wide/full block.json
// advertises — so it has to enclose BOTH the grid and the index or it only
// applies to half the block: that 40px is there to hold the block off the
// "BROWSE THE FULL CATALOG" button below, and as a nav-only margin it landed
// between grid and index instead, leaving the index all but touching the button
// (review r1). Nothing to index → the nav stays the wrapper root, byte-identical
// to legacy; that is the path every landing instance takes (homepage strip,
// /shop/ chips, landing tails).
$grouped = (bool) $orphaned;
if ( $grouped ) {
	echo '<div ' . get_block_wrapper_attributes( [ 'class' => 'osa-model-links__group' ] ) . '>';
	echo '<nav class="osa-model-links osa-model-links--' . esc_attr( $variant ) . '" aria-label="' . esc_attr( __( 'Model pages', 'osa-arsenal' ) ) . '">';
} else {
	echo '<nav ' . get_block_wrapper_attributes( [
		'class'      => 'osa-model-links osa-model-links--' . $variant,
		'aria-label' => __( 'Model pages', 'osa-arsenal' ),
	] ) . '>';
}
if ( '' !== $heading ) {
	echo '<h2 class="osa-model-links__heading">' . esc_html( $heading ) . '</h2>';
}
foreach ( $rows as $row ) {
	$item = $row['item'];
	echo '<a class="osa-model-links__item" href="' . esc_url( $row['href'] ) . '">';
	if ( 'cards' === $variant && '' !== $item['img'] ) {
		echo '<img class="osa-model-links__img" src="' . esc_url( $item['img'] ) . '" alt="' . esc_attr( $item['label'] ) . '" loading="lazy" decoding="async">';
	}
	echo '<span class="osa-model-links__label">' . esc_html( $item['label'] ) . '</span>';
	echo '</a>';
}
echo '</nav>';

// ---- Landing-page index (run 2026-07-30) ------------------------------------
// Why this exists: when 07-22 pointed the hub cards at the shop filter, the card
// became each row's ONLY anchor — so /firearms/ stopped linking its own 10 child
// landing pages (measured hub→landing internal links = 0) and 3 of them were
// never indexed by Google, while the hub itself holds a pos-2.8 brand position
// it was passing nowhere (seo-effectiveness run 2026-07-30, findings 6-1/6-2).
//
// A per-card secondary link was tried first and rejected on sight: the anchor
// text restated the card title directly beneath it, and at 375px one model name
// occupied four consecutive lines. The links moved down here instead — same
// crawlable model-word anchors, zero cost to the card grid, which is now
// byte-identical to its pre-fix output in every mode.
//
// Plain <a href> only: no JS, no onclick, no rel=nofollow. Visible text is the
// bare model label (the anchor text this fix exists to pass down); the aria-label
// adds "model page" so the accessible name is distinguishable from the card
// above, which carries the same label but points at the filtered shop.
// Deliberately NOT a <nav>: it would duplicate the landmark the cards already
// expose with the same set of links.
if ( $grouped ) {
	echo '<div class="osa-model-links__index">';
	// ⚠ EVERY direct child of this container is BLOCK-LEVEL, and that is
	// load-bearing. wpautop inserts "\n\n" around block-level tags and
	// paragraph-izes whatever loose runs remain, so ONE inline element or one
	// bare text node beside the <ul> makes it emit an orphan </p> — an empty
	// paragraph with real margin in the browser. That is why the label is a DIV
	// and not a SPAN (measured against the real wp-includes/formatting.php).
	// A <p> would be structurally stable too, but inherits theme.json's
	// core/paragraph typography (its selector is bare `p`).
	//
	// Editing this markup? The invariant is guarded portably in
	// tests/test-model-links.php (osa_wpautop_hazards) and against the REAL
	// wpautop, with the known-bad shapes as controls, by:
	//     bash tests/verify-wpautop-idempotent.sh
	echo '<div class="osa-model-links__index-label">' . esc_html( __( 'All model pages', 'osa-arsenal' ) ) . '</div>';
	// role=list keeps the list semantics Safari/VoiceOver drop from a
	// list-style:none + display:inline list.
	echo '<ul role="list">';
	foreach ( $orphaned as $item ) {
		/* translators: %s: model name, e.g. "Mosin-Nagant Rifles" */
		$aria = sprintf( __( '%s model page', 'osa-arsenal' ), $item['label'] );
		echo '<li><a href="' . esc_url( $item['url'] ) . '" aria-label="' . esc_attr( $aria ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>'; // .osa-model-links__group — the block wrapper opened above
}
