<?php

declare(strict_types=1);

require_once(dirname(__DIR__, 5) . '/wp-load.php');


use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TwentytwentyfiveChild\Validators\CategoryValidator;

final class CategoryValidatorTest extends TestCase
{
    #[DataProvider('validCategoryDataProvider')]
    public function testValidCategoryScenarios(array $item, array $expectedArray)
    {
        global $wpdb;

        $validator = new CategoryValidator($wpdb);

        $result = $validator->validate($item);

        Assert::assertSame($result, $expectedArray);
    }

    public static function ValidCategoryDataProvider(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'name' => 'Category name',
                    'description' => 'Category description',
                    'parent' => 1,
                    'image' => 'https://loremflickr.com/1024/512',
                ],
                []
            ]
        ];
    }

    #[DataProvider('invalidCategoryDataProvider')]
    public function testInvalidCategoryScenarios(array $item, array $expectedArray)
    {
        global $wpdb;

        $validator = new CategoryValidator($wpdb);

        $result = $validator->validate($item);

        Assert::assertSame($result, $expectedArray);
    }

    public static function invalidCategoryDataProvider(): array
    {
        $idEmptyError = 'The field \'id\' must be present and cannot be empty or equal to zero.';
        $nameEmptyError = 'The field \'name\' must be present and cannot be empty or equal to zero.';
        $idTypeError = 'The \'id\' field must be an integer.';
        $nameTypeError = 'The \'name\' field must be a string.';
        $descriptionTypeError = 'The \'description\' field must be a string.';
        $parentTypeError = 'The \'parent\' field must be an integer.';
        $imageTypeError = 'The \'image\' field must be a string.';

        return [
            'Empty array' => [
                [],
                [
                    $idEmptyError,
                    $nameEmptyError
                ]
            ],
            'Required id' => [
                ["name" => 'Category name'],
                [$idEmptyError]
            ],
            'Zero id' => [
                [
                    'id' => 0,
                    "name" => 'Category name'
                ],
                [$idEmptyError]
            ],
            'Null id' => [
                [
                    'id' => null,
                    "name" => 'Category name'
                ],
                [$idEmptyError]
            ],
            'Empty id' => [
                [
                    'id' => '',
                    'name' => 'Category name'
                ],
                [
                    $idEmptyError,
                    $idTypeError
                ]
            ],
            'Required name' => [
                [
                    'id' => 1,
                ],
                [$nameEmptyError],
            ],
            'Int name' => [
                [
                    'id' => 1,
                    'name' => 2
                ],
                [$nameTypeError],
            ],
            'Null name' => [
                [
                    'id' => 1,
                    'name' => null
                ],
                [
                    $nameEmptyError,
                ],
            ],
            'Int description' => [
                [
                    'id' => 1,
                    'name' => 'Category name',
                    'description' => 1
                ],
                [
                    $descriptionTypeError,
                ],
            ],
            'String parent' => [
                [
                    'id' => 1,
                    'name' => 'Category name',
                    'parent' => 'string'
                ],
                [
                    $parentTypeError,
                ],
            ],
            'Int image' => [
                [
                    'id' => 1,
                    'name' => 'Category name',
                    'image' => 1
                ],
                [
                    $imageTypeError,
                ],
            ],
        ];
    }
}
