<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\Authcontroller;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\BrandsController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\front\FproductController;
use App\Http\Controllers\front\FauthController;
use App\Http\Controllers\front\SaveOrderController;
use App\Http\Controllers\front\OrderController;
use App\Http\Controllers\admin\OrderController as AdminOrderController;
use App\Http\Controllers\front\ProfileController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\UserController;








Route::post('/admin/login', [Authcontroller::class, 'authenticate']);

Route::get('latest-products', [FproductController::class, 'LastestProducts']);
Route::get('featured-products', [FproductController::class, 'FeaturedProducts']);
Route::get('categories-products', [FproductController::class, 'getcategories']);
Route::get('brands-products', [FproductController::class, 'getBrands']);
Route::get('get-products', [FproductController::class, 'getproducts']);

Route::get('get-product/{id}', [FproductController::class, 'getproduct']);
Route::post('register', [FauthController::class, 'register']);
Route::post('login', [FauthController::class, 'authenticate']);


Route::group(['middleware' => ['auth:sanctum','checkuser']], function () {
Route::post('save-order', [SaveOrderController::class, 'saveOrder']);
Route::get('orders', [OrderController::class, 'index']);
Route::get('profile', [ProfileController::class, 'show']);
Route::put('profile', [ProfileController::class, 'update']);
Route::post('change-password', [ProfileController::class, 'changePassword']);
});


/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/
Route::group(['middleware' => ['auth:sanctum','checkadmin']], function () {
    /*
     Route::get('categories', [CategoryController::class, 'index']);
     Route::post('categories', [CategoryController::class, 'store']);
     Route::get('categories/{id}', [CategoryController::class, 'show']);
     Route::put('categories/{id}', [CategoryController::class, 'update']);
     Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
     */
Route::post('admin/change-password', [Authcontroller::class, 'changePassword']);
    Route::resource('categories', CategoryController::class); 
    Route::resource('brands', BrandsController::class); 
    Route::get('sizes', [SizeController::class, 'index']);
    Route::post('tempimages', [TempImageController::class, 'store']);
    Route::resource('Products', ProductController::class); 
    
    Route::get('admin/orders', [AdminOrderController::class, 'index']);
    Route::get('admin/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::get('admin/users',           [UserController::class, 'index']);
Route::get('admin/users/{id}',      [UserController::class, 'show']);
Route::put('admin/users/{id}',      [UserController::class, 'update']);
Route::delete('admin/users/{id}',   [UserController::class, 'destroy']);

Route::get('admin/shipping',        [ShippingController::class, 'index']);
Route::put('admin/shipping',        [ShippingController::class, 'update']);
     
    
})
;