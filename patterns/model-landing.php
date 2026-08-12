<?php
/**
 * Title: Page · Model Landing
 * Slug: osa-arsenal/model-landing
 * Categories: osa-section
 * Description: Content skeleton for a /firearms/<model>/ landing page (spec C4): breadcrumb, eyebrow, display H1, serif intro, live-inventory section heading, catalog-grid, sales copy, FAQ, buyer's-guide cross-link, model chips. Example values = the SKS page; build real pages from docs/model-landing-template.md (params are frozen in the run's page-params.md — never derive pa_model slugs by hand).
 *
 * Static markup on purpose — the pattern is a paste-and-fill skeleton, so it
 * must not read theme mods or invent copy. Container notes:
 *   - page.html's <main> is bare, so the outer group carries the 1440px
 *     constraint + 40/24/56 padding itself; the .osa-model-landing class lets
 *     the existing ≤1024 CSS block hand the side gutters back to main.
 *   - H1/h2 keep inline font-size (64px/22px) — the existing [style*=]
 *     scaling selectors step them down for free (design-spec §12.2).
 *   - The copy/FAQ/guide zone is the .osa-landing-cols group (v1.4 §13 V4-3):
 *     desktop >1024 it grids into two columns (left = prose + guide card,
 *     right = FAQ; areas "prose faq"/"guide faq"), ≤1024 the group's own
 *     constrained-760/left layout takes back over as the single column. DOM
 *     stays 卖点→FAQ→guide (reading/mobile order). The FAQ block carries NO
 *     margin attrs — top-alignment needs them zero on desktop; the ≤1024
 *     block in osa-base.css restores the 56/48 stack rhythm.
 *   - Pages WITHOUT a buyer's guide (M1 Garand, M1903, Arisaka, CZ) delete
 *     the wp:html guide card below; desktop keeps prose left / FAQ right,
 *     mobile keeps the chips gap intact (FAQ margin collapses into the
 *     group's 56px).
 */
?>
<!-- wp:group {"className":"osa-model-landing","style":{"spacing":{"padding":{"top":"40px","right":"24px","bottom":"56px","left":"24px"}}},"layout":{"type":"constrained","contentSize":"1440px"}} -->
<div class="wp-block-group osa-model-landing" style="padding-top:40px;padding-right:24px;padding-bottom:56px;padding-left:24px">

	<!-- wp:osa/seo-breadcrumb /-->

	<!-- wp:paragraph {"className":"osa-mono-caps","style":{"spacing":{"margin":{"top":"16px","bottom":"8px"}},"typography":{"fontSize":"10px","textTransform":"uppercase","letterSpacing":"0.22em"}},"textColor":"steel-1"} -->
	<p class="osa-mono-caps has-steel-1-color has-text-color" style="margin-top:16px;margin-bottom:8px;font-size:10px;text-transform:uppercase;letter-spacing:0.22em">FIREARMS · COLD WAR SEMI-AUTO RIFLES</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":1,"style":{"spacing":{"margin":{"bottom":"16px"}},"typography":{"fontSize":"64px","textTransform":"uppercase","letterSpacing":"-0.005em","lineHeight":"1"}},"fontFamily":"display"} -->
	<h1 class="wp-block-heading has-display-font-family" style="margin-bottom:16px;font-size:64px;text-transform:uppercase;letter-spacing:-0.005em;line-height:1">SKS Rifles</h1>
	<!-- /wp:heading -->

	<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"48px"}}},"layout":{"type":"constrained","contentSize":"760px","justifyContent":"left"}} -->
	<div class="wp-block-group" style="margin-bottom:48px">

		<!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","lineHeight":"1.65"}},"fontFamily":"serif"} -->
		<p class="has-serif-font-family" style="font-size:18px;line-height:1.65">Chinese Type 56s, Soviet Tula SKS-45s and Yugoslav 59/66 grenadiers — each SKS for sale here is inspected, graded and photographed as the exact rifle you'll receive.</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:html -->
	<div class="osa-section-hd">
		<h2 class="has-display-font-family" style="font-size:22px;text-transform:uppercase;letter-spacing:0.04em">In the Case Right Now</h2>
		<span class="osa-mono-caps osa-section-hd__tag">LIVE INVENTORY — ONE OF EACH</span>
	</div>
	<!-- /wp:html -->

	<!-- wp:osa/catalog-grid {"cols":3,"limit":12,"model":"sks,type-56,type-26,sks-45,59-66","instock":true,"list":"model_landing_sks","schema":"collection"} /-->

	<!-- wp:group {"className":"osa-landing-cols","style":{"spacing":{"margin":{"top":"56px","bottom":"56px"}}},"layout":{"type":"constrained","contentSize":"760px","justifyContent":"left"}} -->
	<div class="wp-block-group osa-landing-cols" style="margin-top:56px;margin-bottom:56px">

		<!-- wp:group {"className":"osa-landing-prose","layout":{"type":"default"}} -->
		<div class="wp-block-group osa-landing-prose">

			<!-- wp:heading {"style":{"spacing":{"margin":{"bottom":"16px"}},"typography":{"fontSize":"22px","textTransform":"uppercase","letterSpacing":"0.04em"}},"fontFamily":"display"} -->
			<h2 class="wp-block-heading has-display-font-family" style="margin-bottom:16px;font-size:22px;text-transform:uppercase;letter-spacing:0.04em">The SKS at Old Steel Arsenal</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"16px"}},"typography":{"fontSize":"15px","lineHeight":"1.7"}}} -->
			<p style="margin-bottom:16px;font-size:15px;line-height:1.7">The shelf runs from Chinese Type 56s — spike bayonets, factory arsenal marks, sometimes a Factory 26 receiver — to Soviet Tula SKS-45s and Yugoslav 59/66 grenadiers. Different arsenals, same rugged 7.62x39 semi-auto.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"16px"}},"typography":{"fontSize":"15px","lineHeight":"1.7"}}} -->
			<p style="margin-bottom:16px;font-size:15px;line-height:1.7">Arsenal refurbs are the rule with these rifles, so we photograph the serials on receiver, bolt and stock and grade every rifle individually. The listing you click is the rifle that ships — one of each, gone when it's gone.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:group -->

		<!-- wp:osa/model-faq {"items":[{"q":"How does buying a firearm online from Old Steel Arsenal work?","a":"Every firearm ships to a licensed FFL dealer of your choice — pick your dealer at checkout and we handle the transfer paperwork and shipping. See our <a href='/ffl-shipping/'>FFL &amp; Shipping</a> page for the full process."},{"q":"What's the difference between a Chinese Type 56 and a Russian SKS-45?","a":"The Type 56 is China's licensed copy, often with a spike bayonet and factory arsenal marks (like Factory 26); Soviet SKS-45s came from Tula or Izhevsk with blade bayonets. Both shoot the same 7.62x39."},{"q":"Do SKS rifles have matching numbers?","a":"Many were arsenal-refurbished, so forced-matching is common. Each listing photographs the serials on receiver, bolt and stock so you can judge for yourself."}]} /-->

		<!-- wp:html -->
		<a class="osa-guide-xlink" href="/sks-rifles-buyers-guide/">
			<span class="osa-guide-xlink__kicker">Field Guide</span>
			<span class="osa-guide-xlink__line">New to the SKS? Read our buyer's guide →</span>
		</a>
		<!-- /wp:html -->

	</div>
	<!-- /wp:group -->

	<!-- wp:osa/model-links {"variant":"chips","heading":"More from the collection"} /-->

</div>
<!-- /wp:group -->
