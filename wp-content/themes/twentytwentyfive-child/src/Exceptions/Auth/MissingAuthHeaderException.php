<?php

namespace TwentytwentyfiveChild\Exceptions\Auth;

class MissingAuthHeaderException extends \Exception
{
    public const HTTP_STATUS = 401;

    public function __construct(
        string $message = "The authorization header is missing.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatus(): int
    {
        return self::HTTP_STATUS;
    }
}
