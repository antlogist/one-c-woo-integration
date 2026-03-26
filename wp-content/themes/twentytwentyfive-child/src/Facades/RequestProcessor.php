<?php

namespace TwentytwentyfiveChild\Facades;

use Exception;
use TwentytwentyfiveChild\Authenticators\BasicAuthenticator;
use TwentytwentyfiveChild\Loggers\RawRequestLogger;
use TwentytwentyfiveChild\Validators\RequestValidator;
use WP_REST_Request;

class RequestProcessor
{
    private $authenticator;
    private $validator;
    private $logger;
    private $expectedContentType;

    public function __construct(
        BasicAuthenticator $authenticator,
        RequestValidator $validator,
        RawRequestLogger $logger,
        string $expectedContentType = 'application/json'
    ) {
        $this->authenticator = $authenticator;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->expectedContentType = $expectedContentType;
    }

    public function process(WP_REST_Request $request): void
    {
        $this->authenticate($request);
        $this->validate();
        $this->log($request);
    }

    private function authenticate(WP_REST_Request $request)
    {
        $authHeader = $request->get_header('authorization');

        try {
            $this->authenticator->authenticate($authHeader);
        } catch (Exception $e) {
            error_log('Auth error: ' . $e->getMessage());

            throw new Exception($e->getMessage(), 401);
        }
    }

    private function validate(): void
    {
        try {
            $this->validator->validateContentType($this->expectedContentType);
        } catch (Exception $e) {
            error_log("Validation error: " . $e->getMessage());
            throw new Exception($e->getMessage(), 400);
        }
    }

    private function log(WP_REST_Request $request): void
    {
        try {
            $method = $request->get_method();
            $uri = $request->get_route();
            $headers = $request->get_headers();
            $rawInput = file_get_contents('php://input');

            $this->logger->logRawRequest($method, $uri, $rawInput, $headers);
        } catch (Exception $e) {
            error_log('Logging error: ' . $e->getMessage());
        }
    }
}
