<?php
/**
 * Title: Section · Shop by Model (Homepage)
 * Slug: osa-arsenal/model-index-strip
 * Categories: osa-section
 * Description: Paper-tint "Shop by Model" band (spec C5): floor-section's heading + grid + centered outline button three-part rhythm, with the grid swapped for osa/model-links cards and an All Models button to /firearms/. Cards read from Customize → OSA · Model Pages.
 *
 * Guard: an emptied `osa_model_links` theme mod suppresses the WHOLE band —
 * tint background, heading and button included — so clearing the Customizer
 * list never strands an empty gray shell on the homepage (G4/D5). Same
 * theme-mod-at-render mechanism as patterns/floor-section.php.
 */
if ( ! function_exists( 'osa_model_links_items' ) || ! osa_model_links_items() ) {
	return;
}
?>
<!-- wp:group {"className":"osa-model-strip","backgroundColor":"paper-tint","style":{"spacing":{"padding":{"top":"48px","right":"24px","bottom":"48px","left":"24px"}}},"layout":{"type":"constrained","contentSize":"1440px"}} -->
<div class="wp-block-group osa-model-strip has-paper-tint-background-color has-background" style="padding-top:48px;padding-right:24px;padding-bottom:48px;padding-left:24px">

	<!-- wp:heading {"level":2,"className":"osa-strip-heading","style":{"border":{"bottom":{"color":"var:preset|color|ink","width":"1px"}},"spacing":{"padding":{"bottom":"24px"},"margin":{"bottom":"24px"}},"typography":{"fontSize":"22px","textTransform":"uppercase","letterSpacing":"0.04em"}},"fontFamily":"display"} -->
	<h2 class="wp-block-heading osa-strip-heading has-display-font-family" style="border-bottom-color:var(--wp--preset--color--ink);border-bottom-width:1px;padding-bottom:24px;margin-bottom:24px;font-size:22px;text-transform:uppercase;letter-spacing:0.04em">Shop by Model</h2>
	<!-- /wp:heading -->

	<!-- wp:osa/model-links {"variant":"cards"} /-->

	<!-- wp:html -->
	<div class="wp-block-buttons" style="display:flex;justify-content:center;margin-top:24px">
		<div class="wp-block-button is-style-outline">
			<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( site_url( '/firearms/' ) ); ?>">All Models →</a>
		</div>
	</div>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
