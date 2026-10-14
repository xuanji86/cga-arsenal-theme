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
 *   - Word-aware matching + in-stock/title/firearm ranking (see below).
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

/*
 * Search matching + ranking for the main front-end search (header ?s= and
 * WooCommerce ?post_type=product alike).
 *
 * Core search is a bare substring LIKE, so "ak" also hits Hakim, SAKO, Brake,
 * Cerakote, Arisaka… and buries the real AKs pages deep. Here:
 *   - a short term (≤3 letters/digits) must start a word: "ak" finds AK, AKM,
 *     AK-74 but not Hakim;
 *   - a letters+digits term ignores separators: ak47 = ak-47 = AK.47,
 *     ar15 = AR-15, and a trailing number can't run on (m1 ≠ M1903);
 *   - OSA_SEARCH_SYNONYMS maps colloquial names (ak47 → any AK);
 *   - relevance order: in stock → all terms in title → a gun category
 *     (OSA_SEARCH_FIREARM_CATS — CGA has no Firearms umbrella; guns sit in
 *     flat top-level Pistol/Revolver/Rifle/Shotgun, which POS creates by name),
 *     then core's own order. An explicit ?orderby (price, newest…) is untouched.
 * Patterns are POSIX ERE so they run on both MySQL 8 and MariaDB REGEXP; the
 * columns' _ci collation makes them case-insensitive.
 */
const OSA_SEARCH_SYNONYMS     = [ 'ak47' => 'ak' ];
const OSA_SEARCH_FIREARM_CATS = [ 'pistol', 'revolver', 'rifle', 'shotgun' ];

/**
 * [ LIKE prefilter, REGEXP ] for a term that needs word-aware matching, or
 * null to keep core's substring LIKE.
 */
function osa_search_term_pattern( $term ) {
	$term = strtolower( (string) $term );
	if ( ! preg_match( '/^[a-z0-9]+(-[a-z0-9]+)*$/', $term ) ) {
		return null;
	}
	$core = str_replace( '-', '', $term );
	$core = OSA_SEARCH_SYNONYMS[ $core ] ?? $core;
	preg_match_all( '/[a-z]+|[0-9]+/', $core, $m );
	$runs = $m[0];
	if ( count( $runs ) > 1 ) {
		$tail = ctype_digit( end( $runs ) ) ? '([^0-9]|$)' : '';
		return [ $runs[0], '(^|[^a-z0-9])' . implode( '[-. ]?', $runs ) . $tail ];
	}
	return strlen( $core ) <= 3 ? [ $core, '(^|[^a-z0-9])' . $core ] : null;
}

/**
 * Core splits "ak 47" on the space; rejoin a pair only when the joined form is
 * a synonym — a general join would demand adjacency ("sig 365" ≠ SIG P365).
 */
function osa_search_normalize_terms( array $terms ) {
	$out = [];
	foreach ( $terms as $term ) {
		$prev = end( $out );
		if ( false !== $prev && isset( OSA_SEARCH_SYNONYMS[ strtolower( $prev . $term ) ] ) ) {
			$out[ key( $out ) ] = $prev . $term;
			continue;
		}
		$out[] = $term;
	}
	return $out;
}

/** Normalized terms of a query this file ranks, or [] to leave it to core. */
function osa_search_terms( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() || $q->get( 'sentence' ) || $q->get( 'exact' ) ) {
		return [];
	}
	return osa_search_normalize_terms( (array) $q->get( 'search_terms' ) );
}

function osa_search_column_sql( $column, $term ) {
	global $wpdb;
	$col     = "{$wpdb->posts}.{$column}";
	$pattern = osa_search_term_pattern( $term );
	if ( ! $pattern ) {
		return $wpdb->prepare( "({$col} LIKE %s)", '%' . $wpdb->esc_like( $term ) . '%' );
	}
	return $wpdb->prepare( "({$col} LIKE %s AND {$col} REGEXP %s)", '%' . $wpdb->esc_like( $pattern[0] ) . '%', $pattern[1] );
}

/** WHERE: rebuilt only when some term needs word-aware matching. */
add_filter( 'posts_search', 'osa_search_where', 10, 2 );
function osa_search_where( $search, $q ) {
	global $wpdb;
	$terms = osa_search_terms( $q );
	if ( ! array_filter( array_map( 'osa_search_term_pattern', $terms ) ) ) {
		return $search;
	}
	$columns = array_intersect( (array) ( $q->get( 'search_columns' ) ?: [] ), [ 'post_title', 'post_excerpt', 'post_content' ] )
		?: [ 'post_title', 'post_excerpt', 'post_content' ];
	$clauses = [];
	foreach ( $terms as $term ) {
		$parts = [];
		if ( '-' === $term[0] ) { // Exclusion: same matching, negated.
			foreach ( $columns as $column ) {
				$parts[] = 'NOT ' . osa_search_column_sql( $column, substr( $term, 1 ) );
			}
			$clauses[] = '(' . implode( ' AND ', $parts ) . ')';
			continue;
		}
		foreach ( $columns as $column ) {
			$parts[] = osa_search_column_sql( $column, $term );
		}
		$clauses[] = '(' . implode( ' OR ', $parts ) . ')';
	}
	$sql = ' AND (' . implode( ' AND ', $clauses ) . ') ';
	if ( ! is_user_logged_in() ) {
		$sql .= " AND ({$wpdb->posts}.post_password = '') ";
	}
	return $sql;
}

/** term_taxonomy_ids of the gun categories and everything under them. */
function osa_search_firearm_tt_ids() {
	$ids = [];
	foreach ( OSA_SEARCH_FIREARM_CATS as $slug ) {
		$root = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $root ) {
			continue;
		}
		$children = get_terms( [
			'taxonomy'   => 'product_cat',
			'child_of'   => $root->term_id,
			'fields'     => 'tt_ids',
			'hide_empty' => false,
		] );
		$ids = array_merge( $ids, [ $root->term_taxonomy_id ], is_array( $children ) ? $children : [] );
	}
	return array_map( 'intval', $ids );
}

/** ORDER BY: core passes '' when an explicit ?orderby wins — leave that alone. */
add_filter( 'posts_search_orderby', 'osa_search_orderby', 10, 2 );
function osa_search_orderby( $orderby, $q ) {
	global $wpdb;
	$terms = array_filter( osa_search_terms( $q ), static function ( $t ) {
		return '-' !== $t[0];
	} );
	if ( '' === $orderby || ! $terms ) {
		return $orderby;
	}
	$id   = "{$wpdb->posts}.ID";
	$keys = [];
	$vis  = function_exists( 'wc_get_product_visibility_term_ids' ) ? wc_get_product_visibility_term_ids() : [];
	if ( ! empty( $vis['outofstock'] ) ) {
		$keys[] = $wpdb->prepare( "EXISTS (SELECT 1 FROM {$wpdb->term_relationships} osa_tr WHERE osa_tr.object_id = {$id} AND osa_tr.term_taxonomy_id = %d) ASC", $vis['outofstock'] );
	}
	$title = [];
	foreach ( $terms as $term ) {
		$title[] = osa_search_column_sql( 'post_title', $term );
	}
	$keys[] = '(' . implode( ' AND ', $title ) . ') DESC';
	$firearm = osa_search_firearm_tt_ids();
	if ( $firearm ) {
		$keys[] = "EXISTS (SELECT 1 FROM {$wpdb->term_relationships} osa_fr WHERE osa_fr.object_id = {$id} AND osa_fr.term_taxonomy_id IN (" . implode( ',', $firearm ) . ')) DESC';
	}
	return implode( ', ', $keys ) . ', ' . $orderby;
}

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
