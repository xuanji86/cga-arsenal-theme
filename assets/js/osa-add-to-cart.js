/**
 * OSA — single-product AJAX add-to-cart (progressive enhancement).
 *
 * Intercepts the single product add-to-cart form for SIMPLE products and posts
 * to WooCommerce's wc-ajax endpoint so the buyer stays on the page; the header
 * cart badge updates via the returned fragments. Anything uncertain — a variable
 * product, a disabled button, no resolvable product id, or any network/parse
 * error — is left to (or handed back to) a normal form submit, so add-to-cart is
 * never worse than the default reload.
 *
 * No build step, no jQuery. Config comes from wp_localize_script (osaCart).
 *
 * @package OSA_Arsenal
 */
( function () {
	if ( typeof window.osaCart === 'undefined' ) {
		return;
	}
	const cfg = window.osaCart;

	document.querySelectorAll( 'form.cart' ).forEach( initForm );

	function initForm( form ) {
		form.addEventListener( 'submit', function ( e ) {
			const btn = form.querySelector( '.single_add_to_cart_button' );
			// Bail to a normal submit for anything we don't confidently handle.
			if ( ! btn || btn.disabled || btn.classList.contains( 'disabled' ) ) {
				return;
			}
			if ( form.querySelector( '[name="variation_id"]' ) ) {
				return; // variable product — let WooCommerce handle it.
			}
			const pid = ( btn.value || '' ).trim();
			if ( ! pid ) {
				return;
			}

			e.preventDefault();

			const fd = new FormData( form );
			fd.set( 'product_id', pid );
			if ( ! fd.get( 'quantity' ) ) {
				fd.set( 'quantity', '1' );
			}

			btn.classList.add( 'loading' );

			fetch( cfg.ajaxUrl, {
				method: 'POST',
				body: fd,
				credentials: 'same-origin',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( data ) {
					btn.classList.remove( 'loading' );
					if ( ! data ) {
						return fallback( form );
					}
					if ( data.error ) {
						// e.g. needs to choose options / out of stock → let the
						// server-rendered page show the proper notice.
						if ( data.product_url ) {
							window.location = data.product_url;
						} else {
							fallback( form );
						}
						return;
					}
					applyFragments( data.fragments );
					showFeedback( form, btn );
				} )
				.catch( function () {
					btn.classList.remove( 'loading' );
					fallback( form );
				} );
		} );
	}

	// Native submit does NOT re-fire the submit listener, so no loop.
	function fallback( form ) {
		form.submit();
	}

	function applyFragments( fragments ) {
		if ( ! fragments ) {
			return;
		}
		Object.keys( fragments ).forEach( function ( sel ) {
			try {
				document.querySelectorAll( sel ).forEach( function ( el ) {
					const tmp = document.createElement( 'div' );
					tmp.innerHTML = fragments[ sel ];
					const next = tmp.firstElementChild;
					if ( next ) {
						el.replaceWith( next );
					}
				} );
			} catch ( err ) { /* ignore a bad selector */ }
		} );
	}

	function showFeedback( form, btn ) {
		let note = form.querySelector( '.osa-cart-feedback' );
		if ( ! note ) {
			note = document.createElement( 'div' );
			note.className = 'osa-cart-feedback';
			note.setAttribute( 'role', 'status' );
			note.setAttribute( 'aria-live', 'polite' );
			( btn.parentNode || form ).appendChild( note );
		}
		note.innerHTML =
			'<span class="osa-cart-feedback__tick" aria-hidden="true">✓</span> ' +
			esc( cfg.addedText ) +
			' · <a href="' + esc( cfg.cartUrl ) + '">' + esc( cfg.cartText ) + '</a>';
		note.classList.add( 'is-visible' );
	}

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
} )();
