/**
 * OSA — search overlay open/close.
 *
 *   - Triggered by any element with [data-osa-search] (the header Search btn).
 *   - Closes on overlay backdrop click, the close button, or Esc.
 *   - Focuses the input when opened.
 */
( function () {
	const overlay = document.getElementById( 'osa-search-overlay' );
	if ( ! overlay ) return;
	const input = overlay.querySelector( '.osa-search-overlay__input' );

	let opener = null;

	function open() {
		opener = document.activeElement;
		overlay.hidden = false;
		overlay.setAttribute( 'aria-hidden', 'false' );
		document.documentElement.style.overflow = 'hidden';
		// Defer focus so the browser sees the element become visible first.
		requestAnimationFrame( () => input && input.focus() );
	}

	function close() {
		overlay.hidden = true;
		overlay.setAttribute( 'aria-hidden', 'true' );
		document.documentElement.style.overflow = '';
		if ( input ) input.value = '';
		// Hand focus back to whatever opened the overlay (dialog semantics).
		if ( opener && typeof opener.focus === 'function' ) opener.focus();
		opener = null;
	}

	document.addEventListener( 'click', ( e ) => {
		const trigger = e.target.closest( '[data-osa-search]' );
		if ( trigger ) {
			e.preventDefault();
			open();
			return;
		}
		if ( e.target.closest( '[data-osa-search-close]' ) ) {
			e.preventDefault();
			close();
			return;
		}
		// Click on the overlay backdrop (not the form) closes it.
		if ( e.target === overlay ) {
			close();
		}
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( overlay.hidden ) return;
		if ( e.key === 'Escape' ) {
			close();
			return;
		}
		// Keep Tab cycling inside the dialog (input, hint links, close button)
		// — aria-modal promises focus containment.
		if ( e.key === 'Tab' ) {
			const focusables = overlay.querySelectorAll( 'a[href], button, input' );
			if ( ! focusables.length ) return;
			const first = focusables[ 0 ];
			const last = focusables[ focusables.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	} );
} )();
