<?php
/**
 * Title: CGA · Member Band + Store Card
 * Slug: cga-arsenal/member-band-store
 * Categories: osa-section
 * Inserter: yes
 *
 * Full-bleed pine membership band, then the store card. Customizer-driven:
 * band/store copy under CGA · Homepage → Member band + store card; the
 * address / hours / phone / email rows come from Store Facts (site-wide).
 *
 * @package OSA_Arsenal
 */

$cga_d = CGA_BAND_STORE_DEFAULTS;
?>
<!-- wp:html -->
<div class="cga-band home-band-wrap">
	<div class="cga-band__inner">
		<div>
			<p class="cga-band__eyebrow"><?php echo esc_html( cga_text( 'cga_band_eyebrow', $cga_d['cga_band_eyebrow'] ) ); ?></p>
			<h2 class="cga-band__title"><?php echo esc_html( cga_text( 'cga_band_title', $cga_d['cga_band_title'] ) ); ?></h2>
			<p class="cga-band__body"><?php echo esc_html( cga_text( 'cga_band_body', $cga_d['cga_band_body'] ) ); ?></p>
		</div>
		<a class="cga-btn" href="<?php echo esc_url( cga_text( 'cga_band_cta_url', $cga_d['cga_band_cta_url'] ) ); ?>"><?php echo esc_html( cga_text( 'cga_band_cta_label', $cga_d['cga_band_cta_label'] ) ); ?></a>
	</div>
</div>

<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_store_heading', $cga_d['cga_store_heading'] ) ); ?></h2>
			<p class="cga-section__aside"><?php echo esc_html( cga_text( 'cga_store_aside', $cga_d['cga_store_aside'] ) ); ?></p>
		</div>
		<div class="cga-store">
			<div class="cga-store__body">
				<div class="cga-store__rows">
					<div><p class="cga-store__k">Address</p><p class="cga-store__v"><?php echo cga_multiline_html( cga_fact( 'address' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></p></div>
					<div><p class="cga-store__k">Hours</p><p class="cga-store__v"><?php echo cga_multiline_html( cga_fact( 'hours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></p></div>
					<div><p class="cga-store__k">Phone</p><p class="cga-store__v"><?php echo esc_html( cga_fact( 'phone' ) ); ?></p></div>
					<div><p class="cga-store__k">Email</p><p class="cga-store__v"><?php echo esc_html( cga_fact( 'email' ) ); ?></p></div>
				</div>
				<p class="cga-lede" style="margin-top:6px"><?php echo esc_html( cga_text( 'cga_store_blurb', $cga_d['cga_store_blurb'] ) ); ?></p>
				<?php
				$cga_store_url    = cga_text( 'cga_store_cta_url', $cga_d['cga_store_cta_url'] );
				$cga_store_is_ext = str_starts_with( $cga_store_url, 'http' );
				?>
				<div><a class="cga-btn cga-btn--pine" href="<?php echo esc_url( $cga_store_url ); ?>"<?php echo $cga_store_is_ext ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( cga_text( 'cga_store_cta_label', $cga_d['cga_store_cta_label'] ) ); ?></a></div>
			</div>
			<div class="cga-store__map"><?php echo cga_map_html( cga_text( 'cga_store_map_note', $cga_d['cga_store_map_note'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- builds escaped markup. ?></div>
		</div>
	</section>
</div>
<!-- /wp:html -->
