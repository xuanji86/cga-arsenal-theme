<?php
/**
 * OSA · Menu — renders a registered nav menu by location.
 *
 * Wraps wp_nav_menu so a header/footer template part can reference the theme's
 * existing classic menus (Appearance → Menus) as blocks, without migrating them
 * to the navigation block. footerStyle uses the footer walker + list markup.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$location = isset( $attributes['location'] ) ? sanitize_key( $attributes['location'] ) : '';

if ( ! empty( $attributes['headerStyle'] ) ) {
	$acct           = osa_account_link_info();
	$mobile_account = sprintf(
		'<a href="%s" class="osa-main-nav__account-link" data-osa-mobile-only="1">%s</a>',
		esc_url( $acct['url'] ),
		esc_html( $acct['label'] )
	);
	echo wp_nav_menu( [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — wp_nav_menu returns escaped markup.
		'theme_location'       => $location ? $location : 'primary',
		'container'            => 'nav',
		'container_class'      => 'osa-main-nav',
		'container_id'         => 'osa-mobile-nav', // referenced by the menu toggle's aria-controls.
		'container_aria_label' => __( 'Primary', 'osa-arsenal' ),
		'items_wrap'           => '%3$s' . $mobile_account,
		'walker'               => class_exists( 'OSA_Header_Nav_Walker' ) ? new OSA_Header_Nav_Walker() : '',
		'fallback_cb'          => function_exists( 'osa_header_nav_fallback' ) ? 'osa_header_nav_fallback' : '__return_empty_string',
		'depth'                => 1,
		'echo'                 => false,
	] );
	return;
}

if ( '' === $location ) {
	return '';
}

$args = [
	'theme_location' => $location,
	'container'      => false,
	'menu_class'     => isset( $attributes['menuClass'] ) ? (string) $attributes['menuClass'] : '',
	'depth'          => isset( $attributes['depth'] ) ? (int) $attributes['depth'] : 0,
	'fallback_cb'    => '__return_empty_string',
	'echo'           => false,
];
if ( ! empty( $attributes['footerStyle'] ) && class_exists( 'OSA_Footer_Nav_Walker' ) ) {
	$args['walker'] = new OSA_Footer_Nav_Walker();
}

echo wp_nav_menu( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — wp_nav_menu returns escaped markup.
