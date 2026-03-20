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

        $_SERVER['HTTP_AUTHORIZATION'] = $authorizationHeader;

        $this->assertTrue($authenticator->authenticate());

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public static function validCredentialsDataProvider(): array
    {
        $authorizationHeader = 'Basic ' . base64_encode(ONE_C_USERNAME . ':' . ONE_C_PASSWORD);

        return [
            [ONE_C_USERNAME, ONE_C_PASSWORD, $authorizationHeader]
        ];
    }
}
