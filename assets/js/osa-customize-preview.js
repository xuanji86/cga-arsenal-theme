/**
 * CGA — Customizer live preview.
 *
 * Binds the most-edited text strings to postMessage so they update instantly
 * in the preview frame instead of triggering a full reload. Anything without
 * a clean single target (multi-field cells, {token} strings, link controls)
 * stays on 'refresh' — the list in inc/customizer.php must contain exactly
 * the ids bound here, or a flipped setting would preview as a no-op.
 *
 * @package OSA_Arsenal
 */
( function ( wp ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}
	const api = wp.customize;

	const bind = ( id, resolve, format ) => {
		api( id, function ( value ) {
			value.bind( function ( next ) {
				const el = typeof resolve === 'string' ? document.querySelector( resolve ) : resolve();
				if ( el ) {
					el.textContent = format ? format( next ) : next;
				}
			} );
		} );
	};

	// Section heading inside the .home-block that contains `containerSel`.
	const sectionTitle = ( containerSel ) => () => {
		const c = document.querySelector( containerSel );
		return c ? c.closest( 'section' )?.querySelector( '.cga-section__title' ) : null;
	};

	bind( 'osa_header_strip', '.osa-utility-strip__msg' );

	bind( 'cga_hero_eyebrow', '.home-hero .cga-eyebrow' );
	bind( 'cga_hero_lede', '.home-hero .cga-lede' );
	// Headline keeps its line breaks: escape each line, join with <br>.
	api( 'cga_hero_headline', function ( value ) {
		value.bind( function ( next ) {
			const el = document.querySelector( '.home-hero .cga-h1' );
			if ( el ) {
				const esc = ( s ) => s.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
				el.innerHTML = String( next ).split( /\r?\n/ ).map( esc ).join( '<br />' );
			}
		} );
	} );

	bind( 'cga_rail_heading', sectionTitle( '.cga-rail' ) );
	bind( 'cga_cats_heading', sectionTitle( '.cga-cats' ) );
	bind( 'cga_steps_heading', sectionTitle( '.cga-steps' ) );
	bind( 'cga_svc_heading', sectionTitle( '.cga-svc' ) );
	bind( 'cga_train_heading', sectionTitle( '.home-train' ) );
	bind( 'cga_store_heading', sectionTitle( '.cga-store' ) );

	bind( 'cga_band_eyebrow', '.cga-band__eyebrow' );
	bind( 'cga_band_title', '.cga-band__title' );
	bind( 'cga_band_body', '.cga-band__body' );
	bind( 'cga_store_blurb', '.cga-store .cga-lede' );
} )( window.wp );
