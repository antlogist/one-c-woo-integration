<?php

namespace TwentytwentyfiveChild\Exceptions\Request;

class InvalidContentTypeException extends \DomainException
{
    public const HTTP_STATUS = 415; // Unsupported Media Type

    public function __construct(
        string $expectedType,
        string $providedType,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = "Expected Content-Type: '$expectedType'. Provided: '$providedType'.";
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatus(): int
    {
        return self::HTTP_STATUS;
    }
}
