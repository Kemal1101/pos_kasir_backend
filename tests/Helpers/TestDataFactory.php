<?php

namespace Tests\Helpers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAddition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Test Data Factory
 * 
 * Provides convenient methods to create test data
 */
class TestDataFactory
{
    /**
     * Create multiple users with specific role
     */
    public static function createUsers($count, $roleName = 'Kasir')
    {
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $role = Role::create([
                'name' => $roleName,
                'description' => "{$roleName} role",
            ]);
        }

        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = User::create([
                'role_id' => $role->role_id,
                'uuid' => Str::uuid(),
                'username' => "{$roleName}_user_{$i}",
                'name' => "{$roleName} User {$i}",
                'email' => strtolower("{$roleName}_user_{$i}@test.com"),
                'password' => bcrypt('password'),
            ]);
        }

        return $users;
    }

    /**
     * Create multiple products in a category
     */
    public static function createProducts($count, $categoryId = null)
    {
        if (!$categoryId) {
            $category = Category::create([
                'name' => 'Test Category',
                'description' => 'Category for testing',
            ]);
            $categoryId = $category->categories_id;
        }

        $products = [];
        $productNames = [
            'Coca Cola', 'Pepsi', 'Sprite', 'Fanta', 'Aqua',
            'Chitato', 'Lays', 'Pringles', 'Indomie', 'Mie Sedaap',
        ];

        for ($i = 0; $i < $count; $i++) {
            $name = $productNames[$i % count($productNames)] . " {$i}";
            $products[] = Product::create([
                'categories_id' => $categoryId,
                'name' => $name,
                'description' => "Description for {$name}",
                'cost_price' => rand(1000, 10000),
                'selling_price' => rand(2000, 20000),
                'stock' => rand(10, 100),
                'barcode' => 'BARCODE' . str_pad($i, 6, '0', STR_PAD_LEFT),
            ]);
        }

        return $products;
    }

    /**
     * Create sales with random data
     */
    public static function createRandomSales($count, $userId = null, $startDate = null, $endDate = null)
    {
        if (!$userId) {
            $users = self::createUsers(1, 'Kasir');
            $userId = $users[0]->user_id;
        }

        if (!$startDate) {
            $startDate = Carbon::today()->subDays(30);
        }

        if (!$endDate) {
            $endDate = Carbon::today();
        }

        $products = self::createProducts(10);
        $sales = [];

        for ($i = 0; $i < $count; $i++) {
            $saleDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            $payment = Payment::create([
                'payment_method' => ['cash', 'qris', 'debit'][rand(0, 2)],
                'order_id' => 'ORDER-' . time() . '-' . rand(1000, 9999),
                'gross_amount' => 0,
                'transaction_status' => 'settlement',
            ]);

            $subtotal = 0;
            $itemCount = rand(1, 5);
            $saleItems = [];

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products[rand(0, count($products) - 1)];
                $quantity = rand(1, 5);
                $itemSubtotal = $product->selling_price * $quantity;
                $subtotal += $itemSubtotal;

                $saleItems[] = [
                    'product_id' => $product->product_id,
                    'name_product' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discountAmount = rand(0, 1) ? rand(0, $subtotal * 0.1) : 0;
            $taxAmount = $subtotal * 0.1; // 10% tax
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $sale = Sale::create([
                'user_id' => $userId,
                'payment_id' => $payment->payment_id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'sale_date' => $saleDate,
            ]);

            foreach ($saleItems as $saleItem) {
                SaleItem::create(array_merge($saleItem, [
                    'sale_id' => $sale->sale_id,
                ]));
            }

            $payment->update(['gross_amount' => $totalAmount]);
            $sales[] = $sale;
        }

        return $sales;
    }

    /**
     * Create stock additions
     */
    public static function createStockAdditions($productId, $count, $userId = null)
    {
        if (!$userId) {
            $users = self::createUsers(1, 'Gudang');
            $userId = $users[0]->user_id;
        }

        $additions = [];
        for ($i = 0; $i < $count; $i++) {
            $quantity = rand(10, 50);
            
            $additions[] = StockAddition::create([
                'product_id' => $productId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'notes' => "Stock addition batch " . ($i + 1),
            ]);

            // Update product stock
            $product = Product::find($productId);
            $product->increment('stock', $quantity);
        }

        return $additions;
    }

    /**
     * Generate JWT token for user
     */
    public static function generateTokenForUser($userId)
    {
        $user = User::find($userId);
        return JWTAuth::fromUser($user);
    }

    /**
     * Create complete test environment
     */
    public static function createCompleteTestEnvironment()
    {
        // Create roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrator']
        );

        $kasirRole = Role::firstOrCreate(
            ['name' => 'Kasir'],
            ['description' => 'Cashier']
        );

        $gudangRole = Role::firstOrCreate(
            ['name' => 'Gudang'],
            ['description' => 'Warehouse']
        );

        // Create users
        $admin = User::create([
            'role_id' => $adminRole->role_id,
            'uuid' => Str::uuid(),
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $kasir = User::create([
            'role_id' => $kasirRole->role_id,
            'uuid' => Str::uuid(),
            'username' => 'kasir',
            'name' => 'Kasir User',
            'email' => 'kasir@test.com',
            'password' => bcrypt('password'),
        ]);

        $gudang = User::create([
            'role_id' => $gudangRole->role_id,
            'uuid' => Str::uuid(),
            'username' => 'gudang',
            'name' => 'Gudang User',
            'email' => 'gudang@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create categories
        $categories = [
            Category::create(['name' => 'Minuman', 'description' => 'Kategori minuman']),
            Category::create(['name' => 'Makanan', 'description' => 'Kategori makanan']),
            Category::create(['name' => 'Snack', 'description' => 'Kategori snack']),
        ];

        // Create products
        $products = self::createProducts(20, $categories[0]->categories_id);

        return [
            'roles' => compact('adminRole', 'kasirRole', 'gudangRole'),
            'users' => compact('admin', 'kasir', 'gudang'),
            'categories' => $categories,
            'products' => $products,
        ];
    }
}
