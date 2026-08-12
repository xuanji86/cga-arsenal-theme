/**
 * OSA — small front-end UX enhancements, all progressive:
 *   1. Back-to-top button (shows after scrolling; smooth scroll, reduced-motion
 *      aware). The button is server-rendered in the footer when enabled.
 *   2. Quantity steppers — wrap WooCommerce .qty inputs with − / + buttons.
 *   3. Sticky-header shrink — collapse the utility strip + add a shadow once the
 *      page is scrolled (only when the sticky header is enabled).
 *
 * No build step, no jQuery. Each feature no-ops if its hook isn't on the page.
 *
 * @package OSA_Arsenal
 */
( function () {
	initBackToTop();
	initQtySteppers();
	initStickyScroll();

	function initBackToTop() {
		const btn = document.querySelector( '.osa-to-top' );
		if ( ! btn ) {
			return;
		}
		const onScroll = () => btn.classList.toggle( 'is-visible', window.scrollY > 600 );
		onScroll();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		btn.addEventListener( 'click', () => {
			const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );
		} );
	}

	function initQtySteppers() {
		document.querySelectorAll( '.quantity input.qty' ).forEach( function ( input ) {
			const wrap = input.closest( '.quantity' );
			if ( ! wrap || wrap.classList.contains( 'osa-qty' ) ) {
				return;
			}
			wrap.classList.add( 'osa-qty' );

			const num = ( v, fallback ) => {
				const n = parseFloat( v );
				return isNaN( n ) ? fallback : n;
			};
			const step = () => num( input.getAttribute( 'step' ), 1 ) || 1;
			const min  = () => num( input.getAttribute( 'min' ), 0 );
			const maxAttr = input.getAttribute( 'max' );
			const max  = () => ( maxAttr === '' || maxAttr === null ) ? Infinity : num( maxAttr, Infinity );

			// Skip locked quantities (e.g. unique one-off items where min === max);
			// a stepper that can't move just looks broken.
			if ( max() <= min() ) {
				wrap.classList.remove( 'osa-qty' );
				return;
			}

			const bump = ( dir ) => {
				const next = ( num( input.value, 0 ) ) + dir * step();
				input.value = Math.min( max(), Math.max( min(), next ) );
				input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			};

			const dec = mkBtn( '−', 'osa-qty__btn osa-qty__dec' );
			const inc = mkBtn( '+', 'osa-qty__btn osa-qty__inc' );
			dec.addEventListener( 'click', () => bump( -1 ) );
			inc.addEventListener( 'click', () => bump( 1 ) );
			input.insertAdjacentElement( 'beforebegin', dec );
			input.insertAdjacentElement( 'afterend', inc );
		} );
	}

	function initStickyScroll() {
		if ( ! document.body.classList.contains( 'osa-has-sticky-header' ) ) {
			return;
		}
		let ticking = false;
		const update = () => {
			document.body.classList.toggle( 'osa-header-scrolled', window.scrollY > 80 );
			ticking = false;
		};
		window.addEventListener( 'scroll', () => {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );
		update();
	}

	function mkBtn( label, cls ) {
		const b = document.createElement( 'button' );
		b.type = 'button';
		b.className = cls;
		b.tabIndex = -1;
		b.setAttribute( 'aria-hidden', 'true' );
		b.textContent = label;
		return b;
	}
} )();
