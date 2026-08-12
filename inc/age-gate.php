<?php
/**
 * OSA — first-visit age verification modal.
 *
 * Lightweight: renders an HTML modal in the page footer if the
 * `osa_age_ok` cookie isn't set. JS sets the cookie on confirm and removes
 * the modal. Skipped in admin and for users already logged in.
 *
 * Phase 5 will replace this with a proper plugin + persistent server-side
 * tracking. Phase 2 is the minimum viable nudge.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Single source of truth for "should the gate render right now?".
 * Used by both the head marker and the footer modal so they stay in sync.
 */
function osa_age_gate_active() {
	if ( is_admin() || is_user_logged_in() ) {
		return false;
	}
	// Customizer kill-switch (defaults to true so existing sites keep current behavior).
	return (bool) get_theme_mod( 'osa_age_gate_enabled', true );
}

/**
 * Early head script: if the visitor has already confirmed (cookie or
 * localStorage), mark <html> with `osa-age-ok` BEFORE the body renders, so
 * the CSS rule below hides the modal without a flash. Runs even when the
 * page is served from full-page cache.
 */
add_action( 'wp_head', 'osa_age_gate_head_marker', 1 );
function osa_age_gate_head_marker() {
	if ( ! osa_age_gate_active() ) {
		return;
	}
	?>
	<script>
	(function () {
		try {
			var ok = (document.cookie.indexOf('osa_age_ok=1') !== -1) ||
			         (window.localStorage && localStorage.getItem('osa_age_ok') === '1');
			if (ok) document.documentElement.classList.add('osa-age-ok');
		} catch (e) {}
	})();
	</script>
	<style>html.osa-age-ok .osa-age-gate-backdrop{display:none!important}</style>
	<?php
}

add_action( 'wp_footer', 'osa_render_age_gate' );
function osa_render_age_gate() {
	if ( ! osa_age_gate_active() ) {
		return;
	}

	$eyebrow   = (string) get_theme_mod( 'osa_age_gate_eyebrow',   OSA_AGE_GATE_EYEBROW_DEFAULT );
	$title     = (string) get_theme_mod( 'osa_age_gate_title',     OSA_AGE_GATE_TITLE_DEFAULT );
	$body      = (string) get_theme_mod( 'osa_age_gate_body',      OSA_AGE_GATE_BODY_DEFAULT );
	$enter     = (string) get_theme_mod( 'osa_age_gate_enter',     OSA_AGE_GATE_ENTER_DEFAULT );
	$leave     = (string) get_theme_mod( 'osa_age_gate_leave',     OSA_AGE_GATE_LEAVE_DEFAULT );
	$leave_url = (string) get_theme_mod( 'osa_age_gate_leave_url', OSA_AGE_GATE_LEAVE_URL_DEFAULT );
	// NOTE: we deliberately do NOT skip render based on $_COOKIE here.
	// On hosts with full-page cache (LiteSpeed/Hostinger), the PHP cookie
	// check would only run on cache misses, so cached responses would still
	// include the modal markup. Always rendering + JS-driven visibility
	// (head marker above) makes this cache-safe.
	?>
	<div id="osa-age-gate" class="osa-age-gate-backdrop" role="dialog" aria-modal="true" aria-labelledby="osa-age-gate-title" data-nosnippet>
		<div class="osa-age-gate-card">
			<p class="osa-mono-caps" style="font-size:10px;letter-spacing:.22em;color:var(--osa-paper-edge);margin:0 0 8px"><?php echo esc_html( $eyebrow ); ?></p>
			<h2 id="osa-age-gate-title"><?php echo esc_html( $title ); ?></h2>
			<p><?php echo nl2br( esc_html( $body ) ); ?></p>
			<div class="osa-age-gate-actions">
				<button type="button" class="osa-btn" id="osa-age-gate-enter"><?php echo esc_html( $enter ); ?></button>
				<a href="<?php echo esc_url( $leave_url ); ?>" class="osa-btn osa-btn--leave" rel="noopener"><?php echo esc_html( $leave ); ?></a>
			</div>
		</div>
	</div>
	<script>
		( function () {
			var btn  = document.getElementById( 'osa-age-gate-enter' );
			var bg   = document.getElementById( 'osa-age-gate' );
			if ( ! btn || ! bg ) return;
			btn.addEventListener( 'click', function () {
				try {
					var expires = new Date();
					expires.setTime( expires.getTime() + ( 30 * 24 * 60 * 60 * 1000 ) ); // 30 days
					var secure = ( location.protocol === 'https:' ) ? '; Secure' : '';
					document.cookie = 'osa_age_ok=1; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax' + secure;
				} catch ( e ) {}
				try { if ( window.localStorage ) localStorage.setItem( 'osa_age_ok', '1' ); } catch ( e ) {}
				document.documentElement.classList.add( 'osa-age-ok' );
				bg.style.display = 'none';
			} );
			// A11y: aria-modal promises real modal behavior — put focus on the
			// Enter button when the gate is actually showing, and keep Tab
			// cycling between the two controls while it's up.
			var leave = bg.querySelector( '.osa-btn--leave' );
			if ( ! document.documentElement.classList.contains( 'osa-age-ok' ) ) {
				try { btn.focus(); } catch ( e ) {}
			}
			bg.addEventListener( 'keydown', function ( ev ) {
				if ( ev.key !== 'Tab' || ! leave ) return;
				if ( ev.shiftKey && document.activeElement === btn ) {
					ev.preventDefault();
					leave.focus();
				} else if ( ! ev.shiftKey && document.activeElement === leave ) {
					ev.preventDefault();
					btn.focus();
				}
			} );
		} )();
	</script>
	<?php
}
