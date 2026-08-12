<?php
/**
 * Title: CGA · California Four-Step
 * Slug: cga-arsenal/ca-steps
 * Categories: osa-section
 * Inserter: yes
 *
 * The four-step California purchase sequence. Customizer-driven
 * (CGA · Homepage → California four-step): one textarea per step,
 * lines = title / body; numbers render from position.
 *
 * @package OSA_Arsenal
 */

list( $cga_aside_label, $cga_aside_url ) = cga_link( 'cga_steps_aside', CGA_STEPS_ASIDE_DEFAULT );
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_steps_heading', CGA_STEPS_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><a href="<?php echo esc_url( $cga_aside_url ); ?>"><?php echo esc_html( $cga_aside_label ); ?></a></p>
		</div>
		<div class="cga-steps">
			<?php foreach ( CGA_STEP_DEFAULTS as $cga_i => $cga_default ) :
				$cga_step = cga_lines( 'cga_step_' . ( $cga_i + 1 ), $cga_default, 2 );
				?>
			<div class="cga-step"><span class="cga-step__n"><?php echo esc_html( sprintf( '%02d', $cga_i + 1 ) ); ?></span><h3 class="cga-step__t"><?php echo esc_html( $cga_step[0] ); ?></h3><p class="cga-step__d"><?php echo esc_html( $cga_step[1] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
