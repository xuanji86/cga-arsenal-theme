<?php
/**
 * OSA · Header Controls — search button (data-osa-search → search.js overlay),
 * login-aware account link, and the cart link with the live count badge
 * (osa_cart_count_badge, kept in sync by the add-to-cart fragment).
 * Reproduces the old header pattern's .osa-header-controls cluster.
 */
defined( 'ABSPATH' ) || exit;

$acct       = osa_account_link_info();
$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : site_url( '/cart/' );
?>
<div class="osa-header-controls">

	<?php if ( get_theme_mod( 'osa_show_search', true ) ) : ?>
	<button type="button" class="osa-header-icon" aria-label="<?php esc_attr_e( 'Search', 'osa-arsenal' ); ?>" data-osa-search>
		<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
			<circle cx="11" cy="11" r="7"/>
			<line x1="20" y1="20" x2="16" y2="16"/>
		</svg>
	</button>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'osa_show_account', true ) ) : ?>
	<a href="<?php echo esc_url( $acct['url'] ); ?>" class="osa-header-icon osa-header-icon--account" aria-label="<?php echo esc_attr( $acct['label'] ); ?>">
		<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="12" cy="8" r="4"/>
			<path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
		</svg>
	</a>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'osa_show_cart', true ) ) : ?>
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
	<?php endif; ?>

</div>
<?php
