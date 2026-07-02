<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brands;




class FproductController extends Controller
{
public function getproducts(Request $request)
{
    $products = Product::orderBy('created_at', 'DESC'); 

    if (!empty($request->category)) {
        $catArray = explode(',', $request->category);
        $products = $products->whereIn('category_id', $catArray);
    }

    if (!empty($request->brands)) {
        $brandArray = explode(',', $request->brands);
        $products = $products->whereIn('brand_id', $brandArray);
    }

    $products = $products->get();  

    return response()->json([
        'data' => $products,
        'message' => 'Products retrieved successfully',
        'status' => 200
    ], 200);
}   
public function getproduct($id)
{
    $product = Product::with('sizes', 'images')->find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found', 'status' => 404], 404);
    }
    return response()->json(['data' => $product, 'message' => 'Product retrieved successfully', 'status' => 200], 200);
} 

public function getcategories()
    { 
        $categories = Category::orderBy('created_at', 'DESC')->get();
        return response()->json(['data' => $categories, 'message' => 'Categories retrieved successfully', 'status' => 200], 200);
       
    }
    public function getBrands()
    { 
        $brands = Brands::orderBy('created_at', 'DESC')->get();
        return response()->json(['data' => $brands, 'message' => 'Brands retrieved successfully', 'status' => 200], 200);
       
    }
    public function LastestProducts()
    {
        $products = Product::orderBy('created_at', 'DESC')->take(8)->get();
        return response()->json(['data' => $products, 'message' => 'Latest products retrieved successfully', 'status' => 200], 200);
    }

        public function FeaturedProducts()
    {
        $products = Product::orderBy('created_at', 'DESC')->where('is_featured', '1')->take(8)->get();
        return response()->json(['data' => $products, 'message' => 'Featured products retrieved successfully', 'status' => 200], 200);
    }
}