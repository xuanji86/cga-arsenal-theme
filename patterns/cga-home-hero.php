<?php
/**
 * Title: CGA · Home Hero
 * Slug: cga-arsenal/home-hero
 * Categories: osa-section
 * Inserter: yes
 *
 * Split hero: statement + store facts left, one featured firearm right.
 * All copy is Customizer-driven (CGA · Homepage → Hero); the featured card
 * is the product picked in the Customizer dropdown (or the newest in-stock
 * product on "Automatic").
 *
 * @package OSA_Arsenal
 */

$cga_feat = function_exists( 'cga_featured_product' ) ? cga_featured_product() : null;

$cga_d = CGA_HERO_DEFAULTS;
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-hero">
		<div>
			<p class="cga-eyebrow"><?php echo esc_html( cga_text( 'cga_hero_eyebrow', $cga_d['cga_hero_eyebrow'] ) ); ?></p>
			<h1 class="cga-h1"><?php echo cga_multiline_html( cga_text( 'cga_hero_headline', $cga_d['cga_hero_headline'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></h1>
			<p class="cga-lede"><?php echo esc_html( cga_text( 'cga_hero_lede', $cga_d['cga_hero_lede'] ) ); ?></p>
			<div class="home-hero__cta">
				<a class="cga-btn cga-btn--pine" href="<?php echo esc_url( cga_text( 'cga_hero_cta1_url', $cga_d['cga_hero_cta1_url'] ) ); ?>"><?php echo esc_html( cga_text( 'cga_hero_cta1_label', $cga_d['cga_hero_cta1_label'] ) ); ?></a>
				<a class="cga-btn cga-btn--ghost" href="<?php echo esc_url( cga_text( 'cga_hero_cta2_url', $cga_d['cga_hero_cta2_url'] ) ); ?>"><?php echo esc_html( cga_text( 'cga_hero_cta2_label', $cga_d['cga_hero_cta2_label'] ) ); ?></a>
			</div>
			<div class="home-hero__meta">
				<?php foreach ( CGA_HERO_STATS as $cga_i => [ $cga_dv, $cga_dl ] ) :
					$cga_n = $cga_i + 1;
					?>
				<span class="home-stat"><b><?php echo esc_html( cga_text( "cga_hero_stat{$cga_n}_value", $cga_dv ) ); ?></b><span><?php echo esc_html( cga_text( "cga_hero_stat{$cga_n}_label", $cga_dl ) ); ?></span></span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $cga_feat ) : ?>
		<div class="home-feat">
			<span class="home-feat__tag"><?php echo esc_html( cga_text( 'cga_hero_feat_tag', $cga_d['cga_hero_feat_tag'] ) ); ?></span>
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
			<span class="home-feat__tag"><?php echo esc_html( cga_text( 'cga_hero_feat_tag', $cga_d['cga_hero_feat_tag'] ) ); ?></span>
			<div class="home-feat__media cga-ph">Featured firearm<br />appears when inventory syncs</div>
		</div>
		<?php endif; ?>
	</section>
</div>
<!-- /wp:html -->
