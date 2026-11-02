<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           'type' => 'product', 
            'id' => $this->id, 
            'attributes' => [ 
                'name' => $this->name, 
                'description' => $this->when( 
                    !$request->routeIs('products.index'), 
                    $this->description 
                ), 
                'price' => $this->price,
                'category' => $this->whenLoaded('category', fn () => $this->category->name),
                'createdAt' => $this->created_at, 
                'updatedAt' => $this->updated_at, 
            ], 
            'relationships' => [ 
                'category' => [ 
                    'data' => [ 
                        'type' => 'category', 
                        'id' => $this->category_id, 
                    ], 


                    'links' => [ 
                        'self' => route( 
                            'categories.show', 
                            [
                                'category' => $this->category_id 
                            ] 
                        ),
                    ], 
                ], 
            ], 
            'links' => [ 
                'self' => route( 
                    'products.show', 
                    [
                        'product' => $this->id 
                    ] 
                ), 
            ], 

        ];
    }
}
