<?php

namespace TwentytwentyfiveChild\Validators;

class CategoryValidator
{
    const REQUIRED_FIELDS = ['id', 'name'];
    const EXPECTED_TYPES = [
        'id' => 'int',
        'name' => 'string',
        'description' => 'string',
        'parent' => 'int',
        'image' => 'string',
    ];
    const UNIQUE_META = ['id' => '_1C_id'];

    private object $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function validate(array $category): array
    {
        $errors = [];

        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($category[$field]) || empty($category[$field])) {
                $errors[] = "The field '$field' must be present and cannot be empty or equal to zero.";
            }
        }

        foreach (self::EXPECTED_TYPES as $field => $expectedType) {
            /**
             * Skip the check if the field is missing from the expected fields.
             * Move on to the next field. 
             * It is allowed that the array may not contain all the expected fields.
             * For example, the "Description" field may not be present for the category
             */
            if (!isset($category[$field])) {
                continue;
            }

            switch ($expectedType) {
                case 'int':
                    if (!is_int($category[$field])) {
                        $errors[] = "The '$field' field must be an integer.";
                    }
                    break;
                case 'string':
                    if (!is_string($category[$field])) {
                        $errors[] = "The '$field' field must be a string.";
                    }
                    break;
                case 'array':
                    if (!is_array($category[$field])) {
                        $errors[] = "The '$field' field must be an array.";
                    }
                    break;
                default:
                    $errors[] = "Unknown type for the '$field' field.";
                    break;
            }
        }

        foreach (self::UNIQUE_META as $field => $uniqueMetaKey) {
            if (!isset($category[$field])) {
                continue;
            }

            if ($this->checkUniqueMeta($uniqueMetaKey, $category[$field])) {
                $errors[] = "The value of the field '$field' ('{$category[$field]}') is already occupied.";
            }
        }

        return $errors;
    }

    private function checkUniqueMeta(string $metaKey, string $metaValue)
    {
        $existingMeta = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT meta_id FROM {$this->wpdb->prefix}termmeta WHERE meta_key = %s AND meta_value = %s",
            $metaKey,
            $metaValue
        ));

        return $existingMeta;
    }
}
