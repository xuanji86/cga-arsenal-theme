<?php
/**
 * Title: Section · The Floor (Homepage)
 * Slug: osa-arsenal/floor-section
 * Categories: osa-section
 * Description: "The Floor · Available Lots" heading + 4-up product grid + dynamic count View-All button. All copy reads from Customizer.
 */
$heading    = osa_text( 'osa_floor_heading',         OSA_FLOOR_HEADING_DEFAULT );
$cta_label  = osa_format_label_with_count( osa_text( 'osa_floor_view_all_label', OSA_FLOOR_CTA_DEFAULT ) );
$cols    = max( 2, min( 6, (int) get_theme_mod( 'osa_floor_cols', 4 ) ) );
$rows    = max( 1, min( 8, (int) get_theme_mod( 'osa_floor_rows', 3 ) ) );
$limit   = $cols * $rows;
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"32px","right":"24px","bottom":"48px","left":"24px"}}},"layout":{"type":"constrained","contentSize":"1440px"}} -->
<div class="wp-block-group" style="padding-top:32px;padding-right:24px;padding-bottom:48px;padding-left:24px">

	<!-- wp:heading {"level":2,"className":"osa-floor-heading","style":{"typography":{"fontSize":"22px","textTransform":"uppercase","letterSpacing":"0.04em"},"spacing":{"padding":{"bottom":"24px"}},"border":{"bottom":{"color":"var:preset|color|ink","width":"1px"}}},"fontFamily":"display"} -->
	<h2 class="wp-block-heading osa-floor-heading has-display-font-family" style="border-bottom-color:var(--wp--preset--color--ink);border-bottom-width:1px;padding-bottom:24px;font-size:22px;text-transform:uppercase;letter-spacing:0.04em"><?php echo esc_html( $heading ); ?></h2>
	<!-- /wp:heading -->

	<?php printf(
		'<!-- wp:osa/catalog-grid {"cols":%d,"limit":%d,"orderby":"date","order":"DESC"} /-->',
		$cols,
		$limit
	); ?>

	<!-- wp:html -->
	<div class="wp-block-buttons" style="display:flex;justify-content:center;margin-top:32px">
		<div class="wp-block-button is-style-outline">
			<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( site_url( '/shop/' ) ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		</div>
	</div>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
