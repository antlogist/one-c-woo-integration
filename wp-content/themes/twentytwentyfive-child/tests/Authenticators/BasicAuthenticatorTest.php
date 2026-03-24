<?php

declare(strict_types=1);

require_once(dirname(__DIR__, 1) . '/config.php');

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TwentytwentyfiveChild\Authenticators\BasicAuthenticator;

final class BasicAuthenticatorTest extends TestCase
{
    #[DataProvider('validCredentialsDataProvider')]
    public function testValidAuthentication(string $username, string $password, string $authorizationHeader)
    {
        $authenticator = new BasicAuthenticator($username, $password);

        $this->assertTrue($authenticator->authenticate($authorizationHeader));
    }

    public static function validCredentialsDataProvider(): array
    {
        $authorizationHeader = 'Basic ' . base64_encode(ONE_C_USERNAME . ':' . ONE_C_PASSWORD);

        return [
            [ONE_C_USERNAME, ONE_C_PASSWORD, $authorizationHeader]
        ];
    }

    #[DataProvider('invalidAuthDataProvider')]
    public function testInvalidAuthScenarios(string $authHeader, string $expectedMessage)
    {
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $authenticator->authenticate($authHeader);
    }

    public static function invalidAuthDataProvider(): array
    {
        return [
            'Missing header' => ['', 'The authorization header is missing.'],
            'Invalid format' => ['header_data', 'Invalid authorization header format.'],
            'Decoding error' => ['Basic wrong_header_data', 'Error decoding authorization data.'],
            'Missing login or password' => ['Basic ' . base64_encode('only_username'), 'Login or password is missing.'],
            'Incorrect username' => ['Basic ' . base64_encode('incorrect_username:any_password'), 'Incorrect username or password.'],
            'Incorrect password' => ['Basic ' . base64_encode('any_username:incorrect_password'), 'Incorrect username or password.'],
        ];
    }
}
