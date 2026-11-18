<?php
/**
 * OSA — front-end UX: enqueue assets/js/osa-ux.js (back-to-top, quantity
 * steppers, sticky-header shrink) and render the back-to-top button.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/*
 * Motion gate: before first paint, tag <html> with .motion unless the visitor
 * asked for reduced motion. Every animation in osa-base.css is scoped under
 * .motion, and the markup is always the finished state, so with no JS or with
 * reduced motion the page simply renders still.
 */
add_action( 'wp_head', function () {
	echo "<script>if(!matchMedia('(prefers-reduced-motion: reduce)').matches)document.documentElement.classList.add('motion');</script>\n";
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	$rel  = '/assets/js/osa-ux.js';
	$path = OSA_THEME_DIR . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script( 'osa-ux', OSA_THEME_URI . $rel, [], (string) filemtime( $path ), true );
}, 20 );

add_action( 'wp_footer', function () {
	if ( ! get_theme_mod( 'osa_back_to_top', true ) ) {
		return;
	}
	printf(
		'<button type="button" class="osa-to-top" aria-label="%s"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg></button>',
		esc_attr__( 'Back to top', 'osa-arsenal' )
	);
} );
