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

list( $cga_band_cta_label, $cga_band_cta_url )   = cga_link( 'cga_band_cta', CGA_BAND_CTA_DEFAULT );
list( $cga_store_cta_label, $cga_store_cta_url ) = cga_link( 'cga_store_cta', CGA_STORE_CTA_DEFAULT );
?>
<!-- wp:html -->
<div class="cga-band home-band-wrap">
	<div class="cga-band__inner">
		<div>
			<p class="cga-band__eyebrow"><?php echo esc_html( cga_text( 'cga_band_eyebrow', CGA_BAND_EYEBROW_DEFAULT ) ); ?></p>
			<h2 class="cga-band__title"><?php echo esc_html( cga_text( 'cga_band_title', CGA_BAND_TITLE_DEFAULT ) ); ?></h2>
			<p class="cga-band__body"><?php echo esc_html( cga_text( 'cga_band_body', CGA_BAND_BODY_DEFAULT ) ); ?></p>
		</div>
		<a class="cga-btn" href="<?php echo esc_url( $cga_band_cta_url ); ?>"><?php echo esc_html( $cga_band_cta_label ); ?></a>
	</div>
</div>

<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_store_heading', CGA_STORE_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><?php echo esc_html( cga_text( 'cga_store_aside', CGA_STORE_ASIDE_DEFAULT ) ); ?></p>
		</div>
		<div class="cga-store">
			<div class="cga-store__body">
				<div class="cga-store__rows">
					<div><p class="cga-store__k">Address</p><p class="cga-store__v"><?php echo cga_multiline_html( cga_fact( 'address' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></p></div>
					<div><p class="cga-store__k">Hours</p><p class="cga-store__v"><?php echo cga_multiline_html( cga_fact( 'hours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></p></div>
					<div><p class="cga-store__k">Phone</p><p class="cga-store__v"><?php echo esc_html( cga_fact( 'phone' ) ); ?></p></div>
					<div><p class="cga-store__k">Email</p><p class="cga-store__v"><?php echo esc_html( cga_fact( 'email' ) ); ?></p></div>
				</div>
				<p class="cga-lede" style="margin-top:6px"><?php echo esc_html( cga_text( 'cga_store_blurb', CGA_STORE_BLURB_DEFAULT ) ); ?></p>
				<div><a class="cga-btn cga-btn--pine" href="<?php echo esc_url( $cga_store_cta_url ); ?>"><?php echo esc_html( $cga_store_cta_label ); ?></a></div>
			</div>
			<div class="cga-store__map cga-ph"><?php echo cga_multiline_html( cga_text( 'cga_store_map_note', CGA_STORE_MAP_DEFAULT ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes per line. ?></div>
		</div>
	</section>
</div>
<!-- /wp:html -->
