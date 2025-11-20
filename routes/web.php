<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LoginController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'categories'], function () {
    Route::post('/add_category', [CategoryController::class, 'add_category']);
    Route::put('/{id}', [CategoryController::class, 'edit_category']);
    Route::delete('/{id}', [CategoryController::class, 'delete_category']);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get("/category", [DashboardController::class, 'category']);

Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'list_categories']);
    Route::post('/add_category', [CategoryController::class, 'add_category']);
    Route::put('/{id}', [CategoryController::class, 'edit_category']);
    Route::delete('/{id}', [CategoryController::class, 'delete_category']);
// Authentication (web session)
Route::get('/login', [LoginController::class, 'show'])->name('login.form');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.perform');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout.perform');

Route::middleware(['web_auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UsersController::class, 'manage'])->name('users.manage');
});
