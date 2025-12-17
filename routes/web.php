<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\StockAdditionController;

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
    return view('landingPage.index');
});

Route::get('/login', [AuthController::class, 'show'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

Route::group(['prefix' => 'categories'], function () {
    Route::post('/add_category', [CategoryController::class, 'add_category']);
    Route::put('/{id}', [CategoryController::class, 'edit_category']);
    Route::delete('/{id}', [CategoryController::class, 'delete_category']);
});

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // moved under JWT-protected group


Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'list_categories']);
    Route::post('/add_category', [CategoryController::class, 'add_category']);
    Route::put('/{id}', [CategoryController::class, 'edit_category']);
    Route::delete('/{id}', [CategoryController::class, 'delete_category']);
});

// Authentication (web session)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes untuk role_id = 1 (Admin/Owner) dan role_id = 2 (Gudang)
Route::middleware(['jwt.cookie', 'role:Admin,Gudang'])->group(function () {
    Route::get('/users', [UsersController::class, 'manage'])->name('users.manage');
    Route::get("/category", [DashboardController::class, 'category']);
    Route::get('/products', [ProductController::class, 'index'])->name('products.manage');
    Route::get('/laporan-penjualan', [SalesReportController::class, 'index'])->name('sales.report');
    Route::get('/laporan-penjualan/export-pdf', [SalesReportController::class, 'exportPdf'])->name('sales.report.pdf');
});

Route::middleware(['jwt.cookie', 'role:Admin,Gudang'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Routes untuk role_id = 3 (Kasir) dan role_id = 1 (Admin)
Route::middleware(['jwt.cookie', 'role:Gudang'])->group(function () {
    Route::get('/stock-additions', [StockAdditionController::class, 'index'])->name('stock-additions.index');
    Route::get('/stock-additions/create', [StockAdditionController::class, 'create'])->name('stock-additions.create');
    Route::post('/stock-additions', [StockAdditionController::class, 'store'])->name('stock-additions.store');
    Route::delete('/stock-additions/{id}', [StockAdditionController::class, 'destroy'])->name('stock-additions.destroy');
});
