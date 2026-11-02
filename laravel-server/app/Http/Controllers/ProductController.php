<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Http\Resources\V1\ProductResource;

class ProductController extends Controller
{

    public function queries() {
    // ========================================================= 

    // A. QUERYING TABLES 

    // ========================================================= 

 

 

    // Get the name and price columns from the table. 

    $nameAndPrice = Product::select('name', 'price')->get(); 

 

    // Load products with their category (avoids N+1 queries). 

    $productsWithCategory = Product::with('category')->get(); 

 

    // Get the product id and name, ordered by price ascending. 

    $productsOrderedByPrice = Product::select('id', 'name', 'price') 

        ->orderBy('price', 'asc') 

        ->get(); 

 

    // Get the first 5 rows from the products table. 

    $firstFiveProducts = Product::limit(5)->get(); 

 

 

 

    // ========================================================= 

    // B. FILTERING DATA 

    // ========================================================= 

 

    // Get products with a price more than or equal to 3. 

    $priceGreaterThanOrEqual = Product::where('price', '>=', 3)->get(); 

 

    // Get products in the 'Electronics' category. 

    $electronics = Product::whereHas('category', function ($q) { 

        $q->where('name', 'Electronics'); 

    })->get(); 

 

 

    // ========================================================= 

    // C. AGGREGATING DATA 

    // ========================================================= 

 

    // Get the total price across all products. 

    $totalPrice = Product::sum('price'); 

 

    // Get the average price per product. 

    $averagePrice = round(Product::avg('price'), 2); 

 

    // Get the product with the highest price. 

    $highestPrice = Product::max('price'); 

 

    // Get the product with the lowest price. 

    $lowestPrice = Product::min('price'); 

 

 

    // ========================================================= 

    // D. GROUPING, FILTERING, AND SORTING 

    // ========================================================= 

 

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

 

    // Get the product with the lowest price per category. 

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

 

    // Get the average price for 'Electronics' and 'Books' categories. 

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
            'nameAndPrice' => $nameAndPrice,
            'productsWithCategory' => $productsWithCategory,
            'productsOrderedByPrice' => $productsOrderedByPrice,
            'firstFiveProducts' => $firstFiveProducts,
            'priceGreaterThanOrEqual' => $priceGreaterThanOrEqual,
            'electronics' => $electronics,
            'totalPrice' => $totalPrice,
            'averagePrice' => $averagePrice,
            'highestPrice' => $highestPrice,
            'lowestPrice' => $lowestPrice,
            'totalPricePerCategory' => $totalPricePerCategory,
            'averagePricePerCategory' => $averagePricePerCategory,
            'lowestPricePerCategory' => $lowestPricePerCategory,
            'averagePriceSorted' => $averagePriceSorted,
            'electronicsAndBooks' => $electronicsAndBooks
        ];

         return response()->json([
            'message' => 'Query results retrieved successfully', 
            'data' => $queryResults]);
    }



 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category') ->paginate(10); 
 
        return response()->json ([
            'message' => 'Products retrieved successfully',
            'data' =>  ProductResource::collection($products)]);
        // $products = Product::all(); 
 
        // $result = []; 
 
        // foreach ($products as $product) { 
        //     $result[] = [ 
        //         'id' => $product->id, 
        //         'name' => $this->formatProductName($product->name), 
        //         'slug' => $product->slug, 
        //         'category' => $product->category, 
        //         'price' => $product->price, 
        //         'quantity' => $product->quantity, 
        //         'status' => $product->status, 
        //     ]; 
        // } 
 
        // return response()->json([ 
        //     'status' => true, 
        //     'message' => 'Products retrieved successfully.', 
        //     'data' => $result 
        // ], 200); s
    }

    // public function show(string $slug)
    // {
    //      $product = Product::where('slug', $slug)->first(); 
 
    //     if (!$product) { 
    //         return response()->json([ 
    //          'status' => false, 
    //             'message' => 'Product not found.' 
    //         ], 404);
    //     } 
 
    //     return response()->json([ 
    //         'status' => true, 
    //         'message' => 'Product retrieved successfully.', 
    //         'data' => [ 
    //             'id' => $product->id, 
    //             'name' => $this->formatProductName($product->name), 
    //             'slug' => $product->slug, 
    //             'category' => $product->category, 
    //             'price' => $product->price, 
    //             'quantity' => $product->quantity, 
    //             'status' => $product->status, 
    //         ] 
    //     ], 200); 
    // } 

    public function show(Product $product)
    {
        $product->load('category'); 
        return response()->json([
            'message' => 'Product retrieved successfully', 
            'data' => new ProductResource($product)]);
    }
    
    public function showtwo(string $category)
    {
         $product = Product::where('category', $category)->first(); 
 
        if (!$product) { 
            return response()->json([ 
             'status' => false, 
                'message' => 'Product not found.' 
            ], 404);
        } 
 
        return response()->json([ 
            'status' => true, 
            'message' => 'Product retrieved successfully.', 
            'data' => [ 
                'id' => $product->id, 
                'name' => $this->formatProductName($product->name), 
                'slug' => $product->slug, 
                'category' => $product->category, 
                'price' => $product->price, 
                'quantity' => $product->quantity, 
                'status' => $product->status, 
            ] 
        ], 200); 

    } 
 
 
    // CREATE 
    // USER-DEFINED FUNCTION 
    private function formatProductName($name) 
    { 
        return ucwords( 
            strtolower( 
                trim($name) 
            ) 
        ); 
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}