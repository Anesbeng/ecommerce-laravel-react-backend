<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brands;
use Validator;

class BrandsController extends Controller
{
        public function index() {
        $Brands = Brands::orderBy('created_at','DESC')->get();
        return response()->json([
            'status' => 200,
            'data' => $Brands,
        ]);
    
    }

    public function store( Request $request) {
        
     
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
            
        if ($validator->fails()) {
            return response()->json([
                'status' => '400',    
                'errors' => $validator->errors()], 400);
        }
        $Brands = Brands::create($request->all());
        return response()->json([
            'status' => 200,
            'data' => $Brands,
            'message' => 'Brands are created successfully',
        ]);
        
    }

    public function show( $id) {
        $Brands = Brands::find($id);
        if ($Brands == null) {  
            return response()->json([
                'status' => 404,
                'message' => 'brands not found',
            ]);
        }
        return response()->json([
            'status' => 200,
            'data' => $Brands,
        ]);

    }
    public function update( Request $request, $id) {
                $Brands = Brands::find($id);
        if ($Brands == null) {  
            return response()->json([
                'status' => 404,
                'message' => 'brands not found',
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
        $Brands->update($request->all());
        return response()->json([
            'status' => 200,
            'data' => $Brands,
            'message' => 'Brands are updated successfully',
        ]);

    }
    public function destroy( $id) {
        $Brands = Brands::find($id);
        if ($Brands == null) {  
            return response()->json([
                'status' => 404,
                'message' => 'brands not found',
            ]);
        }
        $Brands->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Brands are deleted successfully',
        ]);

    }
}