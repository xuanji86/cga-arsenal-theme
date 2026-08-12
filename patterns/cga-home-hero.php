<?php
/**
 * Title: CGA · Home Hero
 * Slug: cga-arsenal/home-hero
 * Categories: osa-section
 * Inserter: yes
 *
 * Split hero: statement + store facts left, one featured firearm right.
 * The featured card is the newest in-stock product — live data, no
 * hand-picked placeholder that goes stale.
 *
 * @package OSA_Arsenal
 */

$cga_count = wp_count_posts( 'product' );
$cga_count = $cga_count ? (int) $cga_count->publish : 0;

$cga_feat = null;
if ( function_exists( 'wc_get_products' ) ) {
	$cga_feat_list = wc_get_products( [
		'status'       => 'publish',
		'limit'        => 1,
		'orderby'      => 'date',
		'order'        => 'DESC',
		'stock_status' => 'instock',
	] );
	$cga_feat      = $cga_feat_list ? $cga_feat_list[0] : null;
}
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-hero">
		<div>
			<p class="cga-eyebrow">Est. 2023 · Corona, California · FFL 9-33-065-01-8G-09316 · CFD# 24307</p>
			<h1 class="cga-h1">Stocked for<br />California.</h1>
			<p class="cga-lede">Roster-approved handguns, compliant rifles, and ammunition you can actually take home &mdash; on the shelf at 401 S Lincoln Ave. We run the DROS, proctor the FSC, and track the ten-day wait, so nothing surprises you at the counter.</p>
			<div class="home-hero__cta">
				<a class="cga-btn cga-btn--pine" href="/shop/">Shop in-stock firearms</a>
				<a class="cga-btn cga-btn--ghost" href="/ca-guide/">How to buy in California &rarr;</a>
			</div>
			<div class="home-hero__meta">
				<span class="home-stat"><b><?php echo esc_html( $cga_count ?: '—' ); ?></b><span>Items on the shelf</span></span>
				<span class="home-stat"><b>11&ndash;7</b><span>Open every day</span></span>
				<span class="home-stat"><b>10 days</b><span>CA waiting period</span></span>
				<span class="home-stat"><b>$26</b><span>FSC test, walk-in</span></span>
			</div>
		</div>
		<?php if ( $cga_feat ) : ?>
		<div class="home-feat">
			<span class="home-feat__tag">Featured · New arrival</span>
			<div class="home-feat__media">
				<?php echo get_the_post_thumbnail( $cga_feat->get_id(), 'large', [ 'alt' => esc_attr( $cga_feat->get_name() ), 'style' => 'max-width:100%;max-height:100%;object-fit:contain;' ] ); ?>
			</div>
			<div class="home-feat__body">
				<span class="home-feat__brand"><?php echo esc_html( function_exists( 'osa_product_term' ) ? osa_product_term( 'pa_manufacturer', $cga_feat->get_id() ) : '' ); ?></span>
				<h2 class="home-feat__name"><?php echo esc_html( $cga_feat->get_name() ); ?></h2>
				<p class="home-feat__spec"><?php echo esc_html( $cga_feat->get_short_description() ? wp_strip_all_tags( $cga_feat->get_short_description() ) : ( function_exists( 'osa_get_product_caliber' ) ? osa_get_product_caliber( $cga_feat->get_id() ) : '' ) ); ?></p>
				<?php echo function_exists( 'cga_status_chips_html' ) ? cga_status_chips_html( $cga_feat->get_id() ) : ''; ?>
				<div class="home-feat__row">
					<span class="home-feat__price"><?php echo wp_kses_post( $cga_feat->get_price_html() ); ?></span>
					<span class="home-feat__stock"><?php echo esc_html( function_exists( 'cga_card_stock_line' ) ? cga_card_stock_line( $cga_feat ) . ' · Corona' : '' ); ?></span>
				</div>
				<a class="cga-btn cga-btn--lg" href="<?php echo esc_url( get_permalink( $cga_feat->get_id() ) ); ?>">View details</a>
			</div>
		</div>
		<?php else : ?>
		<div class="home-feat">
			<span class="home-feat__tag">Featured · New arrival</span>
			<div class="home-feat__media cga-ph">Featured firearm<br />appears when inventory syncs</div>
		</div>
		<?php endif; ?>
	</section>
</div>
<!-- /wp:html -->
