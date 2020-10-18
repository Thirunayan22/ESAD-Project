<?php

use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

//category

Route::get('/inventory/category', [App\Http\Controllers\Inventory\ProductsCategoryController::class, 'getAllCategories']);
Route::get('/inventory/category/nested/{id}', [App\Http\Controllers\Inventory\ProductsCategoryController::class, 'getAllNestedCategories']);
Route::get('/inventory/category/withproducts/{id}', [App\Http\Controllers\Inventory\ProductsCategoryController::class, 'getAllWithProducts']);
Route::post('/inventory/category', [App\Http\Controllers\Inventory\ProductsCategoryController::class, 'create']);
Route::put('/inventory/category/{id}', [App\Http\Controllers\Inventory\ProductsCategoryController::class, 'update']);
Route::delete('/inventory/category/{id}', [App\Http\Controllers\Inventory\ProductsCategoryController::class, 'delete']);

//product
Route::get('/inventory/products', [App\Http\Controllers\Inventory\ProductsController::class, 'getAllProducts']);
Route::get('/inventory/products/byid/{id}', [App\Http\Controllers\Inventory\ProductsController::class, 'getById']);
Route::get('/inventory/products/byname/{name}', [App\Http\Controllers\Inventory\ProductsController::class, 'getByName']);
Route::post('/inventory/products', [App\Http\Controllers\Inventory\ProductsController::class, 'create']);
Route::put('/inventory/products/{id}', [App\Http\Controllers\Inventory\ProductsController::class, 'update']);
Route::delete('/inventory/products/{id}', [App\Http\Controllers\Inventory\ProductsController::class, 'delete']);

//productinfo
Route::post('/inventory/productinfo', [App\Http\Controllers\Inventory\ProductInfoController::class, 'create']);
Route::get('/inventory/productinfo/byid/{id}', [App\Http\Controllers\Inventory\ProductInfoController::class, 'getById']);
Route::put('/inventory/productinfo/{id}', [App\Http\Controllers\Inventory\ProductInfoController::class, 'update']);
Route::delete('/inventory/productinfo/{id}', [App\Http\Controllers\Inventory\ProductInfoController::class, 'delete']);

//Route::group(['middleware' => 'auth:sanctum'], function () {
//});
