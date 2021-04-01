<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductTest extends TestCase
{
        
    /**
     * testEndpointReturnsValidFormattedJson
     *
     * @return void
     */
    public function testEndpointReturnsValidFormattedJson()
    {
        $response = $this->json('GET', 'api/v1/products');

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'products' => [
                            '0' => [
                            'sku',
                            'name',
                            'category',
                            'price' => [
                                'original',
                                'final',
                                'discount_percentage',
                                'currency'
                            ]
                        ]
                    ]
                    ]);
    }
    
    /**
     * testEndpointReturnsAtMostFiveElements
     *
     * @return void
     */
    public function testEndpointReturnsAtMostFiveElements()
    {
        $response = $this->json('GET', 'api/v1/products');

        $response->assertJsonCountLessOrEqualTo(5, $response['products']);
    }
        
    /**
     * testEndpointSupportsFilteringByCategoryAsQueryStringParam
     *
     * @return void
     */
    public function testEndpointSupportsFilteringByCategoryAsQueryStringParam()
    {
        $response = $this->json('GET', 'api/v1/products?category=boots');

        $response->assertStatus(200)
                    ->assertExactJson([
                        'products'=> [
                            [
                                'sku'=> '000003',
                                'name'=> 'Ashlington leather ankle boots',
                                'category'=> 'boots',
                                'price'=> [
                                    'original'=> 71000,
                                    'final'=> 39050,
                                    'discount_percentage'=> '45%',
                                    'currency'=> 'EUR'
                                ]
                            ],
                            [
                                'sku'=> '000001',
                                'name'=> 'BV Lean leather ankle boots',
                                'category'=> 'boots',
                                'price'=> [
                                    'original'=> 89000,
                                    'final'=> 62300,
                                    'discount_percentage'=> '30%',
                                    'currency'=> 'EUR'
                                ]
                            ],
                            [
                                'sku'=> '000002',
                                'name'=> 'BV Lean leather ankle boots',
                                'category'=> 'boots',
                                'price'=> [
                                    'original'=> 99000,
                                    'final'=> 69300,
                                    'discount_percentage'=> '30%',
                                    'currency'=> 'EUR'
                                ]
                            ]
                        ]
                    ]);

    }
    
    /**
     * testEndpointSupportsFilteringByPriceLessThanAsQueryStringParam
     *
     * @return void
     */
    public function testEndpointSupportsFilteringByPriceLessThanAsQueryStringParam()
    {
        $response = $this->json('GET', 'api/v1/products?priceLessThan=71000');

        $response->assertStatus(200)
                    ->assertExactJson([
                        'products'=> [
                            [
                                'sku'=> '000003',
                                'name'=> 'Ashlington leather ankle boots',
                                'category'=> 'boots',
                                'price'=> [
                                    'original'=> 71000,
                                    'final'=> 39050,
                                    'discount_percentage'=> '45%',
                                    'currency'=> 'EUR'
                                ]
                            ],
                            [
                                'sku'=> '000005',
                                'name'=> 'Nathane leather sneakers',
                                'category'=> 'sneakers',
                                'price'=> [
                                    'original'=> 59000,
                                    'final'=> 59000,
                                    'discount_percentage'=> null,
                                    'currency'=> 'EUR'
                                ]
                            ]
                        ]
                    ]);
    }
    
    /**
     * testEndpointSupportsFilteringByAllQueryStringParams
     *
     * @return void
     */
    public function testEndpointSupportsFilteringByAllQueryStringParams()
    {
        $response = $this->json('GET', 'api/v1/products?category=boots&priceLessThan=90000');

        $response->assertStatus(200)
                    ->assertExactJson([
                        'products'=> [
                            [
                                'sku'=> '000003',
                                'name'=> 'Ashlington leather ankle boots',
                                'category'=> 'boots',
                                'price'=> [
                                    'original'=> 71000,
                                    'final'=> 39050,
                                    'discount_percentage'=> '45%',
                                    'currency'=> 'EUR'
                                ]
                            ],
                            [
                                'sku'=> '000001',
                                'name'=> 'BV Lean leather ankle boots',
                                'category'=> 'boots',
                                'price'=> [
                                    'original'=> 89000,
                                    'final'=> 62300,
                                    'discount_percentage'=> '30%',
                                    'currency'=> 'EUR'
                                ]
                            ]
                        ]
                    ]);
    }
    
    /**
     * testEndpointReturnsNotFoundExceptionForWrongQuery
     *
     * @return void
     */
    public function testEndpointReturnsNotFoundExceptionForWrongQuery()
    {
        $response = $this->json('GET', 'api/v1/wrongproducts');

        $response->assertStatus(404)
                    ->assertExactJson(['error' => 'Resource not found']);
    }
}
