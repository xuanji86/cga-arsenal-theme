<?php
/**
 * OSA · Model FAQ — details/summary Q&A + FAQPage JSON-LD.
 *
 * items = [ { "q": "…", "a": "…" }, … ]. The DOM accordion and the schema
 * are built from the SAME filtered list in one pass, so they can never
 * disagree (numberOfItems, order, text). Answers may carry limited markup —
 * wp_kses whitelist p / a[href] / strong / em / br — and the FILTERED string
 * is what both the DOM and the schema receive. Entries missing a question
 * or an answer are dropped from both. No JS: <details> is native.
 *
 * (Google's FAQ rich result is mostly sunset; the schema is a freebie — the
 * long-tail Q&A content itself is the point. Spec §3.3 C3.)
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$heading = trim( (string) ( $attributes['heading'] ?? 'Common Questions' ) );

$allowed = [
	'p'      => [],
	'a'      => [ 'href' => true ],
	'strong' => [],
	'em'     => [],
	'br'     => [],
];

$items = [];
foreach ( (array) ( $attributes['items'] ?? [] ) as $entry ) {
	if ( ! is_array( $entry ) ) {
		continue;
	}
	$q = trim( (string) ( $entry['q'] ?? '' ) );
	$a = wp_kses( (string) ( $entry['a'] ?? '' ), $allowed );
	if ( '' === $q || '' === trim( $a ) ) {
		continue;
	}
	$items[] = [
		'q' => $q,
		'a' => $a,
	];
}

if ( ! $items ) {
	return '';
}

$wrapper = get_block_wrapper_attributes( [ 'class' => 'osa-model-faq' ] );

echo '<section ' . $wrapper . '>';
if ( '' !== $heading ) {
	echo '<h2>' . esc_html( $heading ) . '</h2>';
}

$main_entity = [];
foreach ( $items as $item ) {
	echo '<details class="osa-faq-item"><summary>' . esc_html( $item['q'] ) . '</summary>' . $item['a'] . '</details>';
	$main_entity[] = [
		'@type'          => 'Question',
		'name'           => $item['q'],
		'acceptedAnswer' => [
			'@type' => 'Answer',
			'text'  => $item['a'],
		],
	];
}

echo '<script type="application/ld+json">' . wp_json_encode( [
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => $main_entity,
] ) . '</script>';
echo '</section>';
