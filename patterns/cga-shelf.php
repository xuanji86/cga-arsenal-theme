<?php
/**
 * Title: CGA · On the Shelf
 * Slug: cga-arsenal/shelf
 * Categories: osa-section
 * Inserter: yes
 *
 * Newest in-stock inventory, 8-up on the OSA catalog grid (cards carry the
 * CGA brand line, compliance chips and stock count).
 *
 * @package OSA_Arsenal
 */

$cga_shelf_count = wp_count_posts( 'product' );
$cga_shelf_count = $cga_shelf_count ? (int) $cga_shelf_count->publish : 0;
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title">On the shelf this week</h2>
			<p class="cga-section__aside"><a href="/shop/">Updated as the POS receives · View all <?php echo esc_html( (string) $cga_shelf_count ); ?> &rarr;</a></p>
		</div>
	</section>
</div>
<!-- /wp:html -->

<!-- wp:group {"className":"cga-page","style":{"spacing":{"margin":{"top":"-48px"},"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group cga-page" style="margin-top:-48px">
	<!-- wp:osa/catalog-grid {"cols":4,"limit":8,"instock":true} /-->
	<!-- wp:spacer {"height":"72px"} -->
	<div style="height:72px" aria-hidden="true" class="wp-block-spacer"></div>
	<!-- /wp:spacer -->
</div>
<!-- /wp:group -->
