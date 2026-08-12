<?php
/**
 * Title: Header (Retail)
 * Slug: osa-arsenal/header
 * Categories: osa-chrome
 * Block Types: core/template-part/header
 * Inserter: no
 * Description: Dark utility strip (DROP pill + announcement + Shipping/Track/Dealers links)
 *              + main row (logo · centered nav · icon controls with cart badge).
 *
 * LEGACY STARTER — the live retail header is the blockified template part
 * (parts/header.html composed of osa/chrome-* blocks). This pattern keeps a
 * parallel inline copy of that markup only so previously flattened Site
 * Editor content keeps rendering; it is hidden from the inserter so new
 * copies can't drift out of sync. Edit parts/header.html, not this file.
 */
$custom_logo_id = get_theme_mod( 'custom_logo' );
$logo_inline    = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : osa_logo_url( 'inline-dark' );
if ( ! $logo_inline ) {
	$logo_inline = osa_logo_url( 'inline-dark' );
}

$strip_msg = osa_text( 'osa_header_strip', OSA_HEADER_STRIP_DEFAULT );

$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : site_url( '/cart/' );

if ( is_user_logged_in() ) {
	$account_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : site_url( '/my-account/' );
	$account_label = __( 'Account', 'osa-arsenal' );
} else {
	$account_url   = site_url( OSA_LOGIN_PATH );
	$account_label = __( 'Sign In', 'osa-arsenal' );
}
?>
<!-- wp:group {"tagName":"header","align":"full","style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"},"margin":{"top":"0","bottom":"0"}},"border":{"bottom":{"color":"var:preset|color|ink","width":"1px"}}},"layout":{"type":"default"}} -->
<header class="wp-block-group alignfull" style="margin-top:0;margin-bottom:0;border-bottom-color:var(--wp--preset--color--ink);border-bottom-width:1px;padding:0">

	<?php if ( get_theme_mod( 'osa_header_strip_enabled', true ) ) : ?>
	<!-- wp:html -->
	<div class="osa-utility-strip">
		<div class="osa-utility-strip__left">
			<span class="osa-utility-strip__msg"><?php echo esc_html( $strip_msg ); ?></span>
		</div>
		<nav class="osa-utility-strip__right" aria-label="Utility">
			<?php foreach ( osa_header_strip_links() as $link ) : ?>
			<a href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['external'] ? ' class="osa-utility-strip__dealers" target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $link['label'] ); ?><?php if ( $link['external'] ) : ?>
				<svg class="osa-utility-strip__arrow" viewBox="0 0 10 10" width="9" height="9" aria-hidden="true" focusable="false">
					<path d="M2 8 L8 2 M4 2 H8 V6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			<?php endif; ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
	<!-- /wp:html -->
	<?php endif; ?>

	<!-- wp:html -->
	<div class="osa-header-row">

		<button type="button" class="osa-header-hamburger" aria-label="<?php esc_attr_e( 'Menu', 'osa-arsenal' ); ?>" aria-controls="osa-mobile-nav" aria-expanded="false" data-osa-nav-toggle>
			<svg class="osa-header-hamburger__icon osa-header-hamburger__icon--open" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
				<line x1="4" y1="7"  x2="20" y2="7"/>
				<line x1="4" y1="12" x2="20" y2="12"/>
				<line x1="4" y1="17" x2="20" y2="17"/>
			</svg>
			<svg class="osa-header-hamburger__icon osa-header-hamburger__icon--close" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
				<line x1="6" y1="6" x2="18" y2="18"/>
				<line x1="18" y1="6" x2="6" y2="18"/>
			</svg>
		</button>

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Old Steel Arsenal home" class="osa-header-logo">
			<img src="<?php echo esc_url( $logo_inline ); ?>" alt="Old Steel Arsenal" class="osa-logo" />
		</a>

		<?php
		$mobile_account_link = sprintf(
			'<a href="%s" class="osa-main-nav__account-link" data-osa-mobile-only="1">%s</a>',
			esc_url( $account_url ),
			esc_html( $account_label )
		);
		wp_nav_menu( [
			'theme_location'        => 'primary',
			'container'             => 'nav',
			'container_class'       => 'osa-main-nav',
			'container_id'          => 'osa-mobile-nav', // referenced by the hamburger's aria-controls.
			'container_aria_label'  => __( 'Primary', 'osa-arsenal' ),
			'items_wrap'            => '%3$s' . $mobile_account_link,
			'walker'                => new OSA_Header_Nav_Walker(),
			'fallback_cb'           => 'osa_header_nav_fallback',
			'depth'                 => 1,
		] );
		?>

		<div class="osa-header-controls">

			<button type="button" class="osa-header-icon" aria-label="<?php esc_attr_e( 'Search', 'osa-arsenal' ); ?>" data-osa-search>
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
					<circle cx="11" cy="11" r="7"/>
					<line x1="20" y1="20" x2="16" y2="16"/>
				</svg>
			</button>

			<a href="<?php echo esc_url( $account_url ); ?>" class="osa-header-icon osa-header-icon--account" aria-label="<?php echo esc_attr( $account_label ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="8" r="4"/>
					<path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
				</svg>
			</a>

			<?php
			/* translators: %d: number of items in the cart. */
			$cart_label = sprintf( __( 'Cart: %d items', 'osa-arsenal' ), $cart_count );
			?>
			<a href="<?php echo esc_url( $cart_url ); ?>" class="osa-header-icon osa-header-icon--cart" aria-label="<?php echo esc_attr( $cart_label ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="9" cy="20" r="1.3"/>
					<circle cx="17" cy="20" r="1.3"/>
					<path d="M3 4h3l2.5 11.5h11L21 8H7"/>
				</svg>
				<?php echo osa_cart_count_badge( $cart_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped in helper. ?>
			</a>

		</div>

	</div>
	<!-- /wp:html -->

	<!-- wp:html -->
	<div class="osa-nav-backdrop" hidden data-osa-nav-close></div>
	<!-- /wp:html -->

</header>
<!-- /wp:group -->
