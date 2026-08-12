<?php
/**
 * OSA — search modal + product-inclusive results.
 *
 *   - Renders a full-screen overlay in wp_footer; opened by anything with
 *     [data-osa-search] (the header button).
 *   - Posts to /?s=… via standard WP search; results template is
 *     templates/search.html which restyles the WP query.
 *   - Search query includes products + pages + posts (WP defaults to
 *     post+page only).
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Make WP search query products (and posts/pages) by default.
 */
add_filter( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) {
		return $q;
	}
	// Respect explicit type searches (e.g. WooCommerce's ?post_type=product,
	// which applies its own catalog-visibility handling).
	if ( $q->get( 'post_type' ) ) {
		return $q;
	}
	$q->set( 'post_type', [ 'product', 'post', 'page' ] );

	// Core search knows nothing about WooCommerce catalog visibility, so keep
	// hidden (and, per shop settings, out-of-stock) products out of results —
	// mirrors catalog-grid's exclude-from-catalog handling. NOT IN leaves
	// posts/pages (which never carry these terms) untouched.
	if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$term_ids = wc_get_product_visibility_term_ids();
		$exclude  = [ (int) ( $term_ids['exclude-from-search'] ?? 0 ) ];
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$exclude[] = (int) ( $term_ids['outofstock'] ?? 0 );
		}
		$exclude = array_values( array_filter( $exclude ) );
		if ( $exclude ) {
			$existing    = $q->get( 'tax_query' );
			$tax_query   = is_array( $existing ) ? $existing : [];
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $exclude,
				'operator' => 'NOT IN',
			];
			$q->set( 'tax_query', $tax_query );
		}
	}
	return $q;
} );

/**
 * Render the search overlay in the page footer (so it's available on every
 * page). Hidden by default; opened via [data-osa-search] click.
 */
add_action( 'wp_footer', 'osa_render_search_overlay', 5 );
function osa_render_search_overlay() {
	if ( is_admin() ) {
		return;
	}
	?>
	<div id="osa-search-overlay" class="osa-search-overlay" role="dialog" aria-modal="true" aria-hidden="true" hidden>
		<form class="osa-search-overlay__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<p class="osa-mono-caps osa-search-overlay__eyebrow">Search the catalog</p>
			<input
				type="search"
				name="s"
				class="osa-search-overlay__input"
				placeholder="Lot number · maker · caliber…"
				autocomplete="off"
				aria-label="<?php esc_attr_e( 'Search OSA', 'osa-arsenal' ); ?>"
			/>
			<div class="osa-search-overlay__hints">
				<span class="osa-mono">Try: <a href="<?php echo esc_url( home_url( '/?s=colt' ) ); ?>">Colt</a> · <a href="<?php echo esc_url( home_url( '/?s=winchester' ) ); ?>">Winchester</a> · <a href="<?php echo esc_url( home_url( '/?s=.45' ) ); ?>">.45 LC</a></span>
				<span class="osa-mono osa-search-overlay__close-hint">Press <kbd>Esc</kbd> to close</span>
			</div>
		</form>
		<button type="button" class="osa-search-overlay__close" data-osa-search-close aria-label="<?php esc_attr_e( 'Close search', 'osa-arsenal' ); ?>">×</button>
	</div>
	<?php
}

/**
 * Enqueue the small JS that wires open/close.
 */
add_action( 'wp_enqueue_scripts', function () {
	$path = OSA_THEME_DIR . '/assets/js/search.js';
	wp_enqueue_script(
		'osa-search',
		OSA_THEME_URI . '/assets/js/search.js',
		[],
		file_exists( $path ) ? (string) filemtime( $path ) : OSA_THEME_VERSION,
		true
	);
}, 25 );
