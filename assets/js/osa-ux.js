/**
 * OSA — small front-end UX enhancements, all progressive:
 *   1. Back-to-top button (shows after scrolling; smooth scroll, reduced-motion
 *      aware). The button is server-rendered in the footer when enabled.
 *   2. Quantity steppers — wrap WooCommerce .qty inputs with − / + buttons.
 *   3. Sticky-header shrink — collapse the utility strip + add a shadow once the
 *      page is scrolled (only when the sticky header is enabled).
 *   4. Motion (only under html.motion, see inc/ux.php): the marquee loops, home
 *      sections below the fold rise in once, and one rule slides between the
 *      desktop nav links. The markup is the finished state; each is additive.
 *
 * No build step, no jQuery. Each feature no-ops if its hook isn't on the page.
 *
 * @package OSA_Arsenal
 */
( function () {
	const MOTION = document.documentElement.classList.contains( 'motion' );

	// Follow a reduced-motion switch made mid-visit: dropping .motion stills every
	// effect at once (all motion CSS is scoped under it); turning it back on resumes.
	const REDUCE = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	if ( REDUCE.addEventListener ) {
		REDUCE.addEventListener( 'change', () => document.documentElement.classList.toggle( 'motion', ! REDUCE.matches ) );
	}

	initBackToTop();
	initQtySteppers();
	initStickyScroll();
	initMarquee();
	initReveal();
	initNavMark();

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

	// Marquee: append aria-hidden copies of the list until the strip can never run
	// short mid-loop, then slide by exactly one list width at a steady 40px/s.
	function initMarquee() {
		if ( ! MOTION ) {
			return;
		}
		document.querySelectorAll( '.osa-marquee' ).forEach( function ( strip ) {
			const track = strip.querySelector( '.osa-marquee__track' );
			const list = track && track.querySelector( '.osa-marquee__list' );
			if ( ! list ) {
				return;
			}
			let width = 0;
			const build = () => {
				track.querySelectorAll( '[data-osa-copy]' ).forEach( ( c ) => c.remove() );
				strip.classList.add( 'is-running' );
				const unit = list.getBoundingClientRect().width;
				if ( ! unit ) {
					strip.classList.remove( 'is-running' );
					return;
				}
				width = strip.clientWidth;
				for ( let i = Math.ceil( width / unit ); i >= 0; i-- ) {
					const copy = list.cloneNode( true );
					copy.setAttribute( 'aria-hidden', 'true' );
					copy.setAttribute( 'data-osa-copy', '' );
					track.appendChild( copy );
				}
				strip.style.setProperty( '--osa-mq-w', unit + 'px' );
				strip.style.setProperty( '--osa-mq-dur', ( unit / 40 ).toFixed( 2 ) + 's' );
			};
			build();
			if ( document.fonts ) {
				document.fonts.ready.then( build );
			}
			// Rebuild when the strip's width changes — including from 0 when it was
			// hidden at load (a window resize alone would never catch that).
			if ( 'ResizeObserver' in window ) {
				new ResizeObserver( debounce( () => {
					if ( strip.clientWidth !== width ) {
						build();
					}
				}, 150 ) ).observe( strip );
			}
			// Pausing: mouse hover and keyboard focus hold it (CSS); Enter/Space or a
			// tap toggle a lasting pause.
			strip.tabIndex = 0;
			strip.addEventListener( 'pointerup', ( e ) => {
				if ( e.pointerType !== 'mouse' ) {
					strip.classList.toggle( 'is-paused' );
				}
			} );
			strip.addEventListener( 'keydown', ( e ) => {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					strip.classList.toggle( 'is-paused' );
				}
			} );
		} );
	}

	// Home only: sections that start below the fold fade up the first time they
	// scroll in; columns inside them follow 90ms apart. Anything already on screen
	// is left alone (no flash, no LCP delay). A jump past a section (anchor link,
	// restored scroll) shows it at once.
	function initReveal() {
		const main = document.querySelector( 'main' );
		if ( ! MOTION || ! main || ! document.body.classList.contains( 'home' ) || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		const fold = window.innerHeight;
		const items = [];
		Array.from( main.children ).forEach( function ( section ) {
			if ( section.getBoundingClientRect().top < fold ) {
				return;
			}
			items.push( section );
			section.querySelectorAll( '.wp-block-columns' ).forEach( function ( cols ) {
				Array.from( cols.children ).forEach( function ( col, i ) {
					col.style.setProperty( '--osa-d', ( 120 + i * 90 ) + 'ms' );
					items.push( col );
				} );
			} );
		} );
		if ( ! items.length ) {
			return;
		}
		// Threshold 0: any pixel past the margin counts. A ratio threshold would
		// never be met by a section many screens tall (a one-column grid on a phone).
		const io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					e.target.classList.add( 'is-in' );
					io.unobserve( e.target );
				}
			} );
			done();
		}, { threshold: 0, rootMargin: '0px 0px -8% 0px' } );
		// Hide without a transition (else each section would fade OUT first),
		// then hand the reveal its transition back.
		items.forEach( ( el ) => el.classList.add( 'no-anim' ) );
		items.forEach( ( el ) => el.setAttribute( 'data-osa-reveal', '' ) );
		void main.offsetWidth;
		items.forEach( function ( el ) {
			el.classList.remove( 'no-anim' );
			io.observe( el );
		} );
		let ticking = false;
		const catchUp = () => {
			ticking = false;
			items.forEach( function ( el ) {
				if ( ! el.classList.contains( 'is-in' ) && el.getBoundingClientRect().bottom < 0 ) {
					el.classList.add( 'no-anim', 'is-in' );
					io.unobserve( el );
				}
			} );
			done();
		};
		const onScroll = () => {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( catchUp );
			}
		};
		// Once everything is in, stop watching.
		function done() {
			if ( items.every( ( el ) => el.classList.contains( 'is-in' ) ) ) {
				io.disconnect();
				window.removeEventListener( 'scroll', onScroll );
			}
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	// Desktop nav: one 2px rule replaces each link's own underline and slides to
	// the hovered/focused link, else the open panel's link, else the current
	// section. It fades in where it first lands instead of sliding from the edge.
	function initNavMark() {
		// Two header shapes: nav v2 (ul.__list > li.__item > a.__link) and the flat
		// nav (nav > a.__link). The rule works on either.
		const nav = document.querySelector( '.osa-main-nav' );
		const list = nav && ( nav.querySelector( '.osa-main-nav__list' ) || nav );
		if ( ! MOTION || ! list ) {
			return;
		}
		const WIDE = window.matchMedia( '(min-width: 901px)' );
		const links = Array.from( list.querySelectorAll( ':scope > .osa-main-nav__item > .osa-main-nav__link, :scope > .osa-main-nav__link' ) );
		if ( ! links.length ) {
			return;
		}
		const mark = document.createElement( 'span' );
		mark.className = 'osa-main-nav__mark';
		mark.setAttribute( 'aria-hidden', 'true' );
		nav.appendChild( mark );
		nav.classList.add( 'is-marked' );

		let pointed = null;
		const linkOf = ( el ) => {
			const item = el && el.closest && el.closest( '.osa-main-nav__item' );
			if ( item && list.contains( item ) ) {
				return item.querySelector( ':scope > .osa-main-nav__link' );
			}
			const a = el && el.closest && el.closest( '.osa-main-nav__link' );
			return a && links.includes( a ) ? a : null;
		};
		const resting = () => list.querySelector( ':scope > .osa-main-nav__item.is-open > .osa-main-nav__link' )
			|| links.find( ( a ) => a.classList.contains( 'is-active' ) )
			|| null;
		const place = () => {
			const link = pointed || resting();
			const base = mark.offsetParent;
			if ( ! WIDE.matches || ! base ) {
				return;
			}
			if ( ! link ) {
				// Write the class only when it changes: any write (even removing an
				// absent token) is a mutation record the observer below would see.
				if ( mark.classList.contains( 'is-on' ) ) {
					mark.classList.remove( 'is-on' );
				}
				return;
			}
			const b = base.getBoundingClientRect();
			const r = link.getBoundingClientRect();
			const fresh = ! mark.classList.contains( 'is-on' );
			if ( fresh ) {
				mark.classList.add( 'is-placing' );
			}
			mark.style.setProperty( '--osa-mk-x', ( r.left - b.left ) + 'px' );
			mark.style.setProperty( '--osa-mk-y', ( r.bottom - b.top - 2 ) + 'px' );
			mark.style.setProperty( '--osa-mk-w', r.width + 'px' );
			if ( fresh ) {
				void mark.offsetWidth; // commit the position before fading in
				mark.classList.remove( 'is-placing' );
				mark.classList.add( 'is-on' );
			}
		};

		list.addEventListener( 'pointerover', ( e ) => {
			const link = linkOf( e.target );
			if ( link && link !== pointed ) {
				pointed = link;
				place();
			}
		} );
		list.addEventListener( 'pointerleave', () => {
			pointed = null;
			place();
		} );
		list.addEventListener( 'focusin', ( e ) => {
			pointed = linkOf( e.target );
			place();
		} );
		list.addEventListener( 'focusout', ( e ) => {
			if ( ! list.contains( e.relatedTarget ) ) {
				pointed = null;
				place();
			}
		} );
		// mobile-nav.js opens/closes panels by toggling .is-open on the items.
		// On the flat nav the rule itself lives inside the observed tree: ignore its
		// own class changes, or each place() would re-trigger the observer.
		new MutationObserver( ( records ) => {
			if ( records.some( ( r ) => r.target !== mark ) ) {
				place();
			}
		} ).observe( list, { attributes: true, attributeFilter: [ 'class' ], subtree: true } );
		if ( 'ResizeObserver' in window ) {
			new ResizeObserver( place ).observe( list );
		}
		WIDE.addEventListener( 'change', place );
		if ( document.fonts ) {
			document.fonts.ready.then( place );
		}
		place();
	}

	function debounce( fn, ms ) {
		let t = 0;
		return function () {
			window.clearTimeout( t );
			t = window.setTimeout( fn, ms );
		};
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
