<?php

namespace TwentytwentyfiveChild\Helpers;

class Helper
{
    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function extractWpErrorCode(string $errorMessage): string
    {
        $colonPosition = strpos($errorMessage, ':');

        if ($colonPosition !== false) {
            return substr($errorMessage, 0, $colonPosition);
        }

        return 'unknown_error';
    }
}
