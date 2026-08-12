<?php
/**
 * Title: Section · Story strip (dark)
 * Slug: osa-arsenal/story-strip
 * Categories: osa-section
 * Description: Black band with serif quote and armorer attribution. Copy reads from Customizer.
 */
$label       = osa_text( 'osa_story_label',       OSA_STORY_LABEL_DEFAULT );
$quote       = osa_text( 'osa_story_quote',       OSA_STORY_QUOTE_DEFAULT );
$attribution = osa_text( 'osa_story_attribution', OSA_STORY_ATTRIBUTION_DEFAULT );
?>
<!-- wp:group {"backgroundColor":"ink","textColor":"paper","style":{"spacing":{"padding":{"top":"56px","right":"56px","bottom":"56px","left":"56px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-paper-color has-ink-background-color has-text-color has-background" style="padding-top:56px;padding-right:56px;padding-bottom:56px;padding-left:56px">

	<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"56px"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-center">

		<!-- wp:column {"verticalAlignment":"center","width":"180px"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:180px">
			<!-- wp:html -->
			<p class="osa-mono-caps osa-story-label" style="font-size:10px;color:var(--osa-paper-edge);letter-spacing:0.22em;margin:0"><?php echo esc_html( strtoupper( $label ) ); ?></p>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:paragraph {"className":"osa-story-quote","style":{"typography":{"fontStyle":"italic","fontSize":"28px","lineHeight":"1.35"}},"fontFamily":"serif-display"} -->
			<p class="osa-story-quote has-serif-display-font-family" style="font-style:italic;font-size:28px;line-height:1.35"><?php echo esc_html( '"' . trim( $quote, '"“”' ) . '"' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:html -->
			<p class="osa-mono-caps osa-story-attribution" style="font-size:10px;color:var(--osa-paper-edge);letter-spacing:0.22em;margin:18px 0 0"><?php echo esc_html( $attribution ); ?></p>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
