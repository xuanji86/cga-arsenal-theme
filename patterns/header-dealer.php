<?php
/**
 * Title: Header (Dealer)
 * Slug: osa-arsenal/header-dealer
 * Categories: osa-chrome
 * Block Types: core/template-part/header
 * Description: Status strip (FFL, Net 30) + dealer nav + customer-site toggle.
 */
$user      = wp_get_current_user();
$ffl        = (string) get_user_meta( $user->ID, '_osa_ffl_number', true );
$business   = (string) get_user_meta( $user->ID, '_osa_business_name', true ) ?: $user->display_name;
$logo_url   = osa_logo_url( 'inline-light' );
$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : site_url( '/cart/' );
?>
<!-- wp:group {"tagName":"header","style":{"color":{"background":"#0A0A0A","text":"#FAF7F0"}},"layout":{"type":"default"}} -->
<header class="wp-block-group has-text-color has-background" style="color:#FAF7F0;background-color:#0A0A0A">

	<!-- wp:html -->
	<div class="osa-dealer-strip">
		<span class="osa-dealer-strip__status">
			<span class="osa-dealer-strip__dot"></span>
			Dealer Portal · Trade Pricing Active
		</span>
		<span style="display:inline-flex;gap:18px">
			<?php if ( $ffl ) : ?><span>FFL <?php echo esc_html( $ffl ); ?></span><span>·</span><?php endif; ?>
			<span>Net 30 Terms</span>
			<span>·</span>
			<span><?php echo esc_html( $business ); ?></span>
		</span>
	</div>
	<!-- /wp:html -->

	<!-- wp:group {"style":{"spacing":{"padding":{"top":"16px","right":"24px","bottom":"16px","left":"24px"}},"border":{"bottom":{"color":"#2A2A2A","width":"1px"}}},"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
	<div class="wp-block-group" style="border-bottom-color:#2A2A2A;border-bottom-width:1px;padding-top:16px;padding-right:24px;padding-bottom:16px;padding-left:24px">

		<!-- wp:html -->
		<a href="<?php echo esc_url( site_url( '/dealer/' ) ); ?>" aria-label="OSA Dealer home" style="display:inline-block">
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="OSA Dealer" class="osa-logo" style="height:44px;width:auto" />
		</a>
		<!-- /wp:html -->

		<!-- wp:html -->
		<nav class="osa-dealer-nav" style="display:flex;gap:24px;align-items:center;font-family:var(--osa-f-mono);font-size:11px;letter-spacing:0.16em;text-transform:uppercase">
			<?php
			$current = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
			foreach ( [
				'/dealer/'           => 'Dashboard',
				'/dealer/catalog/'   => 'Trade Floor',
				'/dealer/my-orders/' => 'Orders',
				'/dealer/inventory/' => 'My Inventory',
				'/dealer/payouts/'   => 'Payouts',
			] as $href => $label ) :
				$active = ( $href === $current );
				printf(
					'<a href="%s" style="color:%s;text-decoration:none;font-weight:%s;border-bottom:2px solid %s;padding-bottom:6px">%s</a>',
					esc_url( site_url( $href ) ),
					$active ? '#FAF7F0' : '#8A8A8A',
					$active ? '700' : '500',
					$active ? '#FAF7F0' : 'transparent',
					esc_html( $label )
				);
			endforeach;
			?>
		</nav>
		<!-- /wp:html -->

		<!-- wp:html -->
		<div style="display:flex;gap:14px;align-items:center">
			<a href="<?php echo esc_url( $cart_url ); ?>" class="osa-mono-caps" style="color:#FAF7F0;text-decoration:none;font-size:11px;letter-spacing:0.12em;display:inline-flex;align-items:center;gap:6px" aria-label="Cart">Cart<?php if ( $cart_count > 0 ) : ?> <span style="background:#FAF7F0;color:#0A0A0A;border-radius:999px;padding:1px 8px;font-weight:700;font-size:10px"><?php echo (int) $cart_count; ?></span><?php endif; ?></a>
			<a href="<?php echo esc_url( site_url( '/' ) ); ?>" class="osa-btn osa-btn--sm" style="background:transparent;color:#FAF7F0;border-color:#FAF7F0">← Customer Site</a>
			<a href="<?php echo esc_url( wp_logout_url( site_url( '/dealer/login/' ) ) ); ?>" class="osa-mono-caps" style="color:#8A8A8A;text-decoration:none;font-size:10px">Sign Out</a>
		</div>
		<!-- /wp:html -->

	</div>
	<!-- /wp:group -->

</header>
<!-- /wp:group -->
