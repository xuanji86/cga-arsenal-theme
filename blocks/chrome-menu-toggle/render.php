<?php
/**
 * OSA · Menu Toggle — the mobile hamburger. Reproduces the button the old
 * header pattern emitted; mobile-nav.js wires it to the .osa-main-nav drawer
 * via the [data-osa-nav-toggle] hook and the `osa-nav-open` <html> class.
 */
defined( 'ABSPATH' ) || exit;
?>
<button type="button" class="osa-header-hamburger" aria-label="<?php esc_attr_e( 'Menu', 'osa-arsenal' ); ?>" aria-controls="osa-mobile-nav" aria-expanded="false" data-osa-nav-toggle>
	<svg class="osa-header-hamburger__icon osa-header-hamburger__icon--open" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
		<line x1="4" y1="7"  x2="20" y2="7"/>
		<line x1="4" y1="12" x2="20" y2="12"/>
		<line x1="4" y1="17" x2="20" y2="17"/>
	</svg>
	<svg class="osa-header-hamburger__icon osa-header-hamburger__icon--close" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
		<line x1="6" y1="6" x2="18" y2="18"/>
		<line x1="18" y1="6" x2="6" y2="18"/>
	</svg>
</button>
<?php
