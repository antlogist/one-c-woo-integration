<?php

use TwentytwentyfiveChild\Authenticators\BasicAuthenticator;
use TwentytwentyfiveChild\Facades\RequestProcessor;
use TwentytwentyfiveChild\Loggers\RawRequestLogger;
use TwentytwentyfiveChild\Validators\RequestValidator;

add_action('rest_api_init', function () {
    register_rest_route(
        'import/v1',
        '/categories',
        array(
            'methods' => 'POST',
            'callback' => 'custom_wc_import_categories',
            'permission_callback' => '__return_true'
        )
    );
});

function custom_wc_import_categories(WP_REST_Request $request)
{
    $processor = new RequestProcessor(
        new BasicAuthenticator(ONE_C_USERNAME, ONE_C_PASSWORD),
        new RequestValidator($request),
        new RawRequestLogger(WP_CONTENT_DIR . '/logs/import/category.log')
    );

    try {
        $processor->process($request);
    } catch (WP_Exception $e) {
        $statusCode = $e->getCode();
        $errorMessage = $e->getMessage();

        if ($statusCode === 401) {
            return new WP_Error('unauthorized', $errorMessage, ['status' => 401]);
        } elseif ($statusCode === 400 || $statusCode === 415) {
            return new WP_Error('rest_invalid_param', $errorMessage, ['status' => $statusCode]);
        } else {
            return new WP_Error('rest_forbidden', $errorMessage, ['status' => $statusCode]);
        }
    }
}
