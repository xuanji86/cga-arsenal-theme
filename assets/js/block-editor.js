/**
 * OSA Arsenal — block editor registrations.
 *
 * All OSA blocks are server-rendered. This file provides:
 *   - A custom "osa" block category in the inserter.
 *   - Edit components that use ServerSideRender for live preview.
 *   - Sidebar (InspectorControls) panels for each block's attributes.
 *
 * No build step. Loaded directly via wp_enqueue_script with WP-shipped deps.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const {
		PanelBody,
		TextControl,
		TextareaControl,
		ToggleControl,
		SelectControl,
		RangeControl,
		Button,
	} = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const __ = wp.i18n.__;

	// Custom category.
	if ( wp.blocks.getCategories ) {
		const cats = wp.blocks.getCategories();
		if ( ! cats.find( ( c ) => c.slug === 'osa' ) ) {
			wp.blocks.setCategories( [
				{ slug: 'osa', title: 'OSA Arsenal', icon: 'awards' },
				...cats,
			] );
		}
	}

	// osa/* blocks with a server-side registration on THIS site (localized by
	// inc/blocks.php). The dealer blocks live in the osa-dealer plugin — when
	// it's inactive their edit() must not register, or the inserter offers
	// blocks whose ServerSideRender 404s. Fails open if the list is missing.
	const serverRegistered =
		( window.osaEditorBlocks && window.osaEditorBlocks.serverRegistered ) || null;

	const registerOsaBlock = ( name, settings ) => {
		if ( serverRegistered && serverRegistered.indexOf( name ) === -1 ) {
			return; // No server side here (companion plugin inactive).
		}
		if ( wp.blocks.getBlockType( name ) ) {
			return; // Already registered (e.g. by the owning plugin).
		}
		registerBlockType( name, settings );
	};

	const make = ( name, controls ) => ( props ) => {
		const blockProps = useBlockProps();
		return el(
			Fragment,
			null,
			controls && el( InspectorControls, null, controls( props ) ),
			el( 'div', blockProps,
				el( ServerSideRender, {
					block: name,
					attributes: props.attributes,
				} )
			)
		);
	};

	// osa/grade-chip and osa/condition-scale are registered by the ffl-core
	// plugin (includes/catalog/blocks/) and must NOT be registered here.
	// Removed in feat/condition-redesign; kept as tombstone so git history
	// is clear about the intentional deletion.

	registerOsaBlock( 'osa/lot-badge', {
		edit: make( 'osa/lot-badge', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'LOT', 'osa-arsenal' ) },
				el( TextControl, {
					label: __( 'LOT number', 'osa-arsenal' ),
					help: __( 'Leave blank to use the current product\'s LOT.', 'osa-arsenal' ),
					value: attributes.lot || '',
					onChange: ( v ) => setAttributes( { lot: v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/product-card-compact', {
		edit: make( 'osa/product-card-compact', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Card', 'osa-arsenal' ) },
				el( SelectControl, {
					label: __( 'Aspect ratio', 'osa-arsenal' ),
					value: attributes.ar || '5-4',
					options: [
						{ label: '5 / 4', value: '5-4' },
						{ label: '4 / 3', value: '4-3' },
						{ label: '1 / 1', value: '1-1' },
						{ label: '3 / 4', value: '3-4' },
					],
					onChange: ( v ) => setAttributes( { ar: v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/catalog-grid', {
		edit: make( 'osa/catalog-grid', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Grid', 'osa-arsenal' ) },
				el( RangeControl, {
					label: __( 'Columns', 'osa-arsenal' ),
					min: 1, max: 6,
					value: attributes.cols || 4,
					onChange: ( v ) => setAttributes( { cols: v } ),
				} ),
				el( RangeControl, {
					label: __( 'Number of products', 'osa-arsenal' ),
					min: 1, max: 48,
					value: attributes.limit || 12,
					onChange: ( v ) => setAttributes( { limit: v } ),
				} ),
				el( SelectControl, {
					label: __( 'Order by', 'osa-arsenal' ),
					value: attributes.orderby || 'date',
					options: [
						{ label: 'Date',         value: 'date' },
						{ label: 'Title',        value: 'title' },
						{ label: 'Menu order',   value: 'menu_order' },
						{ label: 'Random',       value: 'rand' },
					],
					onChange: ( v ) => setAttributes( { orderby: v } ),
				} ),
				el( SelectControl, {
					label: __( 'Order', 'osa-arsenal' ),
					value: attributes.order || 'DESC',
					options: [
						{ label: 'Descending', value: 'DESC' },
						{ label: 'Ascending',  value: 'ASC' },
					],
					onChange: ( v ) => setAttributes( { order: v } ),
				} ),
				el( TextControl, {
					label: __( 'Category slug (optional)', 'osa-arsenal' ),
					value: attributes.category || '',
					onChange: ( v ) => setAttributes( { category: v } ),
				} ),
				el( TextControl, {
					label: __( 'GA4 list id (optional)', 'osa-arsenal' ),
					help: __( 'Emits data-osa-list for analytics. Model landings use model_landing_<page-slug>.', 'osa-arsenal' ),
					value: attributes.list || '',
					onChange: ( v ) => setAttributes( { list: v } ),
				} ),
				el( SelectControl, {
					label: __( 'Structured data', 'osa-arsenal' ),
					help: __( 'CollectionPage/ItemList JSON-LD — for model landing pages only.', 'osa-arsenal' ),
					value: attributes.schema || '',
					options: [
						{ label: __( 'None', 'osa-arsenal' ), value: '' },
						{ label: 'CollectionPage + ItemList', value: 'collection' },
					],
					onChange: ( v ) => setAttributes( { schema: v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/model-links', {
		edit: make( 'osa/model-links', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Model Links', 'osa-arsenal' ) },
				el( SelectControl, {
					label: __( 'Variant', 'osa-arsenal' ),
					value: attributes.variant || 'chips',
					options: [
						{ label: __( 'Chips (no images)', 'osa-arsenal' ), value: 'chips' },
						{ label: __( 'Cards (image + label)', 'osa-arsenal' ), value: 'cards' },
					],
					onChange: ( v ) => setAttributes( { variant: v } ),
				} ),
				el( TextControl, {
					label: __( 'Heading (optional)', 'osa-arsenal' ),
					value: attributes.heading || '',
					onChange: ( v ) => setAttributes( { heading: v } ),
				} ),
				el( 'p', { className: 'components-base-control__help' },
					__( 'The link list is site-wide: Customize → OSA · Model Pages.', 'osa-arsenal' )
				),
				el( Button, {
					variant: 'secondary',
					// Relative to the current /wp-admin/ page, so it resolves
					// regardless of where WP is installed. New tab keeps any
					// unsaved editor state intact.
					onClick: () => window.open( 'customize.php?autofocus[section]=osa_models_section', '_blank', 'noopener' ),
				}, __( 'Edit the model list →', 'osa-arsenal' ) )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/model-page-link', {
		edit: make( 'osa/model-page-link', () =>
			el( PanelBody, { title: __( 'Model Page Link', 'osa-arsenal' ) },
				el( 'p', { className: 'components-base-control__help' },
					__( 'Nothing to configure. On a product page this links to the landing page whose Filter slugs contain that product\'s Model attribute; the wording is built from the row Label. It renders nothing anywhere else — including here in the editor.', 'osa-arsenal' )
				),
				el( Button, {
					variant: 'secondary',
					onClick: () => window.open( 'customize.php?autofocus[section]=osa_models_section', '_blank', 'noopener' ),
				}, __( 'Edit the model list →', 'osa-arsenal' ) )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/model-faq', {
		edit: make( 'osa/model-faq', ( { attributes, setAttributes } ) => {
			const items = attributes.items || [];
			const setItem = ( i, key, v ) => {
				const next = items.map( ( it, j ) => ( j === i ? { ...it, [ key ]: v } : it ) );
				setAttributes( { items: next } );
			};
			return el( PanelBody, { title: __( 'FAQ', 'osa-arsenal' ) },
				el( TextControl, {
					label: __( 'Heading', 'osa-arsenal' ),
					value: attributes.heading || '',
					onChange: ( v ) => setAttributes( { heading: v } ),
				} ),
				...items.map( ( it, i ) =>
					el( Fragment, { key: i },
						el( TextControl, {
							label: __( 'Question', 'osa-arsenal' ) + ' ' + ( i + 1 ),
							value: it.q || '',
							onChange: ( v ) => setItem( i, 'q', v ),
						} ),
						el( TextareaControl, {
							label: __( 'Answer', 'osa-arsenal' ) + ' ' + ( i + 1 ),
							help: i === 0 ? __( 'Allowed markup: p, a, strong, em, br.', 'osa-arsenal' ) : undefined,
							value: it.a || '',
							onChange: ( v ) => setItem( i, 'a', v ),
						} ),
						el( Button, {
							variant: 'secondary',
							isDestructive: true,
							onClick: () => setAttributes( { items: items.filter( ( _, j ) => j !== i ) } ),
						}, __( 'Remove', 'osa-arsenal' ) )
					)
				),
				el( Button, {
					variant: 'primary',
					style: { marginTop: '12px' },
					onClick: () => setAttributes( { items: [ ...items, { q: '', a: '' } ] } ),
				}, __( 'Add question', 'osa-arsenal' ) )
			);
		} ),
		save: () => null,
	} );

	registerOsaBlock( 'osa/dealer-stat', {
		edit: make( 'osa/dealer-stat', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Dealer Stat', 'osa-arsenal' ) },
				el( SelectControl, {
					label: __( 'Kind', 'osa-arsenal' ),
					value: attributes.kind || 'literal',
					options: [
						{ label: 'Literal (label + value)', value: 'literal' },
						{ label: 'Open Orders (MTD)',       value: 'open_orders' },
						{ label: 'In Transit',              value: 'in_transit' },
						{ label: 'Pending Payout',          value: 'pending_payout' },
						{ label: 'Consigned',               value: 'consigned' },
						{ label: 'Tier Margin',             value: 'tier_margin' },
						{ label: 'YTD Volume',              value: 'ytd_volume' },
						{ label: 'Lifetime Orders',         value: 'lifetime_orders' },
						{ label: 'Watching',                value: 'watching' },
						{ label: 'OSA Credit',              value: 'credit' },
					],
					onChange: ( v ) => setAttributes( { kind: v } ),
				} ),
				attributes.kind === 'literal' && el( Fragment, null,
					el( TextControl, {
						label: __( 'Label', 'osa-arsenal' ),
						value: attributes.label || '',
						onChange: ( v ) => setAttributes( { label: v } ),
					} ),
					el( TextControl, {
						label: __( 'Value', 'osa-arsenal' ),
						value: attributes.value || '',
						onChange: ( v ) => setAttributes( { value: v } ),
					} ),
				),
				el( ToggleControl, {
					label: __( 'Emphasis (inverted)', 'osa-arsenal' ),
					checked: !! attributes.emphasis,
					onChange: ( v ) => setAttributes( { emphasis: !! v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/dealer-kpi-row', {
		edit: make( 'osa/dealer-kpi-row', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'KPI Row', 'osa-arsenal' ) },
				el( SelectControl, {
					label: __( 'Variant', 'osa-arsenal' ),
					value: attributes.variant || 'dashboard',
					options: [
						{ label: 'Dashboard',  value: 'dashboard' },
						{ label: 'Inventory',  value: 'inventory' },
						{ label: 'Account',    value: 'account' },
					],
					onChange: ( v ) => setAttributes( { variant: v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/dealer-tier-progress', {
		edit: make( 'osa/dealer-tier-progress' ),
		save: () => null,
	} );

	registerOsaBlock( 'osa/dealer-pending-banner', {
		edit: make( 'osa/dealer-pending-banner' ),
		save: () => null,
	} );

	registerOsaBlock( 'osa/dealer-catalog-table', {
		edit: make( 'osa/dealer-catalog-table', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Catalog', 'osa-arsenal' ) },
				el( RangeControl, {
					label: __( 'Limit', 'osa-arsenal' ),
					min: 1, max: 200,
					value: attributes.limit || 30,
					onChange: ( v ) => setAttributes( { limit: v } ),
				} ),
				el( TextControl, {
					label: __( 'Category slug (optional)', 'osa-arsenal' ),
					value: attributes.category || '',
					onChange: ( v ) => setAttributes( { category: v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/marquee-ticker', {
		edit: make( 'osa/marquee-ticker', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Marquee', 'osa-arsenal' ) },
				el( TextControl, {
					label: __( 'Items (one per line)', 'osa-arsenal' ),
					help: __( 'Each line becomes a phrase between separators.', 'osa-arsenal' ),
					value: ( attributes.items || [] ).join( '\n' ),
					onChange: ( v ) => setAttributes( {
						items: v.split( '\n' ).map( ( s ) => s.trim() ).filter( Boolean ),
					} ),
				} ),
				el( TextControl, {
					label: __( 'Separator', 'osa-arsenal' ),
					value: attributes.sep || '·',
					onChange: ( v ) => setAttributes( { sep: v } ),
				} ),
				el( ToggleControl, {
					label: __( 'Inverted (white on black)', 'osa-arsenal' ),
					checked: !! attributes.invert,
					onChange: ( v ) => setAttributes( { invert: !! v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/chrome-logo', {
		edit: make( 'osa/chrome-logo', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Logo', 'osa-arsenal' ) },
				el( ToggleControl, {
					label: __( 'Use WordPress Custom Logo', 'osa-arsenal' ),
					help: __( 'Off = use an OSA logo variant below.', 'osa-arsenal' ),
					checked: !! attributes.useCustomLogo,
					onChange: ( v ) => setAttributes( { useCustomLogo: !! v } ),
				} ),
				el( SelectControl, {
					label: __( 'OSA variant', 'osa-arsenal' ),
					value: attributes.variant || 'inline-dark',
					options: [
						{ label: 'Inline · dark', value: 'inline-dark' },
						{ label: 'Inline · light', value: 'inline-light' },
						{ label: 'Stacked · dark', value: 'stacked-dark' },
						{ label: 'Stacked · light', value: 'stacked-light' },
						{ label: 'Banner · dark', value: 'banner-dark' },
						{ label: 'Banner · light', value: 'banner-light' },
						{ label: 'Mark · dark', value: 'mark-dark' },
						{ label: 'Mark · light', value: 'mark-light' },
					],
					onChange: ( v ) => setAttributes( { variant: v } ),
				} )
			)
		),
		save: () => null,
	} );

	registerOsaBlock( 'osa/chrome-nav', {
		edit: make( 'osa/chrome-nav', ( { attributes, setAttributes } ) =>
			el( PanelBody, { title: __( 'Menu', 'osa-arsenal' ) },
				el( SelectControl, {
					label: __( 'Menu location', 'osa-arsenal' ),
					value: attributes.location || '',
					options: [
						{ label: '—', value: '' },
						{ label: 'Primary (header)', value: 'primary' },
						{ label: 'Footer · Shop', value: 'footer-shop' },
						{ label: 'Footer · Services', value: 'footer-services' },
						{ label: 'Footer · Account', value: 'footer-account' },
						{ label: 'Footer · House', value: 'footer-house' },
					],
					help: __( 'Edit the links under Appearance → Menus.', 'osa-arsenal' ),
					onChange: ( v ) => setAttributes( { location: v } ),
				} ),
				el( ToggleControl, {
					label: __( 'Footer styling', 'osa-arsenal' ),
					checked: !! attributes.footerStyle,
					onChange: ( v ) => setAttributes( { footerStyle: !! v } ),
				} )
			)
		),
		save: () => null,
	} );

	// Header chrome blocks — server-rendered, no inspector controls.
	[ 'osa/chrome-utility-strip', 'osa/chrome-menu-toggle', 'osa/chrome-controls' ].forEach( function ( name ) {
		registerOsaBlock( name, { edit: make( name ), save: () => null } );
	} );
} )( window.wp );
