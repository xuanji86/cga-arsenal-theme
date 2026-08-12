<?php
/**
 * OSA · Promo Teaser — Independence Day strip between the hero and the
 * marquee. Empty output outside the promo window.
 *
 * @var array $attributes
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'osa_promo_active' ) || ! osa_promo_active() ) {
	return '';
}

$wrapper = get_block_wrapper_attributes( [ 'class' => 'osa-idteaser' ] );
?>
<a <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> href="<?php echo esc_url( home_url( '/' . OSA_PROMO_PAGE_SLUG . '/' ) ); ?>">
	<div class="osa-idteaser__main">
		<div class="osa-idteaser__stars" aria-hidden="true">★ ★ ★</div>
		<p class="osa-idteaser__eyebrow">Fourth of July · MMXXVI · Code JULY4 through July 14</p>
		<h2 class="osa-idteaser__title">Independence Day — The Arsenal of the Republic</h2>
		<p class="osa-idteaser__text">American iron &amp; arms from around the world for the Fourth. Use code JULY4 for 10% off through July 14 — hand-graded, honestly photographed, shipped after the holiday.</p>
	</div>
	<span class="osa-idteaser__cta osa-btn">Explore the collection →</span>
</a>
