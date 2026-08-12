<?php
/**
 * OSA — register all custom blocks declared under /blocks/.
 *
 * Each block folder MUST contain block.json that declares
 * "render": "file:./render.php" so we get server-side rendering for free.
 * The editor preview is provided by ./view.js (ServerSideRender wrapper).
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$blocks_dir = OSA_THEME_DIR . '/blocks';
	if ( ! is_dir( $blocks_dir ) ) {
		return;
	}
	foreach ( scandir( $blocks_dir ) as $slug ) {
		if ( '' === $slug || '.' === $slug[0] ) {
			continue;
		}
		$path = $blocks_dir . '/' . $slug;
		if ( is_dir( $path ) && file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}
}, 15 );

/**
 * Register the shared editor script that provides edit() components for all
 * OSA dynamic blocks via @wordpress/server-side-render.
 */
add_action( 'enqueue_block_editor_assets', function () {
	$rel  = '/assets/js/block-editor.js';
	$path = OSA_THEME_DIR . $rel;
	wp_enqueue_script(
		'osa-block-editor',
		OSA_THEME_URI . $rel,
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ],
		file_exists( $path ) ? (string) filemtime( $path ) : OSA_THEME_VERSION,
		true
	);

	// Tell block-editor.js which osa/* blocks have a server-side registration
	// on this site. Dealer blocks are registered by the osa-dealer plugin —
	// when it's inactive, the script skips their edit() registration instead
	// of offering inserter entries whose ServerSideRender 404s.
	$osa_blocks = array_values( array_filter(
		array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() ),
		static function ( $name ) {
			return 0 === strpos( $name, 'osa/' );
		}
	) );
	wp_localize_script( 'osa-block-editor', 'osaEditorBlocks', [ 'serverRegistered' => $osa_blocks ] );

	// Editor canvas styles come from add_editor_style( 'assets/css/osa-base.css' )
	// in functions.php (which also reaches the iframed canvas) — no separate
	// enqueue here.
} );

/**
 * Disable the WordPress Site Editor's "default" block scope for our dynamic
 * blocks so users can't insert them where they don't make sense. (Light
 * touch — none for now; let the user place them anywhere.)
 */
