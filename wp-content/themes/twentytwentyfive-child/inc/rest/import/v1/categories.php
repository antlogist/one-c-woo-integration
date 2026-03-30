<?php

use TwentytwentyfiveChild\Authenticators\BasicAuthenticator;
use TwentytwentyfiveChild\Facades\RequestProcessor;
use TwentytwentyfiveChild\Helpers\Helper;
use TwentytwentyfiveChild\Loggers\RawRequestLogger;
use TwentytwentyfiveChild\Services\CategoryImportService;
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
    $requestProcessor = new RequestProcessor(
        new BasicAuthenticator(ONE_C_USERNAME, ONE_C_PASSWORD),
        new RequestValidator($request),
        new RawRequestLogger(WP_CONTENT_DIR . '/logs/import/category.log')
    );

    try {
        $requestProcessor->process($request);
    } catch (WP_Exception $e) {
        $wpErrorCode = Helper::extractWpErrorCode($e->getMessage());
        return new WP_Error($wpErrorCode, $e->getMessage(), ['status' => $e->getCode()]);
    }

    $categoryItems = $request->get_params();
    global $wpdb;

    $categoryService = new CategoryImportService($wpdb);

    return $categoryService->import($categoryItems);
}
