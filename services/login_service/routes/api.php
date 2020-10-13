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

Route::post('/login/token', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/register/seller', [App\Http\Controllers\Auth\AuthController::class, 'registerSeller']);
Route::post('/register/buyer', [App\Http\Controllers\Auth\AuthController::class, 'registerBuyer']);


Route::group(['middleware' => 'auth:sanctum'], function () {

    //regular
    Route::get('/user', [App\Http\Controllers\Auth\AuthController::class, 'getUserData']);
    Route::get('/roles', [App\Http\Controllers\Auth\RolesController::class, 'getRoles']);
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
    Route::post('/register/verify', [App\Http\Controllers\Auth\AuthController::class, 'verifySeller']);
    Route::get('/register/verify', [App\Http\Controllers\Auth\AuthController::class, 'getVerifySellerStatus']);

    //admin user management
    Route::post('/usermgt/create', [App\Http\Controllers\Admin\AdminController::class, 'adminAssistCreate']);
    Route::get('/usermgt/users', [App\Http\Controllers\Admin\AdminController::class, 'getUsers']);
    Route::get('/usermgt/users/byid/{id}', [App\Http\Controllers\Admin\AdminController::class, 'getUserById']);
    Route::put('/usermgt/update/{id}', [App\Http\Controllers\Admin\AdminController::class, 'adminAssistUpdate']);
    Route::delete('/usermgt/delete/{id}', [App\Http\Controllers\Admin\AdminController::class, 'delete']);

    //registration step 2 check
    Route::get('/usermgt/verify', [App\Http\Controllers\Admin\AdminController::class, 'pendingVerifications']);
    Route::put('/usermgt/verify/{id}', [App\Http\Controllers\Admin\AdminController::class, 'verifySellerByAdmin']);


    //role related
    Route::get('/roles', [App\Http\Controllers\Auth\RolesController::class, 'getRoles']);
    Route::get('/roles/byid/{id}', [App\Http\Controllers\Auth\RolesController::class, 'getRoleById']);
    Route::get('/roles/byname/{name}', [App\Http\Controllers\Auth\RolesController::class, 'getByName']);
    Route::post('/roles', [App\Http\Controllers\Auth\RolesController::class, 'create']);
    Route::put('/roles/{id}', [App\Http\Controllers\Auth\RolesController::class, 'update']);
    Route::delete('/roles/{id}', [App\Http\Controllers\Auth\RolesController::class, 'delete']);
});
