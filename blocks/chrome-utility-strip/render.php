<?php
/**
 * OSA · Utility Strip — the thin announcement strip above the header row.
 *
 * Reads the same Customizer settings as the old header pattern (message, links,
 * visibility toggle), so the round-1 "OSA · Header" controls keep working while
 * the strip becomes a block the owner can move/remove in the Site Editor.
 *
 * @var array $attributes
 */
defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'osa_header_strip_enabled', true ) ) {
	return '';
}
$msg = function_exists( 'osa_text' ) ? osa_text( 'osa_header_strip', OSA_HEADER_STRIP_DEFAULT ) : '';
?>
<div class="osa-utility-strip">
	<div class="osa-utility-strip__left">
		<span class="osa-utility-strip__msg"><?php echo esc_html( $msg ); ?></span>
	</div>
	<nav class="osa-utility-strip__right" aria-label="Utility">
		<?php foreach ( osa_header_strip_links() as $link ) : ?>
		<a href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['external'] ? ' class="osa-utility-strip__dealers" target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $link['label'] ); ?><?php if ( $link['external'] ) : ?>
			<svg class="osa-utility-strip__arrow" viewBox="0 0 10 10" width="9" height="9" aria-hidden="true" focusable="false">
				<path d="M2 8 L8 2 M4 2 H8 V6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		<?php endif; ?></a>
		<?php endforeach; ?>
	</nav>
</div>
<?php
