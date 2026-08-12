<?php
/**
 * OSA · My Account dashboard override.
 *
 * Replaces the default "Hello {name}…" with Recent Orders + Watch List
 * (the stat tiles are rendered above the two-column area by the page
 * template via [osa_account_header]).
 *
 * @package OSA_Arsenal
 * @version 4.4.0
 */
defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();
$orders      = function_exists( 'wc_get_orders' ) ? wc_get_orders( [
	'customer_id' => $customer_id,
	'status'      => [ 'wc-processing', 'wc-completed', 'wc-on-hold' ],
	'limit'       => 6,
	'return'      => 'objects',
] ) : [];

$watch_list = array_values( array_filter( array_map( 'intval', (array) get_user_meta( $customer_id, '_osa_watch_list', true ) ) ) );
?>

<div class="osa-account-section">
	<div class="osa-account-section__head">
		<h2 class="osa-account-section__heading">Recent Orders</h2>
		<a class="osa-account-section__link osa-mono-caps" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">View all →</a>
	</div>

	<?php if ( empty( $orders ) ) : ?>
		<p class="osa-mono osa-account-empty">No orders yet. Browse <a href="<?php echo esc_url( site_url( '/shop/' ) ); ?>">the catalog</a> to reserve a lot.</p>
	<?php else : ?>
		<table class="osa-account-table">
			<thead>
				<tr>
					<th>Order</th>
					<th>Date</th>
					<th>Items</th>
					<th>Status</th>
					<th style="text-align:right">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $orders as $order ) :
					$item_count = $order->get_item_count();
					$created    = $order->get_date_created();
					?>
					<tr>
						<td><a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="osa-account-table__id">#<?php echo esc_html( $order->get_order_number() ); ?></a></td>
						<td><?php echo esc_html( $created ? $created->date( 'Y-m-d' ) : '—' ); ?></td>
						<td><?php
							/* translators: %d: number of items in the order. */
							echo esc_html( sprintf( _n( '%d item', '%d items', $item_count, 'osa-arsenal' ), $item_count ) );
						?></td>
						<td>
							<span class="osa-account-status osa-account-status--<?php echo esc_attr( $order->get_status() ); ?>">●</span>
							<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
						</td>
						<td style="text-align:right"><?php echo wp_kses_post( wc_price( $order->get_total(), [ 'currency' => $order->get_currency() ] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<div class="osa-account-section">
	<div class="osa-account-section__head">
		<h2 class="osa-account-section__heading">Watch List</h2>
		<a class="osa-account-section__link osa-mono-caps" href="#osa-watch">Manage →</a>
	</div>
	<?php if ( empty( $watch_list ) ) : ?>
		<p class="osa-mono osa-account-empty">Nothing on watch. Click ♡ on any lot to keep an eye on it.</p>
	<?php else :
		echo '<div class="osa-catalog-grid osa-catalog-grid--cols-4">';
		foreach ( array_slice( $watch_list, 0, 4 ) as $pid ) {
			osa_render_product_card_compact( $pid, '5-4' );
		}
		echo '</div>';
	endif; ?>
</div>
