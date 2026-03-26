<?php

namespace TwentytwentyfiveChild\Exceptions\Auth;

class ErrorDecodingAuthDataException extends \Exception
{
    public const HTTP_STATUS = 401;

    public function __construct(
        string $message = "Error decoding authorization data.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function get_http_status(): int
    {
        return self::HTTP_STATUS;
    }
}
