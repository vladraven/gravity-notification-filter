<?php

declare( strict_types=1 );

$plugin_root = dirname( __DIR__ );
$wp_load     = $plugin_root . '/../../wp-load.php';

if ( ! file_exists( $wp_load ) ) {
	fwrite(
		STDERR,
		"ERROR: Unable to locate WordPress wp-load.php.\n"
	);

	exit( 1 );
}

require_once $wp_load;

if ( ! defined( 'ABSPATH' ) ) {
	fwrite(
		STDERR,
		"ERROR: WordPress could not be loaded.\n"
	);

	exit( 1 );
}

$plugin_file =
	$plugin_root . '/gravity-notification-filter.php';

if ( ! file_exists( $plugin_file ) ) {
	fwrite(
		STDERR,
		"ERROR: Plugin bootstrap file not found.\n"
	);

	exit( 1 );
}

require_once $plugin_file;

$runner_file =
	$plugin_root . '/tests/class-gnf-test-runner.php';

if ( ! file_exists( $runner_file ) ) {
	fwrite(
		STDERR,
		"ERROR: Test runner not found.\n"
	);

	exit( 1 );
}

require_once $runner_file;

if ( ! class_exists( 'GNF_Test_Runner' ) ) {
	fwrite(
		STDERR,
		"ERROR: Test runner could not be loaded.\n"
	);

	exit( 1 );
}

try {
	$runner = new GNF_Test_Runner();

	$result = $runner->run();

	echo "\n";
	echo "========================================\n";
	echo "GRAVITY NOTIFICATION FILTER TESTS\n";
	echo "========================================\n\n";

	foreach ( $result['results'] as $test ) {
		$status =
			'pass' === $test['status']
				? 'PASS'
				: 'FAIL';

		echo sprintf(
			"[%s] %s\n",
			$status,
			$test['name']
		);

		if ( ! empty( $test['message'] ) ) {
			echo "      {$test['message']}\n";
		}
	}

	echo "\n";
	echo "========================================\n";
	echo "TOTAL:  {$result['total']}\n";
	echo "PASSED: {$result['passed']}\n";
	echo "FAILED: {$result['failed']}\n";
	echo "========================================\n\n";

	exit(
		$result['success']
			? 0
			: 1
	);

} catch ( Throwable $exception ) {
	fwrite(
		STDERR,
		"ERROR: {$exception->getMessage()}\n"
	);

	exit( 1 );
}