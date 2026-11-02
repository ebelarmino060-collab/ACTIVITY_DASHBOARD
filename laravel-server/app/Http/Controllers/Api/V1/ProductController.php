<?php

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponses; 
use App\Models\Product; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;  
use Illuminate\Http\Request;
use App\Http\Resources\V1\ProductResource;

class ProductController extends Controller
{
        public function index(){
         $products = Product::with('category') 
         
        ->paginate(10); 
 
    // Keep the paginator links/meta in the response
    return ProductResource::collection($products)
        ->additional(['message' => 'Products retrieved successfully']);
    }

   
    //QUERIES
    public function queries(){

    $nameAndPrice = Product::select('name', 'price')->get(); 
    $productsWithCategory = Product::with('category')->get(); 
    $productsOrderedByPrice = Product::select('id', 'name', 'price')
    ->orderBy('price', 'asc') 
    ->get(); 

    $firstFiveProducts = Product::limit(5)->get();  
    
    //FILTERING
     // Get products with a price more than or equal to 3. 
    $priceGreaterThanOrEqual = Product::where('price', '>=', 3)->get(); 
    $electronics = Product::whereHas('category', function ($q) { 
        $q->where('name', 'Electronics'); 
         })->get(); 
    

    //AGGREGSTING
  

    $totalPrice = Product::sum('price'); 
    $averagePrice = Product::avg('price'); 
    $highestPrice = Product::max('price'); 
    $lowestPrice = Product::min('price'); 

    

    // D. GROUPING, FILTERING, AND SORTING 

                // Get the total price of products for each category.
        $totalPricePerCategory = Product::join(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select(
                'categories.name',
                DB::raw('SUM(products.price) as total_price')
            )
            ->groupBy('categories.name')
            ->get();

        // Get the average price for each category.
        $averagePricePerCategory = Product::join(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select(
                'categories.name',
                DB::raw('AVG(products.price) as avg_price')
            )
            ->groupBy('categories.name')
            ->get();

        // Get the lowest price per category.
        $lowestPricePerCategory = Product::join(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select(
                'categories.name',
                DB::raw('MIN(products.price) as min_price')
            )
            ->groupBy('categories.name')
            ->get();

        // Get the average price per category, sorted ascending.
        $averagePriceSorted = Product::join(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select(
                'categories.name',
                DB::raw('AVG(products.price) as avg_price')
            )
            ->groupBy('categories.name')
            ->orderBy('avg_price', 'asc')
            ->get();

        // Get the average price for Electronics and Books.
        $electronicsAndBooks = Product::join(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->whereIn('categories.name', ['Electronics', 'Books'])
            ->select(
                'categories.name',
                DB::raw('AVG(products.price) as avg_price')
            )
            ->groupBy('categories.name')
            ->get();

            $queryResults = [
            'name_and_price' => $nameAndPrice,
            'products_with_category' => $productsWithCategory,
            'products_ordered_by_price' => $productsOrderedByPrice,
            'first_five_products' => $firstFiveProducts,

            'price_greater_than_or_equal' => $priceGreaterThanOrEqual,
            'electronics' => $electronics,

            'total_price' => $totalPrice,
            'average_price' => $averagePrice,
            'highest_price' => $highestPrice,
            'lowest_price' => $lowestPrice,

            'total_price_per_category' => $totalPricePerCategory,
            'average_price_per_category' => $averagePricePerCategory,
            'lowest_price_per_category' => $lowestPricePerCategory,
            'average_price_sorted' => $averagePriceSorted,
            'electronics_and_books' => $electronicsAndBooks,
        ];

        return response()->json([
            'message' => 'Query results retrieved successfully',
            'data' => $queryResults ]
        );
    }
     public function show(Product $product) {

        $product->load('category'); 
         return response()->json([
            'message' => 'Product retrieved successfully', 
            'data' => new ProductResource($product)]);
    }

 
}
