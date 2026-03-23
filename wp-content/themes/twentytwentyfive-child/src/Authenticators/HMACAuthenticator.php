<?php

namespace TwentytwentyfiveChild\Authenticators;

use TwentytwentyfiveChild\Helpers\Helper;

class HMACAuthenticator
{

    private string $secret;
    private int $maxAge;

    public function __construct(string $secret, int $maxAge)
    {
        $this->secret = $secret;
        $this->maxAge = $maxAge;
    }

    public function authenticate(string $authHeader, string $requestUri, string $requestMethod, string $requestBody): bool
    {
        // Empty auth header
        if (empty($authHeader)) {
            throw new \Exception('The authorization header is missing.');
        }

        // Сheck header format
        if (!preg_match('/^HMAC\s+(\d+)\.\s*([A-Za-z0-9\-_]+)$/', $authHeader, $matches)) {
            throw new \Exception('Invalid authorization header format.', 401);
        }

        // Get timestamp and signature from the auth header
        $timestamp = (int)$matches[1];
        $receivedSignature = $matches[2];

        // Expired signature
        if (abs(time() - $timestamp) >= $this->maxAge) {
            throw new \Exception('Expired HMAC signature.', 401);
        }

        // Generate body hash
        $bodyHash = hash('sha256', $requestBody);

        // Get uri without query-parameters
        $uri = strtok($requestUri, '?');

        // Collecting data to create signature
        $data = implode("\n", [
            $requestMethod,
            $uri,
            $timestamp,
            $bodyHash,
        ]);

        // Generate expected binary signature
        $binarySignature = hash_hmac('sha256', $data, $this->secret, true);

        // Transform binary signature into base64
        $expectedSignature = Helper::base64UrlEncode($binarySignature);

        // Invalid signature length
        if (strlen($expectedSignature) !== strlen($receivedSignature)) {
            throw new \Exception('Invalid HMAC signature length.', 401);
        }

        // Invalid signature
        if (!hash_equals($expectedSignature, $receivedSignature)) {
            throw new \Exception('Invalid HMAC signature.', 401);
        }

        return true;
    }
}
