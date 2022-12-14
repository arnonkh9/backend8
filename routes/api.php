<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductsTypeController;
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

// Pulic routes
Route::post('register',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => 'auth:sanctum'], function(){

    Route::resource('user/roles', AuthController::class);
    Route::get('user/roles/search/{keyword}', [AuthController::class, 'search']);

    Route::resource('products', ProductController::class);
    Route::get('products/search/{keyword}', [ProductController::class, 'search']);

    Route::resource('units', UnitController::class);
    Route::get('units/search/{keyword}', [UnitController::class, 'search']);

    Route::resource('product/types', ProductsTypeController::class);
    Route::get('product/types/search/{keyword}', [ProductsTypeController::class, 'search']);

    Route::post('logout',[AuthController::class, 'logout']);
});


// Route::middleware('auth:api')->get('/user', function (Request $request) {
    //     return $request->user();
    // });
