<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResourceCollection;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use BaoPham\DynamoDb\Facades\DynamoDb;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $category = $request->input('category') ?? null;
        $priceLessThan = $request->input('priceLessThan') ?? null;


        $products = Product::query();

        if ($category) {
            $products->where('category', $category);
        }

        if ($priceLessThan) {
            $products->where('price', '<=', intval($priceLessThan));
        }


        $products = $products->take(5)->withIndex('price-index')->get();

        $this->calculateDiscounts($products);

        return new ProductResourceCollection($products);
    }
    
    /**
     * Calculate discounts for selected products
     *
     * @param  mixed $products
     * @return void
     */
    public function calculateDiscounts($products)
    {
        foreach ($products as $product) {
            $categoryDiscounts = Discount::where('discountable_type', 'category')
                                ->where('discountable_value',  $product['category'])
                                ->get();
            $skuDiscounts = Discount::where('discountable_type', 'sku')
            ->where('discountable_value', $product['sku'])
            ->get();

            $totalDiscount = null;

            if (!empty($categoryDiscounts[0])) {
                $totalDiscount += $categoryDiscounts[0]['percent_off'];
            }

            if (!empty($skuDiscounts[0])) {
                $totalDiscount += $skuDiscounts[0]['percent_off'];
            }

            $product['discount_percentage'] = $totalDiscount;
            $original_price = $product['price'];
            $product['final_price'] = $original_price - ($original_price * ($totalDiscount / 100));
        }
    }
}
