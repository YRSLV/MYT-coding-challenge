<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->category,
            'price' => [
                'original' => $this->price,
                'final' => $this->final_price != null ? $this->final_price : $this->price,
                'discount_percentage' => $this->discount_percentage != null ? strval($this->discount_percentage) . '%' : $this->discount_percentage,
                'currency' => 'EUR'
            ]
        ];
    }
}
