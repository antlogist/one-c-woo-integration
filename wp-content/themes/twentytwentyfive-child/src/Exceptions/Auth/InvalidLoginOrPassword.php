<?php

namespace TwentytwentyfiveChild\Exceptions\Auth;

class InvalidLoginOrPassword extends \Exception
{
    public const HTTP_STATUS = 401;

    public function __construct(
        string $message = "Invalid login or password.",
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
