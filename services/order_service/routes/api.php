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

//invoice
Route::post('/invoice', [App\Http\Controllers\Invoice\InvoiceController::class, 'create']);
Route::get('/invoice', [App\Http\Controllers\Invoice\InvoiceController::class, 'getAllInvoices']);
Route::get('/invoice/byid/{id}', [App\Http\Controllers\Invoice\InvoiceController::class, 'getById']);
Route::get('/invoice/delivery/{id}', [App\Http\Controllers\Invoice\InvoiceController::class, 'delivered']);
Route::put('/invoice/changestatus/{id}', [App\Http\Controllers\Invoice\InvoiceController::class, 'update']);
Route::put('/invoice/{id}', [App\Http\Controllers\Invoice\InvoiceController::class, 'delete']);

//productinfo
Route::post('/invoice/invoiceitems', [App\Http\Controllers\Invoice\InvoiceItemsController::class, 'create']);
Route::get('/invoice/invoiceitems/byid/{id}', [App\Http\Controllers\Invoice\InvoiceItemsController::class, 'getById']);
Route::put('/invoice/invoiceitems/{id}', [App\Http\Controllers\Invoice\InvoiceItemsController::class, 'update']);
Route::delete('/invoice/invoiceitems/{id}', [App\Http\Controllers\Invoice\InvoiceItemsController::class, 'delete']);

//cart related
Route::put('/invoice/productinfo/cart/{id}', [App\Http\Controllers\Invoice\InvoiceItemsController::class, 'quantityUpdate']);

//Route::group(['middleware' => 'auth:sanctum'], function () {
//});
