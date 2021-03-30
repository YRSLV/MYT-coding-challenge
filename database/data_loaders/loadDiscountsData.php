<?php

require 'vendor/autoload.php';

date_default_timezone_set('UTC');

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

$sdk = new Aws\Sdk([
    'endpoint'   => 'http://localhost:8001',
    'region'   => 'eu-central-1',
    'version'  => 'latest'
]);

$dynamodb = $sdk->createDynamoDb();
$marshaler = new Marshaler();

$tableName = 'discounts';

$discounts = json_decode(file_get_contents(__DIR__.'/data_resources/discounts_data.json'), true);

foreach ($discounts as $discount) {

    $discountable_type = $discount['discountable_type']; 
    $discountable_value = $discount['discountable_value'];
    $percent_off = $discount['percent_off'];

    $json = json_encode([
        'discountable_type' => $discountable_type,
        'discountable_value' => $discountable_value,
        'percent_off' => $percent_off
    ]);

    $params = [
        'TableName' => $tableName,
        'Item' => $marshaler->marshalJson($json)
    ];

    try {
        $result = $dynamodb->putItem($params);
        echo "Added discount: " . $discount['discountable_type'] . " : " . $discount['discountable_value'] . " " . $discount['percent_off'] . "\n";
    } catch (DynamoDbException $e) {
        echo "Unable to add discount:\n";
        echo $e->getMessage() . "\n";
        break;
    }

}