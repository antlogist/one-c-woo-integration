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
        try {
            if (!$this->ensureDirectoryExists(dirname($this->logFilePath))) {
                trigger_error("Failed to create a directory for the log file.", E_USER_WARNING);
                error_log($message);
                return;
            }

            $handle = fopen($this->logFilePath, 'ab+');
            if ($handle === false) {
                trigger_error("The log file could not be opened for writing.", E_USER_WARNING);
                error_log($message);
                return;
            }

            fwrite($handle, $message);
            fclose($handle);
        } catch (\Throwable $exception) {
            trigger_error("Request logging error: {$exception->getMessage()}", E_USER_WARNING);
            error_log($message);
        }
    }

    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        return mkdir($directory, 0755, true);
    }
}
