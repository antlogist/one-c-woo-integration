<?php

declare(strict_types=1);

require_once(dirname(__DIR__, 1) . '/tests/config.php');

$tests = [
    'Authenticators/BasicAuthenticatorTest.php',
    'Authenticators/HMACAuthenticatorTest.php',
];

$hasFailures = false;

foreach ($tests as $testFile) {
    echo "\n--- Start test: $testFile ---" . PHP_EOL . str_repeat('-', 50) . PHP_EOL;

    $command = sprintf(
        'docker exec %s php %s %s',
        escapeshellarg(CONTAINER_NAME),
        escapeshellarg(PHPUNIT_PATH),
        escapeshellarg(TESTS_PATH . $testFile)
    );

    $process = proc_open($command, [
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w'], // stderr
    ], $pipes);

    if (is_resource($process)) {
        // stdout
        while (($line = fgets($pipes[1])) !== false) {
            echo $line;
        }

        // stderr
        $hasStderr = false;
        while (($line = fgets($pipes[2])) !== false) {
            fwrite(STDERR, $line);
            $hasStderr = true;
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        // If the return code is not 0, then the test or set has dropped.
        if ($exitCode !== 0 || $hasStderr) {
            echo "--- ERROR: Test '$testFile' failed (code: $exitCode) ---" . PHP_EOL;
            $hasFailures = true;
        } else {
            echo "--- Test '$testFile' was passed successfully ---" . PHP_EOL;
        }
    } else {
        fwrite(STDERR, "Failed to start the test process '$testFile'." . PHP_EOL);
        $hasFailures = true;
    }
}

echo "\n" . str_repeat('=', 60) . PHP_EOL;
if ($hasFailures) {
    echo "⛔ ONE OR MORE TESTS FAILED." . PHP_EOL;
    exit(1);
} else {
    echo "✅ ALL TESTS PASSED SUCCESSFULLY!" . PHP_EOL;
    exit(0);
}
