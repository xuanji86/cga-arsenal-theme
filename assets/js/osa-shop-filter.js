/**
 * OSA — mobile collapse for the catalog filter sidebar.
 *
 * The fcpf/product-filter-sidebar block (ffl-core plugin) renders a 7-section
 * filter form that, below 900px, stacks ABOVE the product grid fully expanded —
 * pushing every product off-screen. This injects a "Filters (n)" toggle that
 * collapses the form on mobile. Desktop is untouched: the toggle is display:none
 * and the form always shows (the collapse CSS lives inside a max-width query).
 *
 *  - The toggle is JS-injected, so the collapse CSS is gated on the
 *    `is-collapsible` marker class this script adds to the <aside>. Without JS
 *    the marker is absent, so the form stays fully visible (today's behavior) —
 *    it is never hidden-with-no-way-to-open.
 *  - The sidebar's own view.js auto-submits (full page reload) on every checkbox
 *    change. sessionStorage remembers the open state so the panel re-expands
 *    after that reload, letting the user keep selecting without re-opening.
 *
 * No build step — plain ES2017+, no dependencies.
 *
 * @package OSA_Arsenal
 */
( function () {
	const KEY = 'osaFilterOpen';
	const sidebars = document.querySelectorAll( '.osa-filter-sidebar' );
	sidebars.forEach( initSidebar );

	function initSidebar( sidebar ) {
		const form = sidebar.querySelector( '.osa-filter-form' );
		if ( ! form ) {
			return;
		}
		if ( ! form.id ) {
			form.id = 'osa-filter-form';
		}

		const count  = activeCount( form );
		const toggle = document.createElement( 'button' );
		toggle.type      = 'button';
		toggle.className  = 'osa-filter-toggle';
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.setAttribute( 'aria-controls', form.id );
		toggle.innerHTML =
			'<span class="osa-filter-toggle__label">Filters</span>' +
			'<span class="osa-filter-toggle__count"' + ( count ? '' : ' hidden' ) + '>(' + count + ')</span>' +
			'<span class="osa-filter-toggle__chevron" aria-hidden="true">▾</span>';

		sidebar.insertBefore( toggle, form );
		sidebar.classList.add( 'is-collapsible' );

		// Restore the open state across the auto-submit reload. Only meaningful
		// on mobile — on desktop the toggle is display:none, so .is-open is inert.
		let open = false;
		try {
			open = sessionStorage.getItem( KEY ) === '1';
		} catch ( e ) {}
		setOpen( sidebar, toggle, open );

		toggle.addEventListener( 'click', function () {
			const next = ! sidebar.classList.contains( 'is-open' );
			setOpen( sidebar, toggle, next );
			try {
				sessionStorage.setItem( KEY, next ? '1' : '0' );
			} catch ( e ) {}
		} );
	}

	function setOpen( sidebar, toggle, open ) {
		sidebar.classList.toggle( 'is-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}

	/*
	 * Active filters = checked checkboxes + 1 if the price range was narrowed
	 * from the catalog bounds. Read straight from the server-rendered DOM, so it
	 * mirrors the toolbar chips fcpf renders for the same request.
	 */
	function activeCount( form ) {
		let n = form.querySelectorAll( 'input[type=checkbox]:checked' ).length;
		const slider = form.querySelector( '[data-fcpf-price]' );
		if ( slider ) {
			const min   = parseInt( slider.dataset.min, 10 );
			const max   = parseInt( slider.dataset.max, 10 );
			const minI  = slider.querySelector( '[data-fcpf-price-input="min"]' );
			const maxI  = slider.querySelector( '[data-fcpf-price-input="max"]' );
			const curMin = minI ? parseInt( minI.value, 10 ) : min;
			const curMax = maxI ? parseInt( maxI.value, 10 ) : max;
			if ( curMin > min || curMax < max ) {
				n += 1;
			}
		}
		return n;
	}
} )();
