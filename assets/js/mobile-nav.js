/**
 * OSA — mobile nav drawer open/close.
 *
 *   - Hamburger button [data-osa-nav-toggle] opens .osa-main-nav as off-canvas drawer.
 *   - Closes on backdrop click [data-osa-nav-close], Esc key, or any nav link click.
 *   - State lives on <html> class `osa-nav-open` so CSS can lock body scroll.
 */
( function () {
	const toggle = document.querySelector( '[data-osa-nav-toggle]' );
	if ( ! toggle ) return;

	const html = document.documentElement;
	const backdrop = document.querySelector( '.osa-nav-backdrop' );
	const nav = document.querySelector( '.osa-main-nav' );

	function open() {
		html.classList.add( 'osa-nav-open' );
		toggle.setAttribute( 'aria-expanded', 'true' );
		if ( backdrop ) backdrop.hidden = false;
	}

	function close() {
		html.classList.remove( 'osa-nav-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		if ( backdrop ) backdrop.hidden = true;
		// If focus is stranded inside the now off-canvas drawer, hand it back
		// to the hamburger so keyboard users aren't left on a hidden element.
		if ( nav && nav.contains( document.activeElement ) ) toggle.focus();
	}

	document.addEventListener( 'click', ( e ) => {
		if ( e.target.closest( '[data-osa-nav-toggle]' ) ) {
			e.preventDefault();
			if ( html.classList.contains( 'osa-nav-open' ) ) close();
			else open();
			return;
		}
		if ( e.target.closest( '[data-osa-nav-close]' ) ) {
			e.preventDefault();
			close();
			return;
		}
		if ( html.classList.contains( 'osa-nav-open' ) && e.target.closest( '.osa-main-nav a' ) ) {
			close();
		}
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && html.classList.contains( 'osa-nav-open' ) ) {
			close();
		}
	} );

	// Close drawer if viewport grows past mobile breakpoint while open.
	const mql = window.matchMedia( '(min-width: 901px)' );
	mql.addEventListener( 'change', ( ev ) => {
		if ( ev.matches && html.classList.contains( 'osa-nav-open' ) ) close();
	} );
} )();
