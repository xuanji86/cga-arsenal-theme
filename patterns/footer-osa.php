<?php
/**
 * Title: Footer (Retail)
 * Slug: osa-arsenal/footer
 * Categories: osa-chrome
 * Block Types: core/template-part/footer
 * Description: Five-column dark footer with logo, legal, and section links.
 *
 * Rendered as a single wp:html block to avoid wp:columns nesting weirdness
 * with raw HTML children. Layout uses CSS Grid (.osa-footer__grid).
 */
$logo_stacked_light = osa_logo_url( 'banner-light' );
$headings           = osa_footer_col_headings();

// Each column: [ heading, theme_location, fallback_items ]
$sections = [
	[ 'Shop', 'footer-shop', [
		[ '/shop/',                'All Inventory' ],
		[ '/shop/?cat=curio-relic', 'Curio and Relic' ],
		[ '/shop/?cat=rifles',     'Rifles' ],
		[ '/shop/?cat=handguns',   'Handguns' ],
		[ '/shop/?just-in=1',      'Recently Acquired' ],
	] ],
	[ 'Services', 'footer-services', [
		[ '/consignment/',         'Consignment' ],
		[ '/services/ffl/',        'FFL Transfer' ],
		[ '/services/appraisal/',  'Appraisal' ],
		[ '/services/gunsmithing/', 'Gunsmithing service' ],
	] ],
	[ 'Account', 'footer-account', [
		// Guarded: this file also renders with WooCommerce inactive (dealer
		// footer part + editor pattern REST) — an unguarded call fatals there.
		[ function_exists( 'wc_get_page_permalink' ) ? ( wc_get_page_permalink( 'myaccount' ) ?: '/my-account/' ) : '/my-account/', 'Sign In' ],
		[ '/dealer/apply/',        'Dealer Apply' ],
		[ '/my-account/orders/',   'Order History' ],
		[ '/my-account/watch-list/','Watch List' ],
	] ],
	[ 'About Us', 'footer-house', [
		[ '/about/',               'Our Story' ],
		[ '/news/',                'News' ],
		[ '/visit/',               'Visit' ],
		[ '/contact/',             'Contact' ],
	] ],
];
?>
<!-- wp:html -->
<footer class="osa-footer">
	<div class="osa-footer__grid">

		<div class="osa-footer__brand">
			<img src="<?php echo esc_url( $logo_stacked_light ); ?>" alt="Old Steel Arsenal" class="osa-logo osa-footer__logo" />
			<p class="osa-footer__legal">
				<?php echo esc_html( osa_text( 'osa_footer_tagline', OSA_FOOTER_TAGLINE_DEFAULT ) ); ?><br />
				<?php echo nl2br( esc_html( osa_text( 'osa_footer_legal', OSA_FOOTER_LEGAL_DEFAULT ) ) ); ?>
			</p>
		</div>

		<?php foreach ( $sections as $i => [ $heading, $location, $fallback ] ) : ?>
			<div class="osa-footer__col">
				<h4 class="osa-footer__heading"><?php echo esc_html( strtoupper( $headings[ $i ] ?? $heading ) ); ?></h4>
				<?php
				wp_nav_menu( [
					'theme_location'  => $location,
					'container'       => false,
					'menu_class'      => 'osa-footer__list',
					'walker'          => new OSA_Footer_Nav_Walker(),
					'fallback_cb'     => function () use ( $fallback ) {
						osa_footer_nav_fallback( $fallback );
					},
					'depth'           => 1,
				] );
				?>
			</div>
		<?php endforeach; ?>

	</div>

	<div class="osa-footer__bar">
		<span><?php echo esc_html( osa_text( 'osa_footer_copyright', OSA_FOOTER_COPYRIGHT_DEFAULT ) ); ?></span>
		<span><?php echo esc_html( osa_text( 'osa_footer_compliance', OSA_FOOTER_COMPLIANCE_DEFAULT ) ); ?></span>
	</div>
</footer>
<!-- /wp:html -->
