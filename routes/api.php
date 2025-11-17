<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Category management routes (protected)
Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'list_categories']);
    Route::post('/add_category', [CategoryController::class, 'add_category']);
    Route::put('/{id}', [CategoryController::class, 'edit_category']);
    Route::delete('/{id}', [CategoryController::class, 'delete_category']);
});

// Users management routes
Route::group(['prefix' => 'users'], function () {
    Route::get('/', [UsersController::class, 'list_user']);
    Route::post('/add_user', [UsersController::class, 'add_user']);
    Route::put('/{id}', [UsersController::class, 'edit_user']);
    Route::delete('/{id}', [UsersController::class, 'delete_user']);
});

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'list_products']);
    Route::post('/add_product', [ProductController::class, 'add_product']);
    Route::post('/upload_image', [ProductController::class, 'upload_product_image']); // jika dipisah
    Route::put('/{id}', [ProductController::class, 'edit_product']);
    Route::delete('/{id}', [ProductController::class, 'delete_product']);
});