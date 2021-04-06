
<h1 align="center">
Explanations on decisions taken
</>

----------
# Framework

Laravel is one of the best PHP frameworks in many respects, surpassing its rivals both in terms of speed and memory consumption. With expressive and elegant syntax, powerful in-built features and rich ecosystem Laravel simplifies development and reduces routine tasks to a minimum.

----------

# Controller

The [ProductController](../app/Http/Controllers/Api/V1/ProductController.php) contains `index()` method and two methods used for applicable discount acquisition and calculation.

calculateDiscounts() method uses `foreach()` loop (instead of `array_map()`) as the fastest approach for array traversal. Inside the loop, additional values (for keys:`$product['discount_percentage']`, `$product['final_price']`) are written to every product subarray to pass these values to ProductResource. Every selected  product will have those values added, so that we can access calculated product total discount percentage and final price inside ProductResource.

The `index()` method returns `ProductResourceCollection`, of`ProductResource`. `ProductResource` utilizes Eloquent's resource classes as a much more expressive and robust way for serializing Eloquent (in this case DynamoDB) models to *json* than just calling `toJson` methods on models or collections;

----------

# Database

Database selection process starts with two global options - *Relational* or *Non-Relational* databases. The nature of the initial data, it's organization and structure, access patterns give an initial advantage to *non-relational* databases in this case.

Just to evaluate different approaches I started with different RDBMS options (although I knew that it won't be the best solution). It quickly became clear that for the relational database I would need to implement either *pivot table* design or *EAV*.

While *pivot table* design could probably be improved by using Laravel Polymorphic one-to-many or many-to-many relationships, it wasn't the best possible solution. 

*EAV* gives flexibility to developer to define the schema as needed and this is good in some circumstances. On the other hand it performs very poorly in the case of an ill-defined query and can support other bad practices. The general idea of *EAV* is having a large number of different attributes for describing an entity with a small number of attributes related to an individual instance. *EAV* is useful when the list of attributes is frequently growing, or when it's so large that most rows would be filled with mostly NULLs if you made every attribute a column. It becomes an anti-pattern when used outside of that context. So in this case it clearly becomes an anti-pattern.

First experimental table designs showed all the flaws of the relational model - somewhat complex db design for quite simple dataset, bad scalability and decreasing performance with increasing number of records in the db.

At this point I focused solely on non-relational database options.

One of the best NoSQL databases with unmatched scalability options is **Amazon DynamoDB**. 

Amazon DynamoDB is a key-value and document database that delivers single-digit millisecond performance at any scale. It's a fully managed, multi-region, multi-active, durable database with built-in security, backup and restore, and in-memory caching for internet-scale applications. **DynamoDB** can handle more than 10 trillion requests per day and can support peaks of more than 20 million requests per second. So, it can easily handle a growing list of products, whether it would contain 20 000 or 20 000 000 items.

DynamoDB is **schema-less**. This means that when you create a table, you do not define a rigid table schema, but only specify the attributes of the primary key, such as the partition key or the partition key and the sort key. You can add any type of attribute to any of the table elements at any time.

In DynamoDB you start designing your database by analyzing access patterns. In this case we need to access all products, all products with given category or all products with given category and price less than or equal to one, provided by the user. We can't use `['category', 'sku']` as a composite primary key, because this pair of hash and range key won't uniquely identify an item (with 5 given elements that won't really matter, but as soon as someone adds a product within the same category with the same price it will cause problems), but we can use this pair as a secondary index;

While the primary key looks like this:

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


Initially I planned to use one of the following two implementations of primary key, but the previous option with `protected $compositeKey = ['category', 'sku'];` showed slightly better performance for querying on DynamoDB Local instance, so I decided to leave it as it is for this challenge.

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'sku';

    /**
    * Array of your composite key.
    * ['<hash>', '<range>']
    *
    * @var array
    */
    protected $compositeKey = ['sku', 'name'];

Or:

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'sku';

DynamoDB local uses SQLite behind the scenes. Since the implementation is different from the real DynamoDB, we should expect that the performance will be different as well. So, sometimes DynamoDB Local can produce a bit strange behaviour 🙃.

In real production environment the primary key would be defined by most common access patterns and other requirements and may implement index overloading.

Finally the global secondary index looks like this:

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

This secondary index was designed with future primary key changes in mind. In this particular case it is possible to use local secondary index instead of a global one, as the partition key is the same as in the index. But I decided to leave this secondary index global for better flexibility and separate throughput provisioning.

----------

# Testing

Now, tests may look somewhat unusual and there's a reason for that. Tests contain only the `Act` and `Assert` parts of the standard `Arrange/Act/Assert` pattern mainly because of the DynamoDB Local limitations. You can't have multiple DynamoDB Local instances running at the same time and for the tests you would need to either stop the main 'production' instance and start a new test instance with `-inMemory` option before each test and after the test you would need to stop it or add code to create all tables with a prefix (not to interfere with the main 'production' tables) in arrange part, load items, perform `Act` and `Assert` parts and then delete all test tables, etc. after each test. The first option implementation would involve using Symfony Process component / plain php's `exec()` (or `shell_exec()`) and dealing with DynamoDB Local jar file path or manually starting/stopping DynamoDB Local server with different parameters, eventually breaking 'Tests should be runnable with 1 command' rule. The second option with test tables prefixes requires other manipulations as there's no 'native' support  of table prefixes in baopham/laravel-dynamodb and overriding `getTable()` method in the model class as suggested by baopham in [old feature request](https://github.com/baopham/laravel-dynamodb/issues/199#issuecomment-504762001) is not a proper solution for this case. 

Although it is possible to add an `Arrange` part to the tests, the implementation with DynamoDB Local jar would be pretty ugly and in this case anyway use the same data as in *production* database.

So I decided to go with the production db for the tests, as no changes are made during tests runtime and not having to deal with some tricky DynamoDB Local configurations saves a fair amount of time and extra hassle.
In real production environment the data would be stored in DynamoDB instance on Amazon servers with according throughput, performance etc. And tests would be easily runnable on DynamoDB local instance.

The issue with DynamoDB Local testing has been discussed [here](https://stackoverflow.com/q/26901613/13563785). Some of the answers suggest using Docker image, but according to baopham/laravel-dynamodb [issue reply](https://github.com/baopham/laravel-dynamodb/issues/238) the Docker container is *kind of slow* and we know that:

'DynamoDB Local on the other hand is quite slow because it uses a SQLite database behind the scenes (You can check this by opening the *.db file in the DynamoDBLocal directory with a SQLite Browser) and SQLite has very slow write speeds compared to other databases.'

DynamoDB Local is just a basic tool for local development that wraps a subset of the DynamoDB API around a simple SQLite database. The real DynamoDB, of course, does not rely on SQLite and is much faster.

So, there is no point in slowing down tests even further.

----------

⚠ ***Note:*** This document may contain typos. ⚠