<?php
/**
 * Test double for the ffl-core API inc/map-price.php wraps:
 * FflCore\Catalog\MapPrice (ffl-core >= 0.11.0,
 * plugin/ffl-core/includes/catalog/class-map-price.php).
 *
 * Own file for the same reason as ffl-core-product-meta-stub.php: a namespaced
 * class has to open its file, and requiring it MID-test lets a test run first
 * with the class ABSENT (ffl-core missing or older), then present.
 *
 * state() is staged per product object: $GLOBALS['osa_test_map'][ spl_object_id( $p ) ]
 * = [ 'mode' => display mode, 'low' => advertised MAP in cents, 'high' => optional ].
 * Unstaged = not hidden, which is what a product with no _osa_map_price gets.
 * hides() / to_cents() / normalize_mode() are ffl-core's pure functions, copied.
 * Calls to state() and label() are counted in $GLOBALS['osa_test_map_calls'].
 *
 * @package OSA_Arsenal
 */

namespace FflCore\Catalog;

final class MapPrice {
	public const MODE_FLOOR  = 'Floor';
	public const MODE_CART   = 'Show in cart';
	public const MODE_EMAIL  = 'Email for price';
	public const MODE_CALL   = 'Call for price';
	public const MODE_IGNORE = 'Ignore';
	public const MODES       = array( self::MODE_FLOOR, self::MODE_CART, self::MODE_EMAIL, self::MODE_CALL, self::MODE_IGNORE );

	public static function state( $product ): array {
		$GLOBALS['osa_test_map_calls']['state'] = ( $GLOBALS['osa_test_map_calls']['state'] ?? 0 ) + 1;
		$staged = is_object( $product ) ? ( $GLOBALS['osa_test_map'][ spl_object_id( $product ) ] ?? null ) : null;
		if ( null === $staged ) {
			return [ 'hidden' => false, 'mode' => '', 'low' => null, 'high' => null ];
		}
		return [ 'hidden' => true, 'mode' => $staged['mode'], 'low' => $staged['low'], 'high' => $staged['high'] ?? $staged['low'] ];
	}

	public static function label( string $mode ): string {
		$GLOBALS['osa_test_map_calls']['label'] = ( $GLOBALS['osa_test_map_calls']['label'] ?? 0 ) + 1;
		return self::MODE_CART === $mode ? 'Add to cart to see price' : $mode;
	}

	public static function hides( int $map_cents, string $mode, ?int $price_cents ): bool {
		return self::MODE_IGNORE !== $mode && $map_cents > 0 && null !== $price_cents && $price_cents < $map_cents;
	}

	public static function to_cents( $value ): int {
		$value = is_string( $value ) ? trim( $value ) : $value;
		if ( ! is_numeric( $value ) ) {
			return 0;
		}
		return max( 0, (int) round( (float) $value * 100 ) );
	}

	public static function normalize_mode( $mode ): string {
		$mode = is_string( $mode ) ? strtolower( trim( $mode ) ) : '';
		foreach ( self::MODES as $known ) {
			if ( strtolower( $known ) === $mode ) {
				return $known;
			}
		}
		return self::MODE_FLOOR;
	}
}
