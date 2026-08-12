<?php
/**
 * OSA · Model Page Link — the product page's backlink up to its /firearms/ landing.
 *
 * Measured gap (content-pack §3.4, curl against prod 2026-08-09): product pages
 * were the one internal-link source the model-landing project never wired.
 * `/product/m39-7-62x54r/` linked its buyer's guide but not the Mosin hub;
 * `/product/chinese-type-56-sks-rifle/` linked neither. Everything else — the
 * homepage strip, the category chips, the /firearms/ index, guide↔hub both
 * ways — was already in place.
 *
 * The product finds its landing through its own pa_model term slugs, matched
 * against the row's filter-slug field in the `osa_model_links` theme mod — the
 * SAME slug set the /firearms/ hub card uses to filter the shop. So a product
 * and its hub card can never disagree about which landing owns a model, and no
 * slug→URL table enters the code: adding a landing page stays the zero-code
 * Customizer step (docs/model-landing-template.md §5).
 *
 * No match — a model with no landing yet, a non-firearm product, an emptied
 * Customizer list — renders the empty string. An invented URL would be a 404 in
 * the crawl graph, which is worse than no link.
 *
 * Anchor text is `More <model> <noun> in stock →`, built from the row Label so
 * the page-params §0 v2 model wording is inherited rather than remembered. It
 * deliberately shares no phrasing with the two anchors already pointing at the
 * same landing from elsewhere — the chips (`SKS Rifles`) and the guide card
 * (`New to the SKS? Read our buyer's guide →`).
 *
 * Seated in templates/single-product.html, so it never passes through
 * the_content — but it is still one <p> of inline children, i.e. wpautop-safe
 * if it is ever dropped into content (tests/test-model-page-link.php pins that).
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_singular( 'product' ) || ! function_exists( 'osa_model_link_for_product' ) ) {
	return '';
}

$item = osa_model_link_for_product( get_queried_object_id() );
if ( ! $item ) {
	return '';
}

list( $model, $noun ) = osa_model_label_parts( $item['label'] );

if ( '' !== $noun ) {
	/* translators: 1: model name, e.g. "Mosin-Nagant". 2: category noun, e.g. "rifles". */
	$text = sprintf( __( 'More %1$s %2$s in stock →', 'osa-arsenal' ), $model, $noun );
} else {
	// A label with no category noun of its own ("West German SIG P226 & P228")
	// keeps every word rather than being forced into a noun it never had. Still
	// $model, not $item['label']: with no noun to split off the two are the same
	// text, but only $model has been through the normalizer, so both branches
	// stay one source (review r1 nit-1).
	/* translators: %s: model name, e.g. "West German SIG P226 & P228". */
	$text = sprintf( __( 'More %s in stock →', 'osa-arsenal' ), $model );
}

echo '<p ' . get_block_wrapper_attributes( [ 'class' => 'osa-model-backlink' ] ) . '>';
echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $text ) . '</a>';
echo '</p>';
