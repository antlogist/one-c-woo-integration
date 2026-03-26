<?php

namespace TwentytwentyfiveChild\Validators;

class RequestValidator
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function validateContentType(string $expectedType)
    {
        $providedType = $this->request->get_header('Content-Type');

        if (strcasecmp($providedType, $expectedType) !== 0) {
            throw new \InvalidArgumentException("Expected Content-Type: $expectedType");
        }
    }
}
