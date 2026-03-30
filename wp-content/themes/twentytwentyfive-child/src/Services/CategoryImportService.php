<?php

namespace TwentytwentyfiveChild\Services;

use TwentytwentyfiveChild\Validators\CategoryValidator;
use WP_Error;
use WP_Exception;
use WP_REST_Response;

class CategoryImportService
{
    const META_KEY_1C_ID = '_1C_id';
    const META_KEY_1C_IMG_URL = '_1C_external_image_url';

    private object $wpdb;
    private object $validator;
    private array $errors = [];
    private array $createdItems = [];
    private array $createdItems1C = [];
    private int $totalItemsCount = 0;
    private int $createdItemsCount = 0;
    private int $failedItemsCount = 0;

    public function __construct(object $wpdb)
    {
        $this->wpdb = $wpdb;
        $this->wpdb->show_errors(false);
        $this->validator = new CategoryValidator($this->wpdb);
    }
    public function import(array $categories): WP_REST_Response
    {
        $this->totalItemsCount = count($categories);

        foreach ($categories as $item) {
            // Validation
            $errors = $this->validator->validate($item);

            if (!empty($errors)) {
                $this->errors[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'errors' => $errors
                ];
                continue;
            }

            // Meta-data
            try {
                $meta = $this->prepareTermMetaData($item);
            } catch (\Exception $e) {
                continue;
            }

            // Import category
            try {
                $createdCategory = $this->createCategory($item, $meta);
            } catch (\Exception $e) {
                continue;
            }

            $this->createdItems[] = $createdCategory['term_id'];
            $this->createdItems1C[] = $item['id'] ?? '';

            // 1C id
            $this->addMetaCategory($createdCategory['term_id'], self::META_KEY_1C_ID, $item['id'] ?? '', $item);

            // 1C external image
            $this->addMetaCategory($createdCategory['term_id'], self::META_KEY_1C_IMG_URL, $item['image'] ?? '', $item);
        }

        $this->createdItemsCount = count($this->createdItems);
        $this->failedItemsCount = count($this->errors);

        if ($this->totalItemsCount == $this->createdItemsCount && $this->createdItemsCount > 0) {
            $message = 'The data has been successfully imported.';
            $status = 'success';
            $statusCode = 200;
        } else if ($this->totalItemsCount > $this->createdItemsCount && $this->createdItemsCount > 0) {
            $message = 'The import was partially completed.';
            $status = 'partial_success';
            $statusCode = 207; // Partial import
        } else {
            $message = 'Import error.';
            $status = 'error';
            $statusCode = 400;  // Nothing has been created
        }

        $response = new WP_REST_Response([
            'errors' => $this->errors,
            'total_items' => $this->totalItemsCount,
            'successfull_imports' => $this->createdItemsCount,
            'failed_imports' => $this->failedItemsCount,
            'created_category_items_wp' => $this->createdItems,
            'created_category_items_1c' => $this->createdItems1C,
            'message' => $message,
            'status' => $status
        ]);

        $response->set_status($statusCode);

        error_log("\nRequest result:\n" . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $response;
    }

    private function prepareTermMetaData(array $item): array
    {
        $itemMeta = [];

        if (isset($item['description'])) {
            $itemMeta['description'] = $item['description'];
        }

        if (isset($item['parent']) && $item['parent'] > 0) {
            try {
                $parentCatId = $this->findParentCategoryBy1CId($item['parent']);

                if ($parentCatId) {
                    $itemMeta['parent'] = $parentCatId;
                } else {
                    throw new \Exception('Couldn\'t find the parent category with _1C_id: ' . $item['parent']);
                }
            } catch (\Exception $e) {
                $this->handleError($e, $item);

                throw $e;
            }
        }

        return $itemMeta;
    }

    private function findParentCategoryBy1CId($metaValue)
    {
        $termID = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT term_id FROM {$this->wpdb->prefix}termmeta WHERE meta_key = %s AND meta_value = %s",
            '_1C_id',
            $metaValue
        ));

        if ($this->wpdb->last_error) {
            error_log('Database error: ' . $this->wpdb->last_error);
            throw new \Exception($this->wpdb->last_error);
        }

        return $termID;
    }

    private function createCategory(array $item, array $meta): array
    {
        $result = wp_insert_term($item['name'], 'product_cat', $meta);

        if (is_wp_error($result)) {
            $this->handleError($result, $item);
            throw new \Exception($result->get_error_message());
        }

        return $result;
    }

    private function addMetaCategory(int $id, string $metaKey, string $metaValue, array $item): bool
    {
        $result = update_term_meta($id, $metaKey, $metaValue);

        if (!$result) {
            $exception = new \Exception("Error when writing meta data for the category #$id.");
            $this->handleError($$exception, $item, false);
        }

        return $result;
    }

    private function handleError(\Exception|WP_Error $e, array $item, bool $count = true): void
    {
        $message = match (true) {
            $e instanceof \Exception => $e->getMessage(),
            $e instanceof WP_Error => implode("\n", $e->get_error_messages()),
            default => 'Unknown error'
        };

        error_log(sprintf(
            '[%s] Error importing item #%d (%s): %s',
            get_class($e),
            $item['id'] ?? '',
            $item['name'] ?? '',
            $message
        ));

        $this->errors[] = [
            'id' => $item['id'] ?? '',
            'name' => $item['name'] ?? '',
            'error' => $message
        ];

        if ($count) {
            $this->failedItemsCount++;
        }
    }
}
