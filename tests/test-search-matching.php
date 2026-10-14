<?php
/**
 * Word-aware search matching + ranking (inc/search.php):
 *  1. short terms match at a word start ("ak" ≠ Hakim/SAKO/Brake/Cerakote);
 *  2. letters+digits terms ignore separators (ak47 = ak-47 = "ak 47"), and a
 *     trailing number can't run on (m1 ≠ M1903); ak47 is a synonym for any AK;
 *  3. posts_search rewrites only queries that need it; posts_search_orderby
 *     ranks in stock → title match → gun category, and leaves an explicit ?orderby.
 * The patterns are run here with preg (same ERE semantics as MySQL/MariaDB
 * REGEXP for these constructs) — real-DB behavior is checked on cga-local.
 */
require __DIR__ . '/bootstrap.php';

function wc_get_product_visibility_term_ids() {
	return [ 'exclude-from-search' => 7, 'outofstock' => 9 ];
}
// CGA: flat top-level gun categories (no Firearms umbrella); no shotgun term yet.
function get_term_by( $field, $value, $taxonomy ) {
	$cats = [ 'pistol' => 63, 'revolver' => 65, 'rifle' => 67 ];
	return isset( $cats[ $value ] ) ? (object) [ 'term_id' => $cats[ $value ], 'term_taxonomy_id' => $cats[ $value ] ] : false;
}
function get_terms( $args ) {
	return 67 === ( $args['child_of'] ?? 0 ) ? [ '77', '78' ] : [];
}

class Fake_WPDB {
	public $posts              = 'wp_posts';
	public $term_relationships = 'wp_term_relationships';
	public function esc_like( $text ) {
		return addcslashes( $text, '_%\\' );
	}
	public function prepare( $query, ...$args ) {
		return preg_replace_callback( '/%[sd]/', static function ( $m ) use ( &$args ) {
			$arg = array_shift( $args );
			return '%d' === $m[0] ? (string) (int) $arg : "'" . addslashes( $arg ) . "'";
		}, $query );
	}
}
$GLOBALS['wpdb'] = new Fake_WPDB();

require OSA_THEME_DIR . '/inc/search.php';

function osa_matches( $term, $text ) {
	$p = osa_search_term_pattern( $term );
	osa_assert( null !== $p, "expected a word-aware pattern for '{$term}'" );
	return 1 === preg_match( '/' . $p[1] . '/i', $text );
}

// 1. Short terms: word start only.
foreach ( [ 'Romanian PM md. 63 AK 7.62×39 Matching Kit', 'Romy G md.63 AKM Build', 'Magpul MOE AK Grip – AK-47/AK-74', '<p>AK pattern rifle' ] as $t ) {
	osa_assert( osa_matches( 'ak', $t ), "'ak' should match: {$t}" );
}
foreach ( [ '1957 Egyptian Hakim 8mm', 'Finnish SAKO M39', 'SureFire SOCOM SFMB Muzzle Brake', 'TRIARC Cerakote', 'Type 38 Arisaka', 'HS515GM Shake Awake', '1927 Czechoslovak CZ vz. 24' ] as $t ) {
	osa_assert( ! osa_matches( 'ak', $t ), "'ak' should NOT match: {$t}" );
}
osa_assert( osa_matches( '22', 'Ruger Standard .22 LR' ) && ! osa_matches( '22', '1922 Mauser' ), "'22' should match .22 but not 1922" );

// 2. Letters+digits: separators optional, trailing number bounded, ak47 synonym.
osa_assert( osa_search_term_pattern( 'ak47' ) === osa_search_term_pattern( 'ak' ), 'ak47 should be a synonym for ak' );
osa_assert( osa_search_term_pattern( 'AK-47' ) === osa_search_term_pattern( 'ak' ), 'AK-47 should normalize like ak47' );
foreach ( [ 'AK-74', 'AK74', 'AK 74', 'AK.74 rifle' ] as $t ) {
	osa_assert( osa_matches( 'ak74', $t ), "'ak74' should match: {$t}" );
}
foreach ( [ 'H&R M1 Garand', 'Springfield M1A Loaded', 'M1-Carbine' ] as $t ) {
	osa_assert( osa_matches( 'm1', $t ), "'m1' should match: {$t}" );
}
foreach ( [ 'Springfield M1903A3', 'Colt M1911' ] as $t ) {
	osa_assert( ! osa_matches( 'm1', $t ), "'m1' should NOT match: {$t}" );
}
osa_assert( osa_matches( 'ar15', 'Custom/BCM .300 Blackout AR-15' ), "'ar15' should match AR-15" );
osa_assert( osa_matches( 'kar98k', 'Mauser Kar 98k 8mm' ), "'kar98k' should match Kar 98k" );
foreach ( [ 'mosin', 'mosin-nagant', '.45', '10/22', '1911', '7.62x39' ] as $t ) {
	osa_assert( null === osa_search_term_pattern( $t ), "'{$t}' should stay a core substring LIKE" );
}
osa_assert( [ 'ak47' ] === osa_search_normalize_terms( [ 'ak', '47' ] ), '"ak 47" should rejoin' );
osa_assert( [ 'sig', '365' ] === osa_search_normalize_terms( [ 'sig', '365' ] ), '"sig 365" must stay two terms (SIG Sauer P365)' );
osa_assert( [ 'colt', '1911' ] === osa_search_normalize_terms( [ 'colt', '1911' ] ), 'long word + number must not join' );
osa_assert( [ '-ak', '47' ] === osa_search_normalize_terms( [ '-ak', '47' ] ), 'exclusion term must not join' );

// 3. SQL filters.
class Fake_Search_Query {
	public $vars = [];
	public $main = true;
	public function __construct( $terms ) {
		$this->vars['search_terms'] = $terms;
	}
	public function is_main_query() {
		return $this->main;
	}
	public function is_search() {
		return true;
	}
	public function get( $key ) {
		return $this->vars[ $key ] ?? '';
	}
}

$core = ' AND (core) ';
osa_assert( $core === osa_search_where( $core, new Fake_Search_Query( [ 'mosin' ] ) ), 'plain long-word search must keep core SQL' );
$not_main       = new Fake_Search_Query( [ 'ak' ] );
$not_main->main = false;
osa_assert( $core === osa_search_where( $core, $not_main ), 'secondary queries must keep core SQL' );

$where = osa_search_where( $core, new Fake_Search_Query( [ 'ak', '-sks' ] ) );
osa_assert( 3 === substr_count( $where, "REGEXP '(^|[^a-z0-9])ak'" ), 'ak should REGEXP title, excerpt and content' );
osa_assert( false !== strpos( $where, "wp_posts.post_content LIKE '%ak%' AND wp_posts.post_content REGEXP" ), 'REGEXP should sit behind a LIKE prefilter' );
osa_assert( 3 === substr_count( $where, "NOT (wp_posts." ) && false !== strpos( $where, "REGEXP '(^|[^a-z0-9])sks'" ), 'short exclusion term should use the same word-start match, negated' );
osa_assert( false !== strpos( $where, "post_password = ''" ), 'logged-out search must keep the password guard' );

osa_assert( '' === osa_search_orderby( '', new Fake_Search_Query( [ 'ak' ] ) ), 'explicit ?orderby (core passes empty) must be left alone' );
$order = osa_search_orderby( 'core_order DESC', new Fake_Search_Query( [ 'ak' ] ) );
$sold  = strpos( $order, 'osa_tr.term_taxonomy_id = 9) ASC' );
$title = strpos( $order, "wp_posts.post_title REGEXP '(^|[^a-z0-9])ak')) DESC" );
$fire  = strpos( $order, 'osa_fr.term_taxonomy_id IN (63,65,67,77,78)) DESC' );
osa_assert( false !== $sold && false !== $title && false !== $fire, "ranking keys missing: {$order}" );
osa_assert( $sold < $title && $title < $fire, 'rank order must be in stock → title → firearm' );
osa_assert( ', core_order DESC' === substr( $order, -strlen( ', core_order DESC' ) ), "core's order should stay as tiebreak" );

osa_pass( 'search: word-aware terms, synonyms, rewrite scope and ranking' );
