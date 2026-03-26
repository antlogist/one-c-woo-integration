<?php

namespace TwentytwentyfiveChild\Authenticators;

use TwentytwentyfiveChild\Exceptions\Auth\ErrorDecodingAuthDataException;
use TwentytwentyfiveChild\Exceptions\Auth\InvalidAuthHeaderFormatException;
use TwentytwentyfiveChild\Exceptions\Auth\InvalidLoginOrPassword;
use TwentytwentyfiveChild\Exceptions\Auth\MissingAuthHeaderException;
use TwentytwentyfiveChild\Exceptions\Auth\MissingLoginOrPasswordException;

class BasicAuthenticator
{
    private string $username;
    private string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate(string $authHeader): bool
    {
        if (empty($authHeader)) {
            throw new MissingAuthHeaderException();
        }

        if (strpos($authHeader, 'Basic ') !== 0) {
            throw new InvalidAuthHeaderFormatException();
        }

        $decodedAuth = base64_decode(substr($authHeader, 6), true);
        if (!$decodedAuth || !is_string($decodedAuth)) {
            throw new ErrorDecodingAuthDataException();
        }

        $parts = explode(':', $decodedAuth);
        if (count($parts) === 2 && isset($parts[0]) && isset($parts[1])) {
            list($username, $password) = $parts;
        } else {
            throw new MissingLoginOrPasswordException();
        }

        if ($username !== $this->username || $password !== $this->password) {
            throw new InvalidLoginOrPassword();
        }

        return true;
    }
}
