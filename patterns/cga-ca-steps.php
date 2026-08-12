<?php
/**
 * Title: CGA · California Four-Step
 * Slug: cga-arsenal/ca-steps
 * Categories: osa-section
 * Inserter: yes
 *
 * The four-step California purchase sequence. Customizer-driven
 * (CGA · Homepage → Buying in California): title + body fields per step;
 * the 01–04 numbers render from position.
 *
 * @package OSA_Arsenal
 */

?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_steps_heading', CGA_STEPS_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><a href="<?php echo esc_url( cga_text( 'cga_steps_aside_url', CGA_STEPS_ASIDE_URL_DEFAULT ) ); ?>"><?php echo esc_html( cga_text( 'cga_steps_aside_label', CGA_STEPS_ASIDE_LABEL_DEFAULT ) ); ?></a></p>
		</div>
		<div class="cga-steps">
			<?php foreach ( CGA_STEPS as $cga_i => [ $cga_dt, $cga_db ] ) :
				$cga_n = $cga_i + 1;
				?>
			<div class="cga-step"><span class="cga-step__n"><?php echo esc_html( sprintf( '%02d', $cga_n ) ); ?></span><h3 class="cga-step__t"><?php echo esc_html( cga_text( "cga_step{$cga_n}_title", $cga_dt ) ); ?></h3><p class="cga-step__d"><?php echo esc_html( cga_text( "cga_step{$cga_n}_body", $cga_db ) ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
