<?php
/**
 * osa/marquee-ticker — the markup contract assets/js/osa-ux.js loops on:
 * .osa-marquee > .osa-marquee__track > one .osa-marquee__list holding the items,
 * separators between them, and exactly one aria-hidden tail separator last (it
 * joins one looping copy to the next; CSS hides it while the strip is still).
 */
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/block-render-stubs.php';

$file = OSA_THEME_DIR . '/blocks/marquee-ticker/render.php';

$html = osa_render_block_file( $file, [ 'items' => [ 'FFL transfer', ' ', 'Consignment open' ], 'sep' => '·', 'invert' => true ] );

osa_assert( false !== strpos( $html, 'osa-marquee--invert' ), 'invert attribute must add the modifier class' );
osa_assert( 1 === substr_count( $html, 'class="osa-marquee__track"' ), 'exactly one track' );
osa_assert( 1 === substr_count( $html, 'class="osa-marquee__list"' ), 'exactly one list (the JS clones it)' );
osa_assert(
	1 === preg_match( '#<div class="osa-marquee__track"><div class="osa-marquee__list">.*</div></div>#s', $html ),
	'the list must be the only child of the track'
);
osa_assert( 1 === substr_count( $html, 'osa-marquee__sep--tail' ), 'exactly one tail separator' );
osa_assert(
	1 === preg_match( '#<span class="osa-marquee__sep osa-marquee__sep--tail" aria-hidden="true">·</span></div></div>#', $html ),
	'the tail separator must be the last thing in the list and aria-hidden'
);
// Blank items are dropped: 2 items → 1 separator between them + the tail.
osa_assert( 2 === substr_count( $html, 'class="osa-marquee__sep' ), 'one separator between two items plus the tail' );
osa_assert( false !== strpos( $html, '<span>FFL transfer</span><span class="osa-marquee__sep">·</span><span>Consignment open</span>' ), 'items and separator in order' );

// No items (and no Customizer fallback) → nothing rendered.
osa_assert( '' === osa_render_block_file( $file, [ 'items' => [] ] ), 'empty strip renders nothing' );

echo "ok\n";
