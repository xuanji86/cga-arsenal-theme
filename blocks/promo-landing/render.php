<?php
/**
 * OSA · Independence Day Landing — the whole promo page body.
 *
 * Mirrors editable/Independence Day.dc.html in the Claude Design project:
 * dark hero with coupon card + live countdown, "The Whole Arsenal" in-stock
 * grid (8 up front, Load More reveals the rest — every card is in the HTML,
 * so the LiteSpeed-cached page needs no AJAX), assurances strip, statement.
 * Empty output outside the promo window (the page also 302s to /shop/ then).
 *
 * @var array $attributes
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'osa_promo_active' ) || ! osa_promo_active() ) {
	return '';
}

$end_ts = osa_promo_end_ts();

// The whole in-stock arsenal, newest first — same visibility rules as the
// shop grid (osa/catalog-grid instock mode).
$q = new WP_Query(
	[
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'tax_query'      => [
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => [ 'exclude-from-catalog', 'outofstock' ],
				'operator' => 'NOT IN',
			],
		],
	]
);

$total   = (int) $q->post_count;
$initial = min( 8, $total );
$clamped = $total > $initial;

// Server-side first paint for the countdown; view.js takes over on load, so
// a stale cached page self-corrects in the first tick.
$left = max( 0, $end_ts - time() );
$cd   = [
	'd' => [ 'Days', (int) floor( $left / DAY_IN_SECONDS ) ],
	'h' => [ 'Hours', (int) floor( ( $left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS ) ],
	'm' => [ 'Mins', (int) floor( ( $left % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS ) ],
	's' => [ 'Secs', (int) ( $left % MINUTE_IN_SECONDS ) ],
];

$wrapper = get_block_wrapper_attributes( [ 'class' => 'ind-root' ] );
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<section class="ind-hero">
		<div class="ind-hero__stripes" aria-hidden="true"></div>
		<div class="ind-hero__inner">
			<div class="ind-hero__intro">
				<p class="ind-hero__stars" aria-hidden="true">★ ★ ★ ★ ★</p>
				<p class="ind-hero__eyebrow">Fourth of July · MMXXVI · America’s 250th</p>
				<h1 class="ind-hero__title">Independence<br>Day</h1>
				<p class="ind-hero__kicker">The Arsenal of the Republic.</p>
				<p class="ind-hero__lede">American iron and arms from around the world — hand-graded and honestly photographed. Use code <strong class="ind-hero__code">JULY4</strong> for 10% off the collection, July 3 through 14; every order ships the moment the holiday clears, FFL as always.</p>
				<div class="ind-hero__cta">
					<a class="osa-btn" href="#collection">Shop the Collection →</a>
					<a class="osa-btn osa-btn--outline ind-hero__ghost" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Browse Full Catalog</a>
				</div>
			</div>
			<aside class="ind-event">
				<div>
					<div class="ind-event__label">Independence Day Coupon</div>
					<div class="ind-event__dates">July 3 – 14</div>
				</div>
				<div class="ind-coupon">
					<div class="ind-coupon__off">10% Off</div>
					<div class="ind-coupon__code">JULY4</div>
					<div class="ind-coupon__hint">Apply at checkout</div>
				</div>
				<div data-ind-countdown="<?php echo esc_attr( (string) $end_ts ); ?>">
					<p class="ind-event__cdlabel">Coupon ends in</p>
					<div class="ind-countdown" role="timer" aria-label="Coupon ends July 14, 11:59 PM">
						<?php foreach ( $cd as $key => $unit ) : ?>
						<div class="ind-cd-unit">
							<span class="ind-cd-unit__num" data-ind-num="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( str_pad( (string) $unit[1], 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="ind-cd-unit__lbl"><?php echo esc_html( $unit[0] ); ?></span>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<p class="ind-event__foot">10% off with code JULY4 · Excludes consignment &amp; already-discounted pieces · Ships after the holiday · Fifty states</p>
			</aside>
		</div>
	</section>

	<section class="ind-collection<?php echo $clamped ? ' ind-collection--clamped' : ''; ?>" id="collection">
		<div class="ind-collection__head">
			<div class="ind-collection__titlewrap">
				<p class="ind-collection__eyebrow">Independence Day · Full Catalog</p>
				<h2 class="ind-collection__title">The Whole Arsenal</h2>
			</div>
			<p class="ind-collection__note">Hand-graded &amp; bench-inspected<br>Code JULY4 · 10% off through July 14<br>Consignment &amp; discounted items excluded</p>
		</div>

		<div class="osa-catalog-grid osa-catalog-grid--cols-4 ind-catalog-grid" data-osa-list="independence_day">
			<?php
			while ( $q->have_posts() ) {
				$q->the_post();
				osa_render_product_card_compact( get_the_ID(), '16-9' );
			}
			wp_reset_postdata();
			?>
		</div>

		<?php if ( $clamped ) : ?>
		<div class="ind-loadmore">
			<button class="osa-btn osa-btn--outline" type="button" data-ind-more="8">Load More ↓</button>
			<p class="ind-loadmore__count">Showing <span data-ind-shown><?php echo (int) $initial; ?></span> of <?php echo (int) $total; ?></p>
		</div>
		<noscript><style>.ind-collection--clamped .ind-catalog-grid > a:nth-child(n+9){display:flex}.ind-loadmore{display:none}</style></noscript>
		<?php endif; ?>

		<div class="ind-strip">
			<span>Bench-inspected &amp; hand-graded</span>
			<span class="ind-strip__sep" aria-hidden="true">·</span>
			<span>Insured · Signature FFL transfer</span>
			<span class="ind-strip__sep" aria-hidden="true">·</span>
			<span>Ships to fifty states</span>
		</div>
	</section>

	<section class="ind-statement">
		<div class="ind-statement__eyebrow">The Fourth</div>
		<div>
			<p class="ind-statement__text">“A free people keeps its own arms in working order. We spend the year keeping the old iron honest — the Fourth is simply when we hand it forward.”</p>
			<p class="ind-statement__attr">Old Steel Arsenal</p>
		</div>
	</section>

</div>
