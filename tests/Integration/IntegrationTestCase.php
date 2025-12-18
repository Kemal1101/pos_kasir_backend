<?php

namespace Tests\Integration;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Base Integration Test Class
 * 
 * Provides common setup and helper methods for all integration tests
 */
abstract class IntegrationTestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected $adminRole;
    protected $kasirRole;
    protected $gudangRole;
    
    protected $adminUser;
    protected $kasirUser;
    protected $gudangUser;
    
    protected $adminToken;
    protected $kasirToken;
    protected $gudangToken;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRoles();
        $this->createUsers();
        $this->generateTokens();
    }

    /**
     * Create standard roles
     */
    protected function createRoles(): void
    {
        $this->adminRole = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator with full access',
        ]);

        $this->kasirRole = Role::create([
            'name' => 'Kasir',
            'description' => 'Cashier for sales operations',
        ]);

        $this->gudangRole = Role::create([
            'name' => 'Gudang',
            'description' => 'Warehouse staff for inventory management',
        ]);
    }

    /**
     * Create standard test users
     */
    protected function createUsers(): void
    {
        $this->adminUser = User::create([
            'role_id' => $this->adminRole->role_id,
            'uuid' => $this->faker()->uuid,
            'username' => 'admin_test',
            'name' => 'Admin Test User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $this->kasirUser = User::create([
            'role_id' => $this->kasirRole->role_id,
            'uuid' => $this->faker()->uuid,
            'username' => 'kasir_test',
            'name' => 'Kasir Test User',
            'email' => 'kasir@test.com',
            'password' => bcrypt('password123'),
        ]);

        $this->gudangUser = User::create([
            'role_id' => $this->gudangRole->role_id,
            'uuid' => $this->faker()->uuid,
            'username' => 'gudang_test',
            'name' => 'Gudang Test User',
            'email' => 'gudang@test.com',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Generate JWT tokens for test users
     */
    protected function generateTokens(): void
    {
        $this->adminToken = JWTAuth::fromUser($this->adminUser);
        $this->kasirToken = JWTAuth::fromUser($this->kasirUser);
        $this->gudangToken = JWTAuth::fromUser($this->gudangUser);
    }

    /**
     * Make authenticated request as admin
     */
    protected function asAdmin()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Make authenticated request as kasir
     */
    protected function asKasir()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Make authenticated request as gudang
     */
    protected function asGudang()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->gudangToken,
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Make authenticated request as specific user
     */
    protected function asUser(User $user)
    {
        $token = JWTAuth::fromUser($user);
        
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Assert response has success meta structure
     */
    protected function assertSuccessResponse($response, $message = null)
    {
        $response->assertJsonStructure([
            'meta' => ['status', 'message'],
            'data',
        ]);

        $response->assertJson([
            'meta' => [
                'status' => 'success',
            ],
        ]);

        if ($message) {
            $response->assertJson([
                'meta' => [
                    'message' => $message,
                ],
            ]);
        }
    }

    /**
     * Assert response has error meta structure
     */
    protected function assertErrorResponse($response, $message = null)
    {
        $response->assertJsonStructure([
            'meta' => ['status', 'message'],
        ]);

        $response->assertJson([
            'meta' => [
                'status' => 'error',
            ],
        ]);

        if ($message) {
            $response->assertJson([
                'meta' => [
                    'message' => $message,
                ],
            ]);
        }
    }

    /**
     * Get faker instance
     */
    protected function faker()
    {
        return \Faker\Factory::create('id_ID');
    }

    /**
     * Create a test category
     */
    protected function createCategory($name = null, $description = null)
    {
        return \App\Models\Category::create([
            'name' => $name ?? $this->faker()->word,
            'description' => $description ?? $this->faker()->sentence,
        ]);
    }

    /**
     * Create a test product
     */
    protected function createProduct($categoryId = null, $attributes = [])
    {
        if (!$categoryId) {
            $category = $this->createCategory();
            $categoryId = $category->categories_id;
        }

        $defaults = [
            'categories_id' => $categoryId,
            'name' => $this->faker()->words(3, true),
            'description' => $this->faker()->sentence,
            'cost_price' => $this->faker()->numberBetween(1000, 50000),
            'selling_price' => $this->faker()->numberBetween(2000, 100000),
            'stock' => $this->faker()->numberBetween(10, 100),
            'barcode' => $this->faker()->ean13,
        ];

        return \App\Models\Product::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a test sale
     */
    protected function createSale($userId = null, $attributes = [])
    {
        if (!$userId) {
            $userId = $this->kasirUser->user_id;
        }

        $payment = $this->createPayment();

        $defaults = [
            'user_id' => $userId,
            'payment_id' => $payment->payment_id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'pending',
            'sale_date' => now(),
        ];

        return \App\Models\Sale::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a test payment
     */
    protected function createPayment($attributes = [])
    {
        $defaults = [
            'payment_method' => 'cash',
            'order_id' => 'ORDER-TEST-' . time() . '-' . rand(1000, 9999),
            'gross_amount' => 0,
            'transaction_status' => 'pending',
        ];

        return \App\Models\Payment::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a test sale item
     */
    protected function createSaleItem($saleId, $productId, $quantity = 1, $attributes = [])
    {
        $product = \App\Models\Product::find($productId);

        $defaults = [
            'sale_id' => $saleId,
            'product_id' => $productId,
            'name_product' => $product->name,
            'quantity' => $quantity,
            'price' => $product->selling_price,
            'cost_price' => $product->cost_price,
            'subtotal' => $product->selling_price * $quantity,
        ];

        return \App\Models\SaleItem::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a completed sale with items
     */
    protected function createCompletedSaleWithItems($items = [], $userId = null)
    {
        $sale = $this->createSale($userId);
        $subtotal = 0;

        foreach ($items as $item) {
            $product = $this->createProduct(null, [
                'selling_price' => $item['price'] ?? 10000,
                'cost_price' => $item['cost'] ?? 5000,
                'stock' => $item['stock'] ?? 100,
            ]);

            $quantity = $item['quantity'] ?? 1;
            $this->createSaleItem($sale->sale_id, $product->product_id, $quantity);
            
            $subtotal += $product->selling_price * $quantity;
            $product->decrement('stock', $quantity);
        }

        $sale->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
            'payment_status' => 'paid',
        ]);

        $sale->payment->update([
            'gross_amount' => $subtotal,
            'transaction_status' => 'settlement',
        ]);

        return $sale->fresh();
    }

    /**
     * Assert database has sale with items
     */
    protected function assertSaleExists($saleId, $expectedItemCount = null)
    {
        $this->assertDatabaseHas('sales', [
            'sale_id' => $saleId,
        ]);

        if ($expectedItemCount !== null) {
            $itemCount = \App\Models\SaleItem::where('sale_id', $saleId)->count();
            $this->assertEquals($expectedItemCount, $itemCount);
        }
    }

    /**
     * Assert product stock equals expected value
     */
    protected function assertProductStock($productId, $expectedStock)
    {
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock' => $expectedStock,
        ]);
    }

    /**
     * Dump response for debugging
     */
    protected function dumpResponse($response)
    {
        dump([
            'status' => $response->status(),
            'headers' => $response->headers->all(),
            'content' => $response->json() ?? $response->content(),
        ]);
    }
}
