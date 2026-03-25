<?php

namespace TwentytwentyfiveChild\Loggers;

class RawRequestLogger
{
    private string $logFilePath;

    public function __construct(?string $logFilePath = null)
    {
        $this->logFilePath = $logFilePath ?? WP_CONTENT_DIR . '/debug.log';
    }

    public function logRawRequest(string $method, string $uri, string $rawInput, array $headers)
    {
        $logMessage = sprintf(
            "Raw Request:\nMethod: %s\nURI: %s\nHeaders: %s\nBody: %s",
            $method,
            $uri,
            json_encode($headers, JSON_PRETTY_PRINT),
            $rawInput
        );

        $this->writeLogToFile($logMessage);
    }

    private function writeLogToFile(string $message): void
    {

        if (!$this->ensureDirectoryExists(dirname($this->logFilePath))) {
            throw new \Exception('Failed to create a directory for the log file.');
        }

        $handle = fopen($this->logFilePath, 'ab+');
        if ($handle === false) {
            throw new \Exception('The log file could not be opened for writing.');
        }

        fwrite($handle, $message);
        fclose($handle);
    }

    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        return mkdir($directory, 0755, true);
    }
}
