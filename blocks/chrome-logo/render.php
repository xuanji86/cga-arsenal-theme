<?php
/**
 * OSA · Logo — links home with a variant-aware logo image.
 *
 * The header prefers the WordPress Custom Logo (useCustomLogo) and falls back to
 * an OSA logo variant; the dark footer uses the light banner variant. Reproduces
 * the logic that lived inline in the header/footer patterns so the chrome can be
 * composed from blocks in the Site Editor.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$variant    = isset( $attributes['variant'] ) ? sanitize_file_name( $attributes['variant'] ) : 'inline-dark';
$use_custom = ! empty( $attributes['useCustomLogo'] );

// Header logo: honor the Customizer logo source and switch to a light variant
// when the header is set to dark (a dark PNG logo would vanish on dark chrome).
if ( ! empty( $attributes['headerLogo'] ) ) {
	$tone   = get_theme_mod( 'osa_dark_header' ) ? 'light' : 'dark';
	$source = get_theme_mod( 'osa_header_logo', 'custom' );
	if ( 'custom' === $source && 'dark' === $tone ) {
		$use_custom = true;
		$variant    = 'inline-dark';
	} else {
		$use_custom = false;
		$family     = in_array( $source, [ 'inline', 'stacked', 'mark', 'banner' ], true ) ? $source : 'inline';
		$variant    = $family . '-' . $tone;
	}
}

$src = '';
if ( $use_custom ) {
	$cid = get_theme_mod( 'custom_logo' );
	if ( $cid ) {
		$src = wp_get_attachment_image_url( $cid, 'full' );
	}
}
if ( ! $src && function_exists( 'osa_logo_url' ) ) {
	$src = osa_logo_url( $variant );
}
if ( ! $src ) {
	return '';
}

$name      = get_bloginfo( 'name' );
$img_class = trim( 'osa-logo ' . ( isset( $attributes['imgClass'] ) ? (string) $attributes['imgClass'] : '' ) );
$wrapper   = function_exists( 'get_block_wrapper_attributes' )
	? get_block_wrapper_attributes( [ 'class' => 'osa-chrome-logo' ] )
	: 'class="osa-chrome-logo"';
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( $name ); ?> home">
	<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $img_class ); ?>" />
</a>
