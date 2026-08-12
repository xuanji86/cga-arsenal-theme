<?php
/**
 * Title: CGA · Fulfilment Rail
 * Slug: cga-arsenal/fulfilment-rail
 * Categories: osa-section
 * Inserter: yes
 *
 * The three-way "how your order gets to you" rail. Copy is Customizer-driven
 * (CGA · Homepage → How your order gets to you): one labeled field per
 * element of each box.
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
			<?php foreach ( CGA_RAIL_CELLS as $cga_i => [ $cga_dk, $cga_dt, $cga_db, $cga_dn ] ) :
				$cga_n = $cga_i + 1;
				?>
			<div class="cga-rail__cell">
				<span class="cga-rail__num"><?php echo esc_html( cga_text( "cga_rail_cell{$cga_n}_kicker", $cga_dk ) ); ?></span>
				<h3 class="cga-rail__title"><?php echo esc_html( cga_text( "cga_rail_cell{$cga_n}_title", $cga_dt ) ); ?></h3>
				<p class="cga-rail__body"><?php echo esc_html( cga_text( "cga_rail_cell{$cga_n}_body", $cga_db ) ); ?></p>
				<p class="cga-rail__note"><?php echo esc_html( cga_text( "cga_rail_cell{$cga_n}_note", $cga_dn ) ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
