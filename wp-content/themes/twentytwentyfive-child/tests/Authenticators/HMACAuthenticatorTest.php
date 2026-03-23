<?php

declare(strict_types=1);

require_once(dirname(__DIR__, 1) . '/config.php');

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TwentytwentyfiveChild\Authenticators\HMACAuthenticator;
use TwentytwentyfiveChild\Helpers\Helper;

final class HMACAuthenticatorTest extends TestCase
{
    private const array BODY = ['test' => 'test'];
    private const string METHOD = 'POST';
    private const string URI = '/wp-json/import/v1/categories';

    private HMACAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new HMACAuthenticator(HMAC_SECRET, 60);
    }

    #[DataProvider('authHeaderDataProvider')]
    public function testInvalidAuthHeaderScenarios(string $authHeader, string $expectedMessage)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->authenticator->authenticate(
            $authHeader,
            self::URI,
            self::METHOD,
            json_encode(self::BODY)
        );
    }

    public static function authHeaderDataProvider(): array
    {
        return [
            'Missing header' => ['', 'The authorization header is missing.'],
            'Invalid format' => ['Bearer some-token', 'Invalid authorization header format.'],
        ];
    }

    #[DataProvider('invalidSignatureDataProvider')]
    public function testInvalidSignatureScenarios(string $authHeader, string $expectedMessage, bool $zeroTTL = false)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $authenticatorInstance = $zeroTTL ? new HMACAuthenticator(HMAC_SECRET, 0) : $this->authenticator;

        $authenticatorInstance->authenticate(
            $authHeader,
            self::URI,
            self::METHOD,
            json_encode(self::BODY)
        );
    }

    public static function invalidSignatureDataProvider(): array
    {
        $validHeader = self::buildAuthHeader(self::URI, self::METHOD, json_encode(self::BODY));

        return [
            'Expired token' => [
                $validHeader,
                'Expired HMAC signature.',
                true
            ],
            'Invalid length' => [
                substr($validHeader, 0, -5),
                'Invalid HMAC signature length.'
            ],
            'Invalid HMAC' => [
                self::buildAuthHeader(self::URI, self::METHOD, json_encode(['test' => 'data'])),
                'Invalid HMAC signature.'
            ]
        ];
    }

    public function testValidAuthentication()
    {
        $authHeader = $this->buildAuthHeader(self::URI, self::METHOD, json_encode(self::BODY));

        $this->assertTrue($this->authenticator->authenticate($authHeader, self::URI, self::METHOD, json_encode(self::BODY)));
    }

    private static function buildAuthHeader(string $url, string $method, string $body): string
    {
        $uri = self::extractUri($url);
        $uri = strtok($uri, '?') ?: $uri;

        $timestamp = time();

        $bodyContent = $body;

        $bodyHash = hash('sha256', $bodyContent);

        $data = implode("\n", [
            $method,
            $uri,
            $timestamp,
            $bodyHash,
        ]);

        $signature = hash_hmac('sha256', $data, HMAC_SECRET, true);
        $signature = Helper::base64UrlEncode($signature);

        return "HMAC $timestamp.$signature";
    }

    private static function extractUri(string $url): string
    {
        $parsedUrl = parse_url($url);

        $normalizedPath = '/' . trim($parsedUrl['path'] ?? '/', '/');

        $uri = $normalizedPath;

        return $uri;
    }
}
