<?php
/**
 * Title: CGA · Home Hero
 * Slug: cga-arsenal/home-hero
 * Categories: osa-section
 * Inserter: yes
 *
 * Split hero: statement + store facts left, one featured firearm right.
 * All copy is Customizer-driven (CGA · Homepage → Hero); the featured card
 * is the newest in-stock product unless a SKU is pinned in the Customizer.
 *
 * @package OSA_Arsenal
 */

$cga_feat = function_exists( 'cga_featured_product' ) ? cga_featured_product() : null;

list( $cga_cta1_label, $cga_cta1_url ) = cga_link( 'cga_hero_cta1', CGA_HERO_CTA1_DEFAULT );
list( $cga_cta2_label, $cga_cta2_url ) = cga_link( 'cga_hero_cta2', CGA_HERO_CTA2_DEFAULT );
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-hero">
		<div>
			<p class="cga-eyebrow"><?php echo esc_html( cga_text( 'cga_hero_eyebrow', CGA_HERO_EYEBROW_DEFAULT ) ); ?></p>
			<h1 class="cga-h1"><?php echo cga_multiline_html( cga_text( 'cga_hero_headline', CGA_HERO_HEADLINE_DEFAULT ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></h1>
			<p class="cga-lede"><?php echo esc_html( cga_text( 'cga_hero_lede', CGA_HERO_LEDE_DEFAULT ) ); ?></p>
			<div class="home-hero__cta">
				<a class="cga-btn cga-btn--pine" href="<?php echo esc_url( $cga_cta1_url ); ?>"><?php echo esc_html( $cga_cta1_label ); ?></a>
				<a class="cga-btn cga-btn--ghost" href="<?php echo esc_url( $cga_cta2_url ); ?>"><?php echo esc_html( $cga_cta2_label ); ?></a>
			</div>
			<div class="home-hero__meta">
				<?php foreach ( cga_lines( 'cga_hero_stats', CGA_HERO_STATS_DEFAULT ) as $cga_stat ) :
					if ( '' === $cga_stat ) { continue; }
					$cga_parts = array_map( 'trim', explode( '|', $cga_stat, 2 ) );
					?>
				<span class="home-stat"><b><?php echo esc_html( $cga_parts[0] ); ?></b><span><?php echo esc_html( $cga_parts[1] ?? '' ); ?></span></span>
				<?php endforeach; ?>
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
