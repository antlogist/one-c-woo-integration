<?php

namespace TwentytwentyfiveChild\Authenticators;

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
            throw new \Exception('The authorization header is missing.');
        }

        if (strpos($authHeader, 'Basic ') !== 0) {
            throw new \Exception('Invalid authorization header format.');
        }

        $decodedAuth = base64_decode(substr($authHeader, 6), true);
        if (!$decodedAuth || !is_string($decodedAuth)) {
            throw new \Exception('Error decoding authorization data.');
        }

        $parts = explode(':', $decodedAuth);
        if (count($parts) === 2 && isset($parts[0]) && isset($parts[1])) {
            list($username, $password) = $parts;
        } else {
            throw new \Exception('Login or password is missing.');
        }

        if ($username !== $this->username || $password !== $this->password) {
            throw new \Exception('Incorrect username or password.');
        }

        return true;
    }
}
