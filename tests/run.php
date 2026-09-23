<?php
/**
 * OSA harness test runner: executes each tests/test-*.php in its own PHP
 * process (isolation — tests define conflicting stubs like a present/absent
 * WooCommerce) and aggregates results.
 *
 * Usage: php tests/run.php
 */

$tests = glob( __DIR__ . '/test-*.php' );
sort( $tests );

$failed = 0;
foreach ( $tests as $test ) {
	$output = [];
	$code   = 0;
	exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $test ) . ' 2>&1', $output, $code );
	printf( "%s %s\n", 0 === $code ? '✓' : '✗', basename( $test ) );
	if ( 0 !== $code ) {
		$failed++;
		foreach ( $output as $line ) {
			echo "    {$line}\n";
		}
	}
}

printf( "%d/%d passed\n", count( $tests ) - $failed, count( $tests ) );
exit( $failed > 0 ? 1 : 0 );
