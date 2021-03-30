<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use BaoPham\DynamoDb\DynamoDbModel;

class Product extends DynamoDbModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'category';

    /**
     * Array of your composite key.
     * ['<hash>', '<range>']
     *
     * @var array
     */
    protected $compositeKey = ['category', 'sku'];

    /**
     * Indexes.
     * [
     *     '<simple_index_name>' => [
     *          'hash' => '<index_key>'
     *     ],
     *     '<composite_index_name>' => [
     *          'hash' => '<index_hash_key>',
     *          'range' => '<index_range_key>'
     *     ],
     * ]
     *
     * @var array
     */
    protected $dynamoDbIndexKeys = [
        'price-index' => [
            'hash' => 'category',
            'range' => 'price'
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sku',
        'name',
        'category',
        'price'
    ];

}
