#!/usr/bin/env php
<?php

/**
 * Test Runner Script for POS Kasir Backend
 *
 * Usage:
 *   php run-tests.php                    # Run all tests
 *   php run-tests.php --coverage         # Run with coverage
 *   php run-tests.php --filter=Sale      # Run specific tests
 *   php run-tests.php --unit             # Run only unit tests
 *   php run-tests.php --feature          # Run only feature tests
 */

$options = getopt('', ['coverage', 'filter:', 'unit', 'feature', 'help']);

if (isset($options['help'])) {
    echo <<<HELP
🧪 POS Kasir Backend Test Runner

Usage:
  php run-tests.php [options]

Options:
  --coverage          Generate coverage report
  --filter=<pattern>  Run tests matching pattern
  --unit              Run only unit tests
  --feature           Run only feature tests
  --help              Show this help message

Examples:
  php run-tests.php                           # Run all tests
  php run-tests.php --coverage                # Run with coverage
  php run-tests.php --filter=SaleController   # Run sale controller tests
  php run-tests.php --unit                    # Run unit tests only
  php run-tests.php --feature --coverage      # Run feature tests with coverage

HELP;
    exit(0);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  🧪 POS Kasir Backend - Test Suite                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Build command
$command = 'php artisan test';

if (isset($options['unit'])) {
    $command .= ' --testsuite=Unit';
    echo "📦 Running Unit Tests...\n";
} elseif (isset($options['feature'])) {
    $command .= ' --testsuite=Feature';
    echo "🔧 Running Feature Tests...\n";
} else {
    echo "🎯 Running All Tests...\n";
}

if (isset($options['filter'])) {
    $command .= ' --filter=' . escapeshellarg($options['filter']);
    echo "🔍 Filter: " . $options['filter'] . "\n";
}

if (isset($options['coverage'])) {
    $command .= ' --coverage --min=80';
    echo "📊 Coverage Report: Enabled (minimum 80%)\n";
}

echo "\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "\n";

// Execute tests
$startTime = microtime(true);
passthru($command, $exitCode);
$endTime = microtime(true);

$duration = round($endTime - $startTime, 2);

echo "\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "\n";

if ($exitCode === 0) {
    echo "✅ All tests passed! Duration: {$duration}s\n";
} else {
    echo "❌ Some tests failed. Duration: {$duration}s\n";
}

if (isset($options['coverage'])) {
    echo "\n📊 Coverage reports generated:\n";
    echo "   - HTML: coverage/html/index.html\n";
    echo "   - Text: coverage/coverage.txt\n";
    echo "   - Clover: coverage/clover.xml\n";
}

echo "\n";

exit($exitCode);
