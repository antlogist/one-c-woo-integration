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

    public function testMissingAuthorizationHeaderThrowsException()
    {
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The authorization header is missing.');

        $authenticator->authenticate();
    }

    public function testInvalidAuthorizationHeaderThrowsException()
    {
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'header data';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid authorization header format.');

        $authenticator->authenticate();

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testDecodingErrorThrowsException()
    {
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic wrong_header_data';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error decoding authorization data.');

        $authenticator->authenticate();

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testMissingLoginOrPasswordThrowsException()
    {
        // error_reporting(E_ALL);
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('only_username');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Login or password is missing.');

        $authenticator->authenticate();

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testIncorrectUsernameThrowException()
    {
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('incorrect_username:any_password');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Incorrect username or password.');

        $authenticator->authenticate();

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testIncorrectPasswordThrowException()
    {
        $authenticator = new BasicAuthenticator('any_username', 'any_password');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('any_username:incorrect_password');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Incorrect username or password.');

        $authenticator->authenticate();

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
