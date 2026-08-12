<?php
/**
 * Title: Hero · Catalog-first
 * Slug: osa-arsenal/hero-catalog-first
 * Categories: osa-section
 * Description: Massive display headline + serif subtitle + dual CTAs.
 */
$hero_headline = osa_text( 'osa_hero_headline', OSA_HERO_HEADLINE_DEFAULT );
$hero_subhead  = osa_text( 'osa_hero_subheadline', OSA_HERO_SUBHEAD_DEFAULT );
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"56px","right":"24px","bottom":"40px","left":"24px"}}},"backgroundColor":"paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-paper-background-color has-background" style="padding-top:56px;padding-right:24px;padding-bottom:40px;padding-left:24px">

	<!-- wp:columns {"verticalAlignment":"bottom","style":{"spacing":{"blockGap":{"left":"64px"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-bottom">

		<!-- wp:column {"verticalAlignment":"bottom","width":"60%"} -->
		<div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:60%">
			<!-- wp:heading {"level":1,"className":"osa-hero-headline","style":{"typography":{"fontSize":"96px","textTransform":"uppercase","letterSpacing":"-0.02em","lineHeight":"0.92"},"spacing":{"margin":{"top":"0","bottom":"0"}}},"fontFamily":"display"} -->
			<h1 class="wp-block-heading osa-hero-headline has-display-font-family" style="margin-top:0;margin-bottom:0;font-size:96px;text-transform:uppercase;letter-spacing:-0.02em;line-height:0.92"><?php echo esc_html( $hero_headline ); ?></h1>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"bottom"} -->
		<div class="wp-block-column is-vertically-aligned-bottom">
			<!-- wp:paragraph {"className":"osa-hero-sub","style":{"typography":{"fontSize":"19px","fontStyle":"normal","lineHeight":"1.45"}},"fontFamily":"serif"} -->
			<p class="osa-hero-sub has-serif-font-family" style="font-size:19px;font-style:normal;line-height:1.45"><?php echo esc_html( $hero_subhead ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"layout":{"type":"flex"},"style":{"spacing":{"margin":{"top":"20px"},"blockGap":"12px"}}} -->
			<div class="wp-block-buttons" style="margin-top:20px;gap:12px">
				<!-- wp:button {"className":"is-style-fill"} -->
				<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/shop/">Browse Catalog →</a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/consignment/">Sell to OSA</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
