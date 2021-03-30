<?php

use Illuminate\Database\Migrations\Migration;
use Aws\DynamoDb\Exception\DynamoDbException;
use Symfony\Component\Console\Output\ConsoleOutput;


class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $output = new ConsoleOutput();

        $dynamodb = $this->prepareDbConnection();
        
        $params = [
            'TableName' => 'products',
            'KeySchema' => [
                [
                    'AttributeName' => 'category',
                    'KeyType' => 'HASH'  //Partition key
                ],
                [
                    'AttributeName' => 'sku',
                    'KeyType' => 'RANGE'  //Sort key
                ]
            ],
            'GlobalSecondaryIndexes' => [
                [
                    'IndexName' => 'price-index', 
                    'KeySchema' => [ 
                        [
                            'AttributeName' => 'category',
                            'KeyType' => 'HASH',
                        ],
                        [
                            'AttributeName' => 'price',
                            'KeyType' => 'RANGE',
                        ],
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL',
                    ],
                    'ProvisionedThroughput' => [
                        'ReadCapacityUnits' => 10,
                        'WriteCapacityUnits' => 10,
                    ],
                ],
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'category',
                    'AttributeType' => 'S'  //Partition key
                ],
                [
                    'AttributeName' => 'sku',
                    'AttributeType' => 'S'  //Sort key
                ],
                [
                    'AttributeName' => 'price',
                    'AttributeType' => 'N'  //Sort key
                ],
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 10,
                'WriteCapacityUnits' => 10
            ]
        ];
        
        try {
            $result = $dynamodb->createTable($params);
            $output->writeln('Created table.  Status: ' . 
                $result['TableDescription']['TableStatus'] ."\n");
        
        } catch (DynamoDbException $e) {
            $output->writeln("Unable to create table:\n");
            $output->writeln($e->getMessage() . "\n");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $output = new ConsoleOutput();

        $dynamodb = $this->prepareDbConnection();

        $params = [
            'TableName' => 'products'
        ];
        
        try {
            $result = $dynamodb->deleteTable($params);
            $output->writeln("Deleted table.\n");
        
        } catch (DynamoDbException $e) {
            $output->writeln("Unable to delete table:\n");
            $output->writeln($e->getMessage() . "\n");
        }
    }

    public function prepareDbConnection()
    {
        $sdk = new Aws\Sdk([
            'endpoint'   => 'http://localhost:8001',
            'region'   => 'eu-central-1',
            'version'  => 'latest'
        ]);
        
        $dynamodb = $sdk->createDynamoDb();

        return $dynamodb;
    }
}
