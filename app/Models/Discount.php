<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use BaoPham\DynamoDb\DynamoDbModel;

class Discount extends DynamoDbModel
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discounts';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'discountable_type';

    /**
     * Array of your composite key.
     * ['<hash>', '<range>']
     *
     * @var array
     */
    protected $compositeKey = ['discountable_type', 'discountable_value'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'discountable_type',
        'discountable_value',
        'percent_off'
    ];
}
