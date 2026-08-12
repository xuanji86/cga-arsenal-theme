<?php
/**
 * Title: CGA · Fulfilment Rail
 * Slug: cga-arsenal/fulfilment-rail
 * Categories: osa-section
 * Inserter: yes
 *
 * The three-way "how your order gets to you" rail. Copy is Customizer-driven
 * (CGA · Homepage → How your order gets to you): one textarea per cell,
 * lines = kicker / title / body / note.
 *
 * @package OSA_Arsenal
 */
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_rail_heading', CGA_RAIL_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><?php echo esc_html( cga_text( 'cga_rail_aside', CGA_RAIL_ASIDE_DEFAULT ) ); ?></p>
		</div>
		<div class="cga-rail">
			<?php foreach ( CGA_RAIL_CELL_DEFAULTS as $cga_i => $cga_default ) :
				$cga_cell = cga_lines( 'cga_rail_cell_' . ( $cga_i + 1 ), $cga_default, 4 );
				?>
			<div class="cga-rail__cell">
				<span class="cga-rail__num"><?php echo esc_html( $cga_cell[0] ); ?></span>
				<h3 class="cga-rail__title"><?php echo esc_html( $cga_cell[1] ); ?></h3>
				<p class="cga-rail__body"><?php echo esc_html( $cga_cell[2] ); ?></p>
				<p class="cga-rail__note"><?php echo esc_html( $cga_cell[3] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
