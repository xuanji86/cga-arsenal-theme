<?php
/**
 * Title: CGA · At the Counter + Training
 * Slug: cga-arsenal/services-training
 * Categories: osa-section
 * Inserter: yes
 *
 * Counter services 4-up and the two training courses. Customizer-driven
 * (CGA · Homepage → At the counter + training): services = kicker / title /
 * body per line; courses = title / body / meta / button "Label | URL".
 *
 * @package OSA_Arsenal
 */

list( $cga_svc_aside_label, $cga_svc_aside_url ) = cga_link( 'cga_svc_aside', CGA_SVC_ASIDE_DEFAULT );
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_svc_heading', CGA_SVC_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><a href="<?php echo esc_url( $cga_svc_aside_url ); ?>"><?php echo esc_html( $cga_svc_aside_label ); ?></a></p>
		</div>
		<div class="cga-svc">
			<?php foreach ( CGA_SVC_CELL_DEFAULTS as $cga_i => $cga_default ) :
				$cga_cell = cga_lines( 'cga_svc_cell_' . ( $cga_i + 1 ), $cga_default, 3 );
				?>
			<div class="cga-svc__cell"><span class="cga-svc__k"><?php echo esc_html( $cga_cell[0] ); ?></span><h3 class="cga-svc__t"><?php echo esc_html( $cga_cell[1] ); ?></h3><p class="cga-svc__d"><?php echo esc_html( $cga_cell[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_train_heading', CGA_TRAIN_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><?php echo esc_html( cga_text( 'cga_train_aside', CGA_TRAIN_ASIDE_DEFAULT ) ); ?></p>
		</div>
		<div class="home-train">
			<?php foreach ( CGA_TRAIN_CELL_DEFAULTS as $cga_i => $cga_default ) :
				$cga_cell = cga_lines( 'cga_train_cell_' . ( $cga_i + 1 ), $cga_default, 4 );
				$cga_btn  = array_map( 'trim', explode( '|', $cga_cell[3], 2 ) );
				$cga_meta = array_filter( array_map( 'trim', explode( '·', $cga_cell[2] ) ), 'strlen' );
				?>
			<div class="home-train__cell">
				<h3 class="cga-svc__t"><?php echo esc_html( $cga_cell[0] ); ?></h3>
				<p class="cga-svc__d"><?php echo esc_html( $cga_cell[1] ); ?></p>
				<div class="home-train__meta"><?php foreach ( $cga_meta as $cga_m ) : ?><span><?php echo esc_html( $cga_m ); ?></span><?php endforeach; ?></div>
				<div class="home-train__push"><a class="cga-btn cga-btn--ghost" href="<?php echo esc_url( ( $cga_btn[1] ?? '' ) !== '' ? $cga_btn[1] : '#' ); ?>"><?php echo esc_html( $cga_btn[0] ); ?></a></div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
