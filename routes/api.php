<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt.verify'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Category management routes (protected)
Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'list_categories']);
    Route::post('/add_category', [CategoryController::class, 'add_category']);
    Route::put('/{id}', [CategoryController::class, 'edit_category']);
    Route::delete('/{id}', [CategoryController::class, 'delete_category']);
});

// Users management routes
Route::prefix('users')
    ->middleware(['jwt.cookie','jwt.verify', 'role:Admin'])
    ->group(function () {
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

// Sales management routes
Route::group(['prefix' => 'sales'], function () {
    // Create a new draft sale
    Route::post('/', [SaleController::class, 'create_sale']);

    // Add/remove items to/from a sale
    Route::post('/items', [SaleController::class, 'add_item']);
    Route::delete('/items/{saleItemId}', [SaleController::class, 'remove_item']);

    // Fetch a sale with its items
    Route::get('/{saleId}', [SaleController::class, 'get_sale']);
});
