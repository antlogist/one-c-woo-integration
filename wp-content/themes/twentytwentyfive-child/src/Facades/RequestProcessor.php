<?php

namespace TwentytwentyfiveChild\Facades;

use Exception;
use TwentytwentyfiveChild\Authenticators\BasicAuthenticator;
use TwentytwentyfiveChild\Exceptions\Auth\ErrorDecodingAuthDataException;
use TwentytwentyfiveChild\Exceptions\Auth\InvalidAuthHeaderFormatException;
use TwentytwentyfiveChild\Exceptions\Auth\InvalidLoginOrPassword;
use TwentytwentyfiveChild\Exceptions\Auth\MissingAuthHeaderException;
use TwentytwentyfiveChild\Exceptions\Auth\MissingLoginOrPasswordException;
use TwentytwentyfiveChild\Exceptions\Request\InvalidContentTypeException;
use TwentytwentyfiveChild\Loggers\RawRequestLogger;
use TwentytwentyfiveChild\Validators\RequestValidator;
use WP_Exception;
use WP_REST_Request;

class RequestProcessor
{
    private object $authenticator;
    private object $validator;
    private object $logger;
    private string $expectedContentType;
    private string $route = '';

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

        $this->route = $request->get_route();
    }

    private function authenticate(WP_REST_Request $request): void
    {
        $authHeader = $request->get_header('authorization');

        try {
            $this->authenticator->authenticate($authHeader);
        } catch (
            MissingAuthHeaderException |
            InvalidAuthHeaderFormatException |
            ErrorDecodingAuthDataException |
            MissingLoginOrPasswordException |
            InvalidLoginOrPassword $e
        ) {
            error_log('Auth error: ' . $e->getMessage() . ' | Route: ' . $this->route);
            throw new WP_Exception('unauthorized: ' . $e->getMessage(), $e->getHttpStatus());
        } catch (\Throwable $e) {
            error_log('Critical error in RequestProcessor: ' . $e->getMessage());
            throw new WP_Exception('internal_server_error: Internal Server Error', 500);
        }
    }

    private function validate(): void
    {
        try {
            $this->validator->validateContentType($this->expectedContentType);
        } catch (InvalidContentTypeException $e) {
            error_log("Request error: " . $e->getMessage());
            throw new WP_Exception('rest_invalid_param: ' . $e->getMessage(), $e->getHttpStatus());
        } catch (\Throwable $e) {
            error_log('Critical error in RequestProcessor: ' . $e->getMessage());
            throw new WP_Exception('internal_server_error: Internal Server Error', 500);
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
