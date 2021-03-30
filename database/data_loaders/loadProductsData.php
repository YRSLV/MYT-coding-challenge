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

$tableName = 'products';

$products = json_decode(file_get_contents(__DIR__.'/data_resources/products_data.json'), true);

foreach ($products as $product) {

    $sku = $product['sku']; 
    $name = $product['name'];
    $category = $product['category'];
    $price = $product['price'];

    $json = json_encode([
        'sku' => $sku,
        'name' => $name,
        'category' => $category,
        'price' => $price
    ]);

    $params = [
        'TableName' => $tableName,
        'Item' => $marshaler->marshalJson($json)
    ];

    try {
        $result = $dynamodb->putItem($params);
        echo "Added product: " . $product['name'] . " " . $product['sku'] . "\n";
    } catch (DynamoDbException $e) {
        echo "Unable to add product:\n";
        echo $e->getMessage() . "\n";
        break;
    }

}