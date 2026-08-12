<?php
/**
 * OSA — account dashboard chrome.
 *
 *  Provides the top "Account Header" (name + 4 stat cards) that sits above
 *  the standard WooCommerce my-account two-column area, plus light filter
 *  hooks to rename WC's menu items to the OSA vocabulary.
 *
 *  Rendered via [osa_account_header] shortcode for use inside the page
 *  template.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'osa_account_header', function () {
	if ( ! is_user_logged_in() ) {
		return '';
	}
	$user = wp_get_current_user();

	$lifetime = 0;
	if ( function_exists( 'wc_get_orders' ) ) {
		// paginate=true returns an object with ->total — the count without
		// materializing every order ID for heavy customers.
		$found    = wc_get_orders( [
			'customer_id' => $user->ID,
			'status'      => [ 'wc-completed' ],
			'paginate'    => true,
			'limit'       => 1,
			'return'      => 'ids',
		] );
		$lifetime = $found ? (int) $found->total : 0;
	}
	$watch_list  = (array) get_user_meta( $user->ID, '_osa_watch_list', true );
	$watching    = count( array_filter( array_map( 'intval', $watch_list ) ) );
	$consigned   = (int) get_user_meta( $user->ID, '_osa_consigned_count', true );
	$registered  = $user->user_registered ? mysql2date( 'F Y', $user->user_registered ) : '—';
	// Prefer a real name; fall back to user_login. Avoid showing the email as the big heading.
	$first   = trim( (string) $user->first_name );
	$last    = trim( (string) $user->last_name );
	$display = trim( $first . ' ' . $last );
	if ( '' === $display || is_email( $display ) ) {
		$display = $user->user_login;
	}
	// If even login is an email-y string, mask it nicely.
	if ( is_email( $display ) ) {
		$display = strstr( $display, '@', true );
	}

	ob_start();
	?>
	<section class="osa-account-header">

		<div class="osa-account-header__id">
			<p class="osa-mono-caps osa-account-header__since">Member since <?php echo esc_html( $registered ); ?></p>
			<h1 class="osa-account-header__name"><?php echo esc_html( $display ); ?></h1>
			<p class="osa-mono osa-account-header__email"><?php echo esc_html( $user->user_email ); ?></p>
		</div>

		<div class="osa-stat-row osa-stat-row--3 osa-account-header__stats">
			<div class="osa-stat">
				<span class="osa-stat__value"><?php echo esc_html( number_format_i18n( $lifetime ) ); ?></span>
				<span class="osa-stat__label">Lifetime Orders</span>
			</div>
			<div class="osa-stat">
				<span class="osa-stat__value"><?php echo esc_html( number_format_i18n( $watching ) ); ?></span>
				<span class="osa-stat__label">Watching</span>
			</div>
			<div class="osa-stat">
				<span class="osa-stat__value"><?php echo esc_html( number_format_i18n( $consigned ) ); ?></span>
				<span class="osa-stat__label">Consigned</span>
			</div>
		</div>

	</section>
	<?php
	return ob_get_clean();
} );

/**
 * Rename WooCommerce account menu items to OSA vocabulary + hide unused.
 */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	$reordered = [];
	$rename = [
		'dashboard'       => 'Overview',
		'orders'          => 'Order History',
		'edit-address'    => 'Address Book',
		'edit-account'    => 'Identity & FFL',
		'customer-logout' => 'Sign Out',
	];
	foreach ( [ 'dashboard', 'orders', 'edit-address', 'edit-account', 'customer-logout' ] as $key ) {
		if ( isset( $items[ $key ] ) ) {
			$reordered[ $key ] = $rename[ $key ] ?? $items[ $key ];
		}
	}
	// Insert phase-4 placeholder items (visual only — they link to # for now).
	$with_placeholders = [];
	foreach ( $reordered as $k => $v ) {
		$with_placeholders[ $k ] = $v;
		if ( 'orders' === $k ) {
			$with_placeholders['osa-watch']   = __( 'Watch List', 'osa-arsenal' );
			$with_placeholders['osa-consign'] = __( 'My Consignments', 'osa-arsenal' );
		}
	}
	return $with_placeholders;
}, 20 );

/**
 * The placeholder menu items link to # (no endpoint registered yet).
 */
add_filter( 'woocommerce_get_endpoint_url', function ( $url, $endpoint ) {
	if ( in_array( $endpoint, [ 'osa-watch', 'osa-consign' ], true ) ) {
		return '#';
	}
	return $url;
}, 10, 2 );
