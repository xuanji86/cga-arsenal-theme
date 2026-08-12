/**
 * OSA — Customizer live preview.
 *
 * Binds the main editable text strings to postMessage so they update instantly
 * in the preview frame instead of triggering a full reload. Settings without a
 * clean single target (e.g. the floor CTA with its {count} token) stay on
 * 'refresh' and are simply not bound here.
 *
 * @package OSA_Arsenal
 */
( function ( wp ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}
	const api = wp.customize;

	const bind = ( id, selector, format ) => {
		api( id, function ( value ) {
			value.bind( function ( next ) {
				const el = document.querySelector( selector );
				if ( el ) {
					el.textContent = format ? format( next ) : next;
				}
			} );
		} );
	};

	bind( 'osa_hero_headline', '.osa-hero-headline' );
	bind( 'osa_hero_subheadline', '.osa-hero-sub' );
	bind( 'osa_header_strip', '.osa-utility-strip__msg' );
	bind( 'osa_floor_heading', '.osa-floor-heading' );
	bind( 'osa_story_label', '.osa-story-label', function ( v ) { return v.toUpperCase(); } );
	bind( 'osa_story_quote', '.osa-story-quote', function ( v ) {
		return '“' + v.replace( /^["“”]+|["“”]+$/g, '' ) + '”';
	} );
	bind( 'osa_story_attribution', '.osa-story-attribution' );
} )( window.wp );
