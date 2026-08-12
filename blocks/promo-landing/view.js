/**
 * OSA · Independence Day Landing — countdown + load-more.
 * Enqueued by block.json only where the block renders.
 */
( function () {
	'use strict';

	// Countdown to the promo end epoch (site-timezone anchored server-side).
	var cd = document.querySelector( '[data-ind-countdown]' );
	if ( cd ) {
		var end = parseInt( cd.getAttribute( 'data-ind-countdown' ), 10 ) * 1000;
		var num = {
			d: cd.querySelector( '[data-ind-num="d"]' ),
			h: cd.querySelector( '[data-ind-num="h"]' ),
			m: cd.querySelector( '[data-ind-num="m"]' ),
			s: cd.querySelector( '[data-ind-num="s"]' )
		};
		var pad = function ( n ) {
			return ( n < 10 ? '0' : '' ) + n;
		};
		var tick = function () {
			var left = Math.max( 0, Math.floor( ( end - Date.now() ) / 1000 ) );
			if ( num.d ) { num.d.textContent = pad( Math.floor( left / 86400 ) ); }
			if ( num.h ) { num.h.textContent = pad( Math.floor( ( left % 86400 ) / 3600 ) ); }
			if ( num.m ) { num.m.textContent = pad( Math.floor( ( left % 3600 ) / 60 ) ); }
			if ( num.s ) { num.s.textContent = pad( left % 60 ); }
		};
		tick();
		setInterval( tick, 1000 );
	}

	// Load more — every card is in the DOM (CSS clamps to 8); reveal +8 per click.
	var btn = document.querySelector( '[data-ind-more]' );
	var grid = document.querySelector( '.ind-catalog-grid' );
	if ( btn && grid ) {
		var section = document.querySelector( '.ind-collection--clamped' );
		var shownEl = document.querySelector( '[data-ind-shown]' );
		var step = parseInt( btn.getAttribute( 'data-ind-more' ), 10 ) || 8;
		var cards = Array.prototype.slice.call( grid.children );
		var shown = Math.min( step, cards.length );

		btn.addEventListener( 'click', function () {
			var next = Math.min( shown + step, cards.length );
			for ( var i = shown; i < next; i++ ) {
				cards[ i ].style.display = 'flex';
			}
			shown = next;
			if ( shownEl ) {
				shownEl.textContent = String( shown );
			}
			if ( shown >= cards.length ) {
				if ( section ) {
					section.classList.remove( 'ind-collection--clamped' );
				}
				var more = btn.closest( '.ind-loadmore' );
				if ( more ) {
					more.style.display = 'none';
				}
			}
		} );
	}
} )();
