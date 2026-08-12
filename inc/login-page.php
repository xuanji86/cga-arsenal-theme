<?php
/**
 * OSA — retail customer login + register page.
 *
 *  Shortcode  [osa_login_form]  renders the split-brand Sign In + Open an
 *  Account UI (Direction C · Serial Plate — see run dir
 *  2026-07-08-login-redesign/design-spec.md, the authoritative spec for the
 *  `osa-auth-*` markup below). Handlers post to admin-post.php and use
 *  wp_signon / wp_create_user under the hood (full WP auth, no custom session).
 *
 *  - Login accepts either email or username.
 *  - Registration creates a customer-role account, fills first/last name,
 *    email, password, and auto-signs in.
 *  - Errors round-trip as `osa_err` query string so the form re-renders with
 *    the user's previous input intact. Which section (Sign In vs Open an
 *    Account) shows the banner is inferred from which fields round-tripped —
 *    see osa_login_error_target().
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

const OSA_LOGIN_PATH = '/account/login/';

/**
 * Never full-page cache the login page. It renders per-user (signed-in vs
 * guest) and embeds CSRF nonces that expire in 24h, while LSCache's default
 * TTL here is 7 days — a cached page serves a long-stale nonce and every
 * sign-in dies with "The link you followed has expired."
 *
 * Must run on template_redirect, not inside the [osa_login_form] shortcode:
 * the shortcode lives in an FSE block template and renders too late, after
 * LiteSpeed has already computed the page's cache tag. No-op without LiteSpeed.
 */
add_action( 'template_redirect', function () {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?: '/';
	if ( OSA_LOGIN_PATH === rtrim( $path, '/' ) . '/' ) {
		do_action( 'litespeed_control_set_nocache', 'OSA login/register: per-request nonce + per-user content' );
	}
} );

/**
 * Pure decision: should an unauthenticated visitor to the current
 * WooCommerce My Account request be redirected to the OSA login page
 * instead of seeing WooCommerce's bare native form-login? Task #16 —
 * one login door for the whole site.
 *
 * Takes the three already-resolved facts as parameters rather than calling
 * is_account_page() / is_user_logged_in() / is_wc_endpoint_url() itself, so
 * this is directly unit-testable with the harness's WP stubs — see
 * tests/test-myaccount-redirect.php for the full truth table.
 *
 * $is_lost_password must cover the lost-password endpoint in ALL its
 * query-string forms — the bare request form, the raw email-link
 * `?key=...&login=...`/`?id=...` variant, and WooCommerce's own
 * post-redirect `?show-reset-form=true` variant — because all three share
 * the SAME endpoint path and only differ in query string (verified against
 * the real WooCommerce source: wc_lostpassword_url() in
 * wc-account-functions.php always builds from the my-account page +
 * lost-password endpoint slug regardless of query args, and
 * WC_Form_Handler::redirect_reset_password_link() — itself hooked to
 * template_redirect, same as this — is what turns the raw key/login/id
 * link into the show-reset-form one). Passing
 * `is_wc_endpoint_url( 'lost-password' )` at the call site therefore covers
 * all three uniformly; this function doesn't need to know the difference.
 * The new login page's own "Forgot password?" link depends on this staying
 * reachable.
 *
 * @param bool $is_account_page  is_account_page().
 * @param bool $is_logged_in     is_user_logged_in().
 * @param bool $is_lost_password is_wc_endpoint_url( 'lost-password' ).
 * @return bool
 */
function osa_myaccount_should_redirect_to_login( $is_account_page, $is_logged_in, $is_lost_password ) {
	return $is_account_page && ! $is_logged_in && ! $is_lost_password;
}

/**
 * Defense-in-depth guard on the value THIS hook embeds into the login
 * page's `redirect_to` query param — on top of, not instead of,
 * wp_safe_redirect()'s own allowed-hosts check at the LATER point where
 * osa_handle_login()/osa_handle_register() (untouched, out of scope for
 * task #16) actually redirect there after a successful sign-in.
 *
 * Requires an absolute URL whose HOST matches home_url()'s host — same
 * host-only strategy as WP core's own wp_validate_redirect() (deliberate
 * parity, not an oversight: scheme is not part of the check, so a same-host
 * URL that downgrades http(s) still passes — see the "downgraded scheme"
 * case in tests/test-myaccount-redirect.php pinning this down explicitly).
 * Rejects protocol-relative (//host/...), a different host outright, and a
 * host that merely contains/suffixes the real one (e.g.
 * "example.test.evil.example"). Bare relative paths are also rejected: this
 * function only accepts the same absolute-URL shape
 * osa_login_redirect_target() already produces/expects elsewhere in this
 * file (wc_get_page_permalink() and $_POST/$_GET['redirect_to'] round-trips
 * are always full URLs, never bare paths).
 *
 * @param string $url
 * @return bool
 */
function osa_myaccount_is_safe_redirect_target( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return false;
	}
	$target = wp_parse_url( $url );
	$home   = wp_parse_url( home_url() );
	if ( ! is_array( $target ) || empty( $target['host'] ) || empty( $home['host'] ) ) {
		return false;
	}
	return strtolower( $target['host'] ) === strtolower( $home['host'] );
}

/**
 * Task #16: unauthenticated visitors to any WooCommerce My Account
 * page/endpoint get sent to the OSA login page instead of the native
 * form-login shortcode — one login door for the whole site. Lost-password
 * stays reachable directly (osa_myaccount_should_redirect_to_login()'s
 * docblock has the full argument). Logged-in visitors are completely
 * unaffected. /account/login/ itself is a plain page (not the WooCommerce
 * myaccount page), so is_account_page() is never true there — no redirect
 * loop; verified explicitly in browser self-check too, not just by this
 * reasoning.
 *
 * No-ops entirely if WooCommerce isn't active (is_account_page() undefined)
 * — matches this file's existing function_exists( 'wc_get_page_permalink' )
 * guard style in osa_login_redirect_target() below.
 */
add_action( 'template_redirect', function () {
	if ( ! function_exists( 'is_account_page' ) ) {
		return;
	}

	$should_redirect = osa_myaccount_should_redirect_to_login(
		is_account_page(),
		is_user_logged_in(),
		is_wc_endpoint_url( 'lost-password' )
	);
	if ( ! $should_redirect ) {
		return;
	}

	$current_url = home_url( $_SERVER['REQUEST_URI'] ?? '/' );
	if ( ! osa_myaccount_is_safe_redirect_target( $current_url ) ) {
		// Structurally shouldn't be reachable (home_url() always returns
		// this site's own host) — paranoid fallback only.
		$current_url = function_exists( 'wc_get_page_permalink' )
			? wc_get_page_permalink( 'myaccount' )
			: site_url( '/my-account/' );
	}

	// Mirrors the sibling template_redirect hook above (:36-41): don't rely
	// on LSCache's own defaults (account pages uncacheable, 3xx not cached,
	// login state in a separate vary bucket) all staying true forever — say
	// it explicitly, same as the login page itself does. No-op without
	// LiteSpeed. (G3-r2 P7-M2.)
	do_action( 'litespeed_control_set_nocache', 'OSA my-account redirect: per-user auth state' );

	wp_safe_redirect(
		add_query_arg( 'redirect_to', rawurlencode( $current_url ), site_url( OSA_LOGIN_PATH ) ),
		302
	);
	exit;
} );

/**
 * Resolve the redirect target after auth — prefer ?redirect_to=, else WC my-account.
 */
function osa_login_redirect_target() {
	if ( ! empty( $_POST['redirect_to'] ) ) {
		return esc_url_raw( wp_unslash( $_POST['redirect_to'] ) );
	}
	if ( ! empty( $_GET['redirect_to'] ) ) {
		return esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
	}
	return function_exists( 'wc_get_page_permalink' )
		? wc_get_page_permalink( 'myaccount' )
		: site_url( '/my-account/' );
}

/**
 * Collapse source-formatting whitespace so the `core/shortcode` block's
 * unconditional `wpautop( $content )` pass (wp-includes/blocks/shortcode.php)
 * has nothing to convert to stray `<br>`/`<p>`. get_the_block_template_html()
 * expands shortcodes in the raw template source *before* do_blocks() parses
 * the block tree (wp-includes/block-template.php), so by the time the
 * `core/shortcode` block's render callback runs, `$content` is already this
 * function's entire multi-line return value — see notes-woo-dev.md §4 for
 * the full root-cause trace.
 *
 * Removes any run of whitespace that (a) contains a newline, or (b) is 2+
 * characters long even with no newline — i.e. purely source-formatting
 * indentation/line-breaks from the PHP template's human-readable layout —
 * wherever it appears: between two tags, between a tag and adjacent text, or
 * between text and a tag. Condition (b) exists because PHP's `?>` silently
 * eats exactly ONE newline immediately following it — this file's
 * `<?php if (...) : ?>` / `<?php endif; ?>` conditionals (around the error
 * banner) leave behind pure runs of leading tabs with the newline already
 * gone by the time this function ever sees the string, so "contains \n" isn't
 * a reliable signal on its own (verified empirically: an 8-tab, zero-newline
 * gap survived condition (a) alone and reached wpautop as a real
 * `>\t\t\t\t\t\t\t\t<` gap). Never touches a SINGLE whitespace character
 * with no newline (e.g. the space in "Dealers: <a>...") — every genuine
 * inline word-separator in this file's markup is exactly one space with
 * nothing else around it, so a 2+ threshold can't collide with real content.
 * Safe by construction otherwise too: nothing in this function ever puts an
 * actual line break inside a single piece of rendered text, and every
 * dynamic value ($err, $prev()'s fields) is already newline-free by the time
 * it gets here (sanitize_text_field() strips line breaks).
 *
 * Two DISTINCT wpautop mechanisms this has to defeat, not one (G4 finding,
 * PR #6 BUG-1 — an earlier version of this function only handled #1 and
 * still produced orphaned `<p>` tags in production):
 *
 * 1. "Any leftover newline becomes `<br />`" — defeated by removing every
 *    such newline, per above.
 * 2. wpautop SEPARATELY, unconditionally inserts \n\n immediately
 *    before/after every opening/closing tag in its own hardcoded block-tag
 *    list ($allblocks in wp-includes/formatting.php — div/form/p/h[1-6]/
 *    aside/section/... but NOT label/span/input/button/a/svg/picture/small),
 *    regardless of pre-existing whitespace, then only cleans up the `<p>` it
 *    wraps around each resulting chunk when BOTH of that chunk's own
 *    boundary tags happen to be on the recognized list. A chunk bounded by a
 *    recognized tag on one side and (say) a `<button>` on the other survives
 *    as an orphaned `<p>`/`</p>` — no amount of whitespace removal defeats
 *    this, since wpautop inserts these breaks itself. The actual fix for #2
 *    is the render markup below never using a wpautop-recognized block tag
 *    ($allblocks) anywhere INSIDE a <form> except <form> itself — see the
 *    deliberate tag choices right below (a plain <a> instead of
 *    <p><a>...</a></p>, <small> instead of <p> for the terms text, <span>
 *    instead of <div> for the row2 grid wrapper). Don't "clean up" those
 *    back to div/p — that's exactly what reintroduces the bug.
 *
 * Verified by capturing this function's real output for every state and
 * running each through the actual wp-includes/formatting.php wpautop() in a
 * container process (not a simulation) — see
 * tests/verify-wpautop-idempotent.sh. The portable check in
 * tests/test-login-page.php (assert_wpautop_immune) only proves mechanism
 * #1 doesn't fire; it cannot see mechanism #2 at all, since #2 is wpautop
 * inserting new tags, not failing to remove ones already there.
 */
function osa_flatten_html( $html ) {
	$html = preg_replace( '/[ \t\n]*\n[ \t\n]*|[ \t]{2,}/', '', (string) $html );
	return trim( $html );
}

/**
 * Which form's fields round-tripped decides which section shows the error
 * banner. The admin-post handlers can't be changed to pass an explicit "which
 * form failed" flag (out of scope — see spec.md non-goals), so this infers it
 * from osa_login_redirect_with()'s round-trip payload instead.
 *
 * MUST be OR, not AND: osa_login_redirect_with() drops empty-string fields
 * from the query string entirely (`if ('' !== (string) $v)`), so a single
 * required-field registration failure (e.g. first/last name blank) can
 * round-trip only ONE of first_name/last_name/email. Requiring all three
 * would misroute that case back to Sign In — the exact bug this task exists
 * to fix. See design-spec.md §4.2 for the full argument including the
 * both-blank Sign In edge case that makes the fallback branch correct.
 *
 * @param array $get Typically $_GET.
 * @return string 'register'|'login'
 */
function osa_login_error_target( array $get ) {
	$present = static function ( $key ) use ( $get ) {
		return isset( $get[ $key ] ) && '' !== $get[ $key ];
	};
	if ( $present( 'osa_first_name' ) || $present( 'osa_last_name' ) || $present( 'osa_email' ) ) {
		return 'register';
	}
	return 'login'; // Covers osa_user_login present, and the both-blank edge case.
}

/**
 * Render the split-brand login / register UI (design-spec.md, run dir
 * 2026-07-08-login-redesign, is the authoritative source for this markup).
 */
function osa_render_login_form() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		return sprintf(
			'<p class="osa-mono" style="text-align:center;font-size:13px">Signed in as <strong>%1$s</strong>. <a href="%2$s">Visit your account</a> · <a href="%3$s">Sign out</a></p>',
			esc_html( $user->display_name ?: $user->user_login ),
			esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : site_url( '/my-account/' ) ),
			esc_url( wp_logout_url( site_url( '/' ) ) )
		);
	}

	$err      = isset( $_GET['osa_err'] ) ? sanitize_text_field( wp_unslash( $_GET['osa_err'] ) ) : '';
	$redirect = osa_login_redirect_target();
	$target   = $err ? osa_login_error_target( $_GET ) : '';

	$prev = static function ( $key ) {
		$v = $_GET[ 'osa_' . $key ] ?? '';
		return esc_attr( sanitize_text_field( wp_unslash( $v ) ) );
	};

	// role="alert" + a neutral "!" glyph (no red/color semantics — the token
	// system is ink/steel/paper grayscale only, see design-spec.md §4.5).
	// Reuses the shared .osa-form-error definition as-is (osa-dealer's
	// application form depends on it); .osa-auth-error is purely additive.
	$error_banner = static function () use ( $err ) {
		return '<p class="osa-form-error osa-auth-error" role="alert"><span class="osa-auth-error__icon">!</span><span>' . esc_html( $err ) . '</span></p>';
	};

	ob_start();
	?>
	<div class="osa-auth-split">

		<aside class="osa-auth-brandpanel">
			<div class="osa-auth-brandpanel__crest" aria-hidden="true">
				<svg viewBox="0 0 400 400">
					<circle cx="200" cy="200" r="180" fill="none" stroke="currentColor" stroke-width="1"></circle>
					<circle cx="200" cy="200" r="128" fill="none" stroke="currentColor" stroke-width="1"></circle>
					<circle cx="200" cy="200" r="70" fill="none" stroke="currentColor" stroke-width="1"></circle>
					<line x1="200" y1="4" x2="200" y2="396" stroke="currentColor" stroke-width="1"></line>
					<line x1="4" y1="200" x2="396" y2="200" stroke="currentColor" stroke-width="1"></line>
				</svg>
			</div>

			<picture>
				<source media="(min-width:1280px)" srcset="<?php echo esc_url( osa_logo_url( 'stacked-light' ) ); ?>" />
				<source media="(min-width:768px)" srcset="<?php echo esc_url( osa_logo_url( 'inline-light' ) ); ?>" />
				<img class="osa-auth-brandpanel__logo" src="<?php echo esc_url( osa_logo_url( 'mark-light' ) ); ?>" alt="OSA — Old Steel Arsenal" />
			</picture>

			<div class="osa-auth-brandpanel__mid">
				<p class="osa-mono-caps osa-auth-brandpanel__eyebrow">Member Access</p>
				<p class="osa-auth-brandpanel__line"><span>Sourced.</span><span>Graded.</span><span>Transferred.</span></p>
			</div>

			<div class="osa-auth-brandpanel__foot">
				<p class="osa-auth-brandpanel__fact">Federally Licensed Dealer</p>
				<p class="osa-auth-brandpanel__fact">FFL 5-75-085-01-9B-42763</p>
				<p class="osa-auth-brandpanel__fact">Plano, Texas</p>
			</div>
		</aside>

		<div class="osa-auth-formcol">
			<p class="osa-mono-caps osa-auth-formcol__eyebrow">Account</p>
			<p class="osa-auth-formcol__lede">Track orders, watch lots, manage consignments, and keep your FFL on file.</p>

			<div class="osa-auth-formsection">
				<h2 class="osa-auth-subheading">Sign In</h2>
				<?php if ( 'login' === $target ) : ?>
					<?php echo $error_banner(); ?>
				<?php endif; ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="osa-form">
					<input type="hidden" name="action" value="osa_login" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>" />
					<?php wp_nonce_field( 'osa_login' ); ?>

					<label class="osa-auth-field">
						<span class="osa-mono-caps">Email or username</span>
						<input type="text" name="user_login" autocomplete="username" required value="<?php echo $prev( 'user_login' ); ?>" />
					</label>
					<label class="osa-auth-field">
						<span class="osa-mono-caps">Password</span>
						<span class="osa-auth-field__control">
							<input type="password" name="password" autocomplete="current-password" required />
							<button type="button" class="osa-auth-eye" aria-label="Show password" aria-pressed="false" onclick="var i=this.previousElementSibling;var wasHidden=i.type==='password';i.type=wasHidden?'text':'password';this.setAttribute('aria-pressed',wasHidden?'true':'false');this.setAttribute('aria-label',wasHidden?'Hide password':'Show password');">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12S5.5 5 12 5s10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
							</button>
						</span>
					</label>

					<label class="osa-auth-remember">
						<input type="checkbox" name="remember" value="1" />
						<span class="osa-mono">Remember this machine</span>
					</label>

					<button type="submit" class="osa-btn osa-auth-btn--block">Sign In →</button>

					<a class="osa-auth-foot osa-mono" href="<?php echo esc_url( wp_lostpassword_url( $redirect ) ); ?>">Forgot password?</a>
				</form>
			</div>

			<div class="osa-auth-divider"><span>New to OSA</span></div>

			<div class="osa-auth-formsection">
				<h2 class="osa-auth-subheading">Open an Account</h2>
				<?php if ( 'register' === $target ) : ?>
					<?php echo $error_banner(); ?>
				<?php endif; ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="osa-form">
					<input type="hidden" name="action" value="osa_register" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>" />
					<?php wp_nonce_field( 'osa_register' ); ?>

					<span class="osa-auth-row2">
						<label class="osa-auth-field">
							<span class="osa-mono-caps">First name</span>
							<input type="text" name="first_name" autocomplete="given-name" required value="<?php echo $prev( 'first_name' ); ?>" />
						</label>
						<label class="osa-auth-field">
							<span class="osa-mono-caps">Last name</span>
							<input type="text" name="last_name" autocomplete="family-name" required value="<?php echo $prev( 'last_name' ); ?>" />
						</label>
					</span>
					<label class="osa-auth-field">
						<span class="osa-mono-caps">Email</span>
						<input type="email" name="email" autocomplete="email" required value="<?php echo $prev( 'email' ); ?>" />
					</label>
					<label class="osa-auth-field">
						<span class="osa-mono-caps">Password (10+ characters)</span>
						<span class="osa-auth-field__control">
							<input type="password" name="password" autocomplete="new-password" minlength="10" required />
							<button type="button" class="osa-auth-eye" aria-label="Show password" aria-pressed="false" onclick="var i=this.previousElementSibling;var wasHidden=i.type==='password';i.type=wasHidden?'text':'password';this.setAttribute('aria-pressed',wasHidden?'true':'false');this.setAttribute('aria-label',wasHidden?'Hide password':'Show password');">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12S5.5 5 12 5s10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
							</button>
						</span>
					</label>

					<button type="submit" class="osa-btn osa-auth-btn--block">Create Account →</button>

					<small class="osa-auth-terms osa-mono">
						By signing up you confirm you are at least 18 (long guns) / 21 (handguns) and accept OSA's terms.
					</small>
				</form>
			</div>

			<p class="osa-auth-dealerline osa-mono">
				Dealers: <a href="<?php echo esc_url( site_url( '/dealer/login/' ) ); ?>">sign in to the dealer portal</a> · <a href="<?php echo esc_url( site_url( '/dealer/apply/' ) ); ?>">apply for a dealer account</a>.
			</p>
		</div>

	</div>
	<?php
	return osa_flatten_html( ob_get_clean() );
}
add_shortcode( 'osa_login_form', 'osa_render_login_form' );

/**
 * Sign-in handler.
 */
add_action( 'admin_post_nopriv_osa_login', 'osa_handle_login' );
add_action( 'admin_post_osa_login',         'osa_handle_login' );
function osa_handle_login() {
	check_admin_referer( 'osa_login' );

	$login    = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
	$password = (string) wp_unslash( $_POST['password'] ?? '' );
	$remember = ! empty( $_POST['remember'] );
	$redirect = osa_login_redirect_target();

	if ( '' === $login || '' === $password ) {
		osa_login_redirect_with( 'error', __( 'Email and password are required.', 'osa-arsenal' ), [ 'user_login' => $login ] );
	}

	// Accept email by translating to user_login.
	if ( is_email( $login ) ) {
		$u = get_user_by( 'email', $login );
		if ( $u ) {
			$login = $u->user_login;
		}
	}

	$result = wp_signon(
		[
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => $remember,
		],
		is_ssl()
	);

	if ( is_wp_error( $result ) ) {
		osa_login_redirect_with( 'error', $result->get_error_message(), [ 'user_login' => $login ] );
	}

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Registration handler.
 */
add_action( 'admin_post_nopriv_osa_register', 'osa_handle_register' );
add_action( 'admin_post_osa_register',         'osa_handle_register' );
function osa_handle_register() {
	check_admin_referer( 'osa_register' );

	$first    = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last     = sanitize_text_field( wp_unslash( $_POST['last_name']  ?? '' ) );
	$email    = sanitize_email(      wp_unslash( $_POST['email']      ?? '' ) );
	$password = (string) wp_unslash( $_POST['password'] ?? '' );
	$redirect = osa_login_redirect_target();

	$round_trip = [ 'first_name' => $first, 'last_name' => $last, 'email' => $email ];

	if ( '' === $first || '' === $last ) {
		osa_login_redirect_with( 'error', __( 'First and last name are required.', 'osa-arsenal' ), $round_trip );
	}
	if ( '' === $email || ! is_email( $email ) ) {
		osa_login_redirect_with( 'error', __( 'A valid email is required.', 'osa-arsenal' ), $round_trip );
	}
	if ( strlen( $password ) < 10 ) {
		osa_login_redirect_with( 'error', __( 'Password must be at least 10 characters.', 'osa-arsenal' ), $round_trip );
	}
	if ( email_exists( $email ) ) {
		osa_login_redirect_with( 'error', __( 'An account already exists for that email. Use Sign In, or reset your password.', 'osa-arsenal' ), $round_trip );
	}

	$base = sanitize_user( strstr( $email, '@', true ), true );
	if ( '' === $base ) $base = 'member';
	$username = $base;
	$i = 1;
	while ( username_exists( $username ) ) {
		$username = $base . $i++;
	}

	$uid = wp_create_user( $username, $password, $email );
	if ( is_wp_error( $uid ) ) {
		osa_login_redirect_with( 'error', $uid->get_error_message(), $round_trip );
	}

	wp_update_user( [
		'ID'           => $uid,
		'first_name'   => $first,
		'last_name'    => $last,
		'display_name' => trim( "$first $last" ) ?: $email,
		'role'         => 'customer',
	] );

	// Auto sign-in.
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true );

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Redirect back to login page with error/message + round-tripped field values.
 *
 * @param string $state    'error'|'msg'
 * @param string $text     Error/message to show.
 * @param array  $payload  Fields to round-trip in the URL.
 */
function osa_login_redirect_with( $state, $text, $payload ) {
	$args = [];
	if ( 'error' === $state ) {
		$args['osa_err'] = rawurlencode( $text );
	} else {
		$args['osa_msg'] = rawurlencode( $text );
	}
	foreach ( $payload as $k => $v ) {
		if ( '' !== (string) $v ) {
			$args[ 'osa_' . $k ] = rawurlencode( (string) $v );
		}
	}
	wp_safe_redirect( add_query_arg( $args, site_url( OSA_LOGIN_PATH ) ) );
	exit;
}

/**
 * Make sure the /account/login/ page exists and is wired to the page-login template.
 * Idempotent — safe to call on every theme load.
 */
add_action( 'after_switch_theme', 'osa_seed_login_page' );
function osa_seed_login_page() {
	// /login/ at top level might collide with other things — use /account/login/.
	$account = get_posts( [
		'post_type'      => 'page',
		'name'           => 'account',
		'post_parent'    => 0,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );
	$account_id = $account ? (int) $account[0] : 0;
	if ( ! $account_id ) {
		$account_id = wp_insert_post( [
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_name'   => 'account',
			'post_title'  => 'Account',
			'post_content'=> '<!-- wp:paragraph --><p>See the sign-in link below.</p><!-- /wp:paragraph -->',
		] );
	}

	$login = get_posts( [
		'post_type'      => 'page',
		'name'           => 'login',
		'post_parent'    => $account_id,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );
	if ( ! $login ) {
		$login_id = wp_insert_post( [
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_name'   => 'login',
			'post_title'  => 'Sign In · Open an Account',
			'post_parent' => $account_id,
			'post_content'=> '',
		] );
	} else {
		$login_id = (int) $login[0];
	}
	update_post_meta( $login_id, '_wp_page_template', 'page-login' );
}
