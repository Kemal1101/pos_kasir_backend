<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
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
    ->middleware(['jwt.verify', 'role:Admin'])
    ->group(function () {
        Route::get('/', [UsersController::class, 'list_user']);
        Route::post('/add_user', [UsersController::class, 'add_user']);
        Route::put('/{id}', [UsersController::class, 'edit_user']);
        Route::delete('/{id}', [UsersController::class, 'delete_user']);
    });
