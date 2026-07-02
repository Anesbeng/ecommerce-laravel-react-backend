<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Validator;


class CategoryController extends Controller
{
    public function index() {
        $categories = Category::orderBy('created_at','DESC')->get();
        return response()->json([
            'status' => 200,
            'data' => $categories,
        ]);
    
    }

    public function store( Request $request) {
        
     
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);
            
        if ($validator->fails()) {
            return response()->json([
                'status' => '400',    
                'errors' => $validator->errors()], 400);
        }
        $category = Category::create($request->all());
        return response()->json([
            'status' => 200,
            'message' => 'Category are created successfully',
            'data' => $category,
        ]);
        
    }

    public function show( $id) {
        $category = Category::find($id);
        if ($category == null) {  
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
        return response()->json([
            'status' => 200,
            'data' => $category,
        ]);

    }
    public function update( Request $request, $id) {
                $category = Category::find($id);
        if ($category == null) {  
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
            
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
            
        if ($validator->fails()) {
            return response()->json([
                'status' => '400',    
                'errors' => $validator->errors()], 400);
        }
        $category->update($request->all());
        return response()->json([
            'status' => 200,
            'data' => $category,
            'message' => 'Category are updated successfully',
        ]);

    }
    public function destroy( $id) {
        $category = Category::find($id);
        if ($category == null) {  
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
        $category->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Category are deleted successfully',
        ]);

    }

}