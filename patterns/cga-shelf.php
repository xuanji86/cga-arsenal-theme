<?php
/**
 * Title: CGA · On the Shelf
 * Slug: cga-arsenal/shelf
 * Categories: osa-section
 * Inserter: yes
 *
 * In-stock inventory, 8-up on the OSA catalog grid. Heading/aside and which
 * products show (order: newest / daily / hourly rotation, newest kept at the
 * front, guns only vs everything) are Customizer-driven (CGA · Homepage →
 * Categories & product grid; {count} = live product count). Excludes the
 * hero's featured product so it never shows twice.
 *
 * @package OSA_Arsenal
 */

$cga_shelf_feat    = function_exists( 'cga_featured_product' ) ? cga_featured_product() : null;
$cga_shelf_exclude = $cga_shelf_feat ? (int) $cga_shelf_feat->get_id() : 0;
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_shelf_heading', CGA_SHELF_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><a href="/shop/"><?php echo esc_html( cga_text( 'cga_shelf_aside', CGA_SHELF_ASIDE_DEFAULT ) ); ?></a></p>
		</div>
	</section>
</div>
<!-- /wp:html -->

<!-- wp:group {"className":"cga-page","style":{"spacing":{"margin":{"top":"-48px"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group cga-page" style="margin-top:-48px">
	<!-- wp:osa/catalog-grid <?php echo wp_json_encode( cga_shelf_grid_attrs( $cga_shelf_exclude ) ); ?> /-->
	<!-- wp:spacer {"height":"72px"} -->
	<div style="height:72px" aria-hidden="true" class="wp-block-spacer"></div>
	<!-- /wp:spacer -->
</div>
<!-- /wp:group -->
