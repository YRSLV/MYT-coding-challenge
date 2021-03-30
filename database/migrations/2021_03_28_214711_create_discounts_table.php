<?php

use Illuminate\Database\Migrations\Migration;
use Aws\DynamoDb\Exception\DynamoDbException;
use Symfony\Component\Console\Output\ConsoleOutput;

class CreateDiscountsTable extends Migration
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
            'TableName' => 'discounts',
            'KeySchema' => [
                [
                    'AttributeName' => 'discountable_type',
                    'KeyType' => 'HASH'  //Partition key
                ],
                [
                    'AttributeName' => 'discountable_value',
                    'KeyType' => 'RANGE'  //Sort key
                ]
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'discountable_type',
                    'AttributeType' => 'S'  //Partition key
                ],
                [
                    'AttributeName' => 'discountable_value',
                    'AttributeType' => 'S'  //Sort key
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
            'TableName' => 'discounts'
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
