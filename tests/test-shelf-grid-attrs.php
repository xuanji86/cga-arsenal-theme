<?php
/**
 * Homepage shelf (patterns/cga-shelf.php) → osa/catalog-grid attributes from
 * CGA · Homepage → Categories & product grid: order (off/daily/hourly), how
 * many newest stay fixed, what to show (guns/all). Defaults: daily, 4, guns.
 * Always in-stock, 4×2, hero product excluded.
 */
require __DIR__ . '/bootstrap.php';
require OSA_THEME_DIR . '/inc/cga-customizer.php';

function cga_shelf_attrs_with( array $mods, $exclude = 42 ) {
	$GLOBALS['osa_theme_mods'] = $mods;
	return cga_shelf_grid_attrs( $exclude );
}

$guns = 'pistol,revolver,rifle,shotgun';

$a = cga_shelf_attrs_with( [] );
osa_assert( [ 'cols' => 4, 'limit' => 8, 'instock' => true, 'exclude' => [ 42 ], 'category' => $guns, 'rotation' => 'daily', 'pinned' => 4 ] === $a, 'defaults = daily, 4 pinned, guns only: ' . json_encode( $a ) );

$a = cga_shelf_attrs_with( [ 'cga_shelf_rotation' => 'off', 'cga_shelf_pinned' => 2 ] );
osa_assert( ! isset( $a['rotation'] ) && ! isset( $a['pinned'] ), 'off → newest first (no rotation keys)' );

$a = cga_shelf_attrs_with( [ 'cga_shelf_rotation' => 'hourly', 'cga_shelf_pinned' => '2' ] );
osa_assert( 'hourly' === $a['rotation'] && 2 === $a['pinned'], 'hourly + pinned 2' );
osa_assert( 8 === cga_shelf_attrs_with( [ 'cga_shelf_pinned' => 30 ] )['pinned'], 'pinned clamped to the 8-up grid' );
osa_assert( 0 === cga_shelf_attrs_with( [ 'cga_shelf_pinned' => -1 ] )['pinned'], 'pinned never negative' );
osa_assert( ! isset( cga_shelf_attrs_with( [ 'cga_shelf_rotation' => 'weekly' ] )['rotation'] ), 'unknown order value → newest first' );

$a = cga_shelf_attrs_with( [ 'cga_shelf_scope' => 'all' ] );
osa_assert( ! isset( $a['category'] ) && true === $a['instock'], 'all → every in-stock item' );

foreach ( [ 'off', 'daily', 'hourly' ] as $r ) {
	foreach ( [ 'guns', 'all' ] as $sc ) {
		$a = cga_shelf_attrs_with( [ 'cga_shelf_rotation' => $r, 'cga_shelf_scope' => $sc ], 7 );
		osa_assert( true === $a['instock'] && [ 7 ] === $a['exclude'], "{$r}/{$sc} keeps instock + hero exclude" );
	}
}

$shelf = file_get_contents( OSA_THEME_DIR . '/patterns/cga-shelf.php' );
osa_assert( false !== strpos( $shelf, 'wp_json_encode( cga_shelf_grid_attrs( $cga_shelf_exclude ) )' ), 'cga-shelf must render cga_shelf_grid_attrs() with the hero exclude' );

osa_pass( 'homepage shelf grid attrs follow the Customizer (order / pinned / scope)' );
