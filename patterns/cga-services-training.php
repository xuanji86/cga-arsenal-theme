<?php
/**
 * Title: CGA · At the Counter + Training
 * Slug: cga-arsenal/services-training
 * Categories: osa-section
 * Inserter: yes
 *
 * Counter services 4-up and the two training courses. Customizer-driven
 * (CGA · Homepage → Counter services & training): one labeled field per
 * element; course details line splits on · into the meta row.
 *
 * @package OSA_Arsenal
 */

?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_svc_heading', CGA_SVC_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><a href="<?php echo esc_url( cga_text( 'cga_svc_aside_url', CGA_SVC_ASIDE_URL_DEFAULT ) ); ?>"><?php echo esc_html( cga_text( 'cga_svc_aside_label', CGA_SVC_ASIDE_LABEL_DEFAULT ) ); ?></a></p>
		</div>
		<div class="cga-svc">
			<?php foreach ( CGA_SVCS as $cga_i => [ $cga_dk, $cga_dt, $cga_db ] ) :
				$cga_n = $cga_i + 1;
				?>
			<div class="cga-svc__cell"><span class="cga-svc__k"><?php echo esc_html( cga_text( "cga_svc{$cga_n}_kicker", $cga_dk ) ); ?></span><h3 class="cga-svc__t"><?php echo esc_html( cga_text( "cga_svc{$cga_n}_title", $cga_dt ) ); ?></h3><p class="cga-svc__d"><?php echo esc_html( cga_text( "cga_svc{$cga_n}_body", $cga_db ) ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_train_heading', CGA_TRAIN_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><?php echo esc_html( cga_text( 'cga_train_aside', CGA_TRAIN_ASIDE_DEFAULT ) ); ?></p>
		</div>
		<div class="home-train">
			<?php foreach ( CGA_COURSES as $cga_i => [ $cga_dt, $cga_db, $cga_dm, $cga_dbl, $cga_dbu ] ) :
				$cga_n    = $cga_i + 1;
				$cga_meta = array_filter( array_map( 'trim', explode( '·', cga_text( "cga_course{$cga_n}_meta", $cga_dm ) ) ), 'strlen' );
				?>
			<div class="home-train__cell">
				<h3 class="cga-svc__t"><?php echo esc_html( cga_text( "cga_course{$cga_n}_title", $cga_dt ) ); ?></h3>
				<p class="cga-svc__d"><?php echo esc_html( cga_text( "cga_course{$cga_n}_body", $cga_db ) ); ?></p>
				<div class="home-train__meta"><?php foreach ( $cga_meta as $cga_m ) : ?><span><?php echo esc_html( $cga_m ); ?></span><?php endforeach; ?></div>
				<div class="home-train__push"><a class="cga-btn cga-btn--ghost" href="<?php echo esc_url( cga_text( "cga_course{$cga_n}_btn_url", $cga_dbu ) ); ?>"><?php echo esc_html( cga_text( "cga_course{$cga_n}_btn_label", $cga_dbl ) ); ?></a></div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
