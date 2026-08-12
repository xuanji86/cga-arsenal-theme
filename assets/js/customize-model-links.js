/**
 * OSA — "Shop by Model" repeater control (Customizer controls pane).
 *
 * Hydrates every `osa-model-links` control (see inc/class-osa-model-links-control.php)
 * into a repeater UI: image thumbnail + WP Media Library picker, Label + URL
 * + Filter slugs fields, drag/keyboard reorder, add/remove. Rows are serialized
 * back to the bound setting as the SAME newline `Label|URL|ImageURL|FilterSlugs`
 * string the theme parses, so blocks/model-links/render.php +
 * osa_model_links_items() keep reading it unchanged (trailing empty fields are
 * dropped, so slug-less rows stay byte-identical to the legacy format).
 *
 * DOM order is the source of truth: every mutation re-collects the rows in
 * document order, serializes, and pushes to the setting (marks it dirty +
 * refreshes the preview). Rows missing a Label or URL are dropped on serialize,
 * matching the PHP parser's skip rule, so an in-progress empty row simply
 * doesn't persist until it's filled.
 *
 * WP-bundled deps only: jquery, jquery-ui-sortable, customize-controls, and
 * wp.media (via wp_enqueue_media()). No external libraries.
 *
 * @package OSA_Arsenal
 */
( function ( $, api, wp ) {
	'use strict';

	if ( ! api || ! $ ) {
		return;
	}

	var l10n = window.osaModelLinksL10n || {};
	function t( key, fallback ) {
		return ( null != l10n[ key ] && '' !== l10n[ key ] ) ? l10n[ key ] : fallback;
	}

	/**
	 * Parse the stored `Label|URL|ImageURL|FilterSlugs` newline string into row
	 * objects. Mirrors osa_model_links_items(): split on "|", trim each part.
	 * Fields are positional — the 4th (slugs) rides after the image, so a row
	 * with slugs but no image is `Label|URL||slugs`. A wholly blank line is
	 * dropped; a partially filled line is kept so the owner can see and finish
	 * it (it's dropped again on serialize if still incomplete).
	 *
	 * @param {string} raw
	 * @return {Array<{label:string,url:string,img:string,slugs:string}>}
	 */
	function parse( raw ) {
		var rows = [];
		String( null == raw ? '' : raw ).split( /\r?\n/ ).forEach( function ( line ) {
			var parts = line.split( '|' ).map( function ( s ) { return s.trim(); } );
			var label = parts[0] || '';
			var url   = parts[1] || '';
			var img   = parts[2] || '';
			var slugs = parts[3] || '';
			if ( '' === label && '' === url && '' === img && '' === slugs ) {
				return;
			}
			rows.push( { label: label, url: url, img: img, slugs: slugs } );
		} );
		return rows;
	}

	/**
	 * Serialize rows back to the exact storage format. Rows missing a Label or
	 * URL are dropped (parser parity). Trailing empty fields are omitted, so a
	 * row with neither image nor slugs round-trips as `Label|URL` (no trailing
	 * pipe) — byte-identical to the theme's code default; a row with slugs but
	 * no image becomes `Label|URL||slugs` (empty image placeholder so slugs
	 * keep position 4). Existing values, including image-URL query strings
	 * (e.g. `?v=2`), are preserved verbatim: nothing is re-encoded, only
	 * trimmed the same way the parser trims.
	 *
	 * @param {Array<{label:string,url:string,img:string,slugs:string}>} rows
	 * @return {string}
	 */
	function serialize( rows ) {
		var lines = [];
		rows.forEach( function ( r ) {
			var label = ( r.label || '' ).trim();
			var url   = ( r.url || '' ).trim();
			var img   = ( r.img || '' ).trim();
			var slugs = ( r.slugs || '' ).trim();
			if ( '' === label || '' === url ) {
				return;
			}
			var line = label + '|' + url;
			if ( '' !== img || '' !== slugs ) {
				line += '|' + img; // may be '' → the `||` before slugs
				if ( '' !== slugs ) {
					line += '|' + slugs;
				}
			}
			lines.push( line );
		} );
		return lines.join( '\n' );
	}

	/**
	 * Trailing debounce so a burst of keystrokes coalesces into one setting
	 * update (and one refresh-transport preview reload) once the owner pauses.
	 *
	 * @param {Function} fn
	 * @param {number}   wait Milliseconds.
	 * @return {Function}
	 */
	function debounce( fn, wait ) {
		var timer = null;
		return function () {
			if ( timer ) {
				clearTimeout( timer );
			}
			timer = setTimeout( fn, wait );
		};
	}

	/**
	 * Hydrate a single control instance.
	 *
	 * @param {object} control wp.customize.Control
	 */
	function initControl( control ) {
		var $root = control.container.find( '.osa-mlc' ).first();
		if ( ! $root.length || $root.data( 'osaMlcInit' ) ) {
			return;
		}
		$root.data( 'osaMlcInit', true );

		var $rows   = $root.find( '.osa-mlc__rows' ).first();
		var setting = control.setting || api( control.id );

		// Collect rows in DOM order and push the serialized value to the setting.
		function push() {
			if ( ! setting ) {
				return;
			}
			var rows = [];
			$rows.children( '.osa-mlc-row' ).each( function () {
				var $r = $( this );
				rows.push( {
					label: $r.find( '.osa-mlc-row__label input' ).val() || '',
					url:   $r.find( '.osa-mlc-row__url input' ).val() || '',
					img:   $r.data( 'osaImg' ) || '',
					slugs: $r.find( '.osa-mlc-row__slugs input' ).val() || ''
				} );
			} );
			setting.set( serialize( rows ) );
		}

		// Text-field edits debounce; reorder/add/remove/image push immediately.
		var pushDebounced = debounce( push, 250 );

		function refreshSortable() {
			if ( $rows.data( 'ui-sortable' ) ) {
				$rows.sortable( 'refresh' );
			}
		}

		// Reflect the current image state onto a row: thumbnail, pick-button
		// label ("Select image" vs "Change"), and clear-button visibility.
		function setThumb( $row, url ) {
			var $thumb = $row.find( '.osa-mlc-row__thumb' );
			var $pick  = $row.find( '.osa-mlc-row__pick' );
			var $clear = $row.find( '.osa-mlc-row__clear' );
			$row.data( 'osaImg', url || '' );
			if ( url ) {
				$thumb.html( $( '<img>', { src: url, alt: '' } ) ).removeClass( 'is-empty' );
				$pick.text( t( 'changeImage', 'Change' ) );
				$clear.show();
			} else {
				$thumb.empty().addClass( 'is-empty' )
					.append( $( '<span>', { 'class': 'dashicons dashicons-format-image', 'aria-hidden': 'true' } ) );
				$pick.text( t( 'selectImage', 'Select image' ) );
				$clear.hide();
			}
		}

		// Open the WP Media Library and store the chosen attachment's URL.
		function pickImage( $row ) {
			if ( ! wp || ! wp.media ) {
				return;
			}
			var frame = wp.media( {
				title: t( 'mediaTitle', 'Select model image' ),
				button: { text: t( 'mediaButton', 'Use this image' ) },
				library: { type: 'image' },
				multiple: false
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				setThumb( $row, att && att.url ? att.url : '' );
				push();
			} );
			frame.open();
		}

		// Build one repeater row and wire its events.
		function buildRow( data ) {
			data = data || { label: '', url: '', img: '', slugs: '' };
			var $row = $( '<li>', { 'class': 'osa-mlc-row', role: 'listitem' } );

			// Drag / keyboard-reorder handle.
			var $handle = $( '<button>', {
				type: 'button',
				'class': 'osa-mlc-row__handle',
				'aria-label': t( 'reorder', 'Reorder (drag, or focus and use the arrow keys)' ),
				title: t( 'reorder', 'Reorder (drag, or focus and use the arrow keys)' )
			} ).append( $( '<span>', { 'class': 'dashicons dashicons-menu', 'aria-hidden': 'true' } ) );

			$handle.on( 'keydown', function ( e ) {
				var up   = ( 'ArrowUp' === e.key || 38 === e.keyCode );
				var down = ( 'ArrowDown' === e.key || 40 === e.keyCode );
				if ( ! up && ! down ) {
					return;
				}
				e.preventDefault();
				var $sib = up ? $row.prev( '.osa-mlc-row' ) : $row.next( '.osa-mlc-row' );
				if ( ! $sib.length ) {
					return;
				}
				if ( up ) {
					$row.insertBefore( $sib );
				} else {
					$row.insertAfter( $sib );
				}
				$handle.trigger( 'focus' );
				refreshSortable();
				push();
			} );

			// Image cell: thumbnail + pick + clear.
			var $media = $( '<div>', { 'class': 'osa-mlc-row__media' } );
			var $thumb = $( '<span>', { 'class': 'osa-mlc-row__thumb is-empty' } );
			var $pick  = $( '<button>', { type: 'button', 'class': 'button osa-mlc-row__pick' } );
			var $clear = $( '<button>', {
				type: 'button',
				'class': 'osa-mlc-row__clear',
				'aria-label': t( 'removeImage', 'Remove image' ),
				title: t( 'removeImage', 'Remove image' )
			} ).append( $( '<span>', { 'class': 'dashicons dashicons-no-alt', 'aria-hidden': 'true' } ) );

			$pick.on( 'click', function ( e ) { e.preventDefault(); pickImage( $row ); } );
			$clear.on( 'click', function ( e ) { e.preventDefault(); setThumb( $row, '' ); push(); } );
			$media.append( $thumb, $( '<span>', { 'class': 'osa-mlc-row__media-btns' } ).append( $pick, $clear ) );

			// Label + URL fields (wrapping <label>s associate text with input).
			var $fields = $( '<div>', { 'class': 'osa-mlc-row__fields' } );
			var $labelWrap = $( '<label>', { 'class': 'osa-mlc-row__label' } )
				.append( $( '<span>', { 'class': 'osa-mlc-row__field-name', text: t( 'label', 'Label' ) } ) )
				.append( $( '<input>', { type: 'text', value: data.label || '', placeholder: t( 'labelPlaceholder', 'e.g. SKS Rifles' ) } ) );
			var $urlWrap = $( '<label>', { 'class': 'osa-mlc-row__url' } )
				.append( $( '<span>', { 'class': 'osa-mlc-row__field-name', text: t( 'url', 'URL' ) } ) )
				.append( $( '<input>', { type: 'text', value: data.url || '', placeholder: t( 'urlPlaceholder', '/firearms/model-slug/' ) } ) );
			// Optional 4th field: pa_model term slugs (CSV) for the hub filter jump.
			var $slugsWrap = $( '<label>', { 'class': 'osa-mlc-row__slugs' } )
				.append( $( '<span>', { 'class': 'osa-mlc-row__field-name', text: t( 'filterSlugs', 'Filter slugs' ) } ) )
				.append( $( '<input>', { type: 'text', value: data.slugs || '', placeholder: t( 'slugsPlaceholder', 'e.g. p226,p228 — optional' ) } ) );
			$fields.append( $labelWrap, $urlWrap, $slugsWrap );
			// Debounce keystrokes; flush immediately on blur/enter (change).
			$fields.find( 'input' ).on( 'input', pushDebounced ).on( 'change', push );

			// Remove row.
			var $remove = $( '<button>', {
				type: 'button',
				'class': 'osa-mlc-row__remove',
				'aria-label': t( 'remove', 'Remove model' ),
				title: t( 'remove', 'Remove model' )
			} ).append( $( '<span>', { 'class': 'dashicons dashicons-trash', 'aria-hidden': 'true' } ) );
			$remove.on( 'click', function ( e ) {
				e.preventDefault();
				$row.remove();
				refreshSortable();
				push();
			} );

			// Stack the image strip ABOVE the Label/URL fields in a full-width
			// body column, so the Select/Change + ✕ controls never underlap the
			// fields at the narrow (~450px) Customizer pane width.
			var $body = $( '<div>', { 'class': 'osa-mlc-row__body' } ).append( $media, $fields );
			$row.append( $handle, $body, $remove );
			setThumb( $row, data.img || '' );
			return $row;
		}

		function addRow( data, focus ) {
			var $row = buildRow( data );
			$rows.append( $row );
			refreshSortable();
			if ( focus ) {
				$row.find( '.osa-mlc-row__label input' ).trigger( 'focus' );
			}
			return $row;
		}

		// Hydrate from the current setting value.
		parse( setting ? setting.get() : '' ).forEach( function ( d ) { addRow( d, false ); } );

		// "Add model" — appends an empty row (serialize drops it until filled).
		$root.find( '.osa-mlc__add' ).on( 'click', function ( e ) {
			e.preventDefault();
			addRow( { label: '', url: '', img: '', slugs: '' }, true );
		} );

		// Drag reorder (jquery-ui-sortable, WP-bundled).
		if ( $.fn.sortable ) {
			$rows.sortable( {
				handle: '.osa-mlc-row__handle',
				items: '> .osa-mlc-row',
				axis: 'y',
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				placeholder: 'osa-mlc-row--placeholder',
				update: function () { push(); }
			} );
		}
	}

	// Hydrate every osa-model-links control once its content is embedded.
	api.bind( 'ready', function () {
		api.control.each( function ( control ) {
			if ( ! control.params || 'osa-model-links' !== control.params.type ) {
				return;
			}
			control.deferred.embedded.done( function () {
				initControl( control );
			} );
		} );
	} );

} )( window.jQuery, window.wp && window.wp.customize, window.wp );
