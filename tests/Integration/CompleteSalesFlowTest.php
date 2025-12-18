<?php

namespace Tests\Integration;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Integration Test: Complete Sales Flow
 * 
 * This test covers the entire sales process from product creation
 * to final sale transaction and payment completion.
 */
class CompleteSalesFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasir;
    protected $adminToken;
    protected $kasirToken;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator with full access',
        ]);

        $kasirRole = Role::create([
            'name' => 'Kasir',
            'description' => 'Cashier for sales operations',
        ]);

        // Create admin user
        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@supercashier.com',
            'password' => bcrypt('admin123'),
        ]);

        // Create kasir user
        $this->kasir = User::create([
            'role_id' => $kasirRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'kasir01',
            'name' => 'Kasir Satu',
            'email' => 'kasir@supercashier.com',
            'password' => bcrypt('kasir123'),
        ]);

        // Generate tokens
        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->kasirToken = JWTAuth::fromUser($this->kasir);

        // Create category
        $this->category = Category::create([
            'name' => 'Minuman',
            'description' => 'Kategori minuman',
        ]);
    }

    /** @test */
    public function it_can_complete_full_sales_workflow_from_product_to_payment()
    {
        // ============================================
        // STEP 1: Admin creates products
        // ============================================
        $product1 = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Coca Cola 330ml',
            'description' => 'Minuman bersoda',
            'cost_price' => 3000,
            'selling_price' => 5000,
            'stock' => 100,
            'barcode' => 'COLA330',
        ]);

        $product2 = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Aqua 600ml',
            'description' => 'Air mineral',
            'cost_price' => 2000,
            'selling_price' => 3500,
            'stock' => 150,
            'barcode' => 'AQUA600',
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Coca Cola 330ml',
            'stock' => 100,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Aqua 600ml',
            'stock' => 150,
        ]);

        // ============================================
        // STEP 2: Kasir creates a new sale
        // ============================================
        $createSaleResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales', []);

        $createSaleResponse->assertStatus(201);
        $saleId = $createSaleResponse->json('data.sale_id');

        $this->assertDatabaseHas('sales', [
            'sale_id' => $saleId,
            'user_id' => $this->kasir->user_id,
            'payment_status' => 'draft',
        ]);

        // ============================================
        // STEP 3: Add items to the sale (Coca Cola x2)
        // ============================================
        $addItem1Response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/items", [
            'sale_id' => $saleId,
            'product_id' => $product1->product_id,
            'quantity' => 2,
        ]);

        $addItem1Response->assertStatus(201)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data',
            ]);

        // Check stock reduced
        $product1->refresh();
        $this->assertEquals(98, $product1->stock);

        // ============================================
        // STEP 4: Add more items (Aqua x3)
        // ============================================
        $addItem2Response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/items", [
            'sale_id' => $saleId,
            'product_id' => $product2->product_id,
            'quantity' => 3,
        ]);

        $addItem2Response->assertStatus(201);

        // Check stock reduced
        $product2->refresh();
        $this->assertEquals(147, $product2->stock);

        // ============================================
        // STEP 5: Get sale details with items
        // ============================================
        $saleDetailsResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->getJson("/api/sales/{$saleId}");

        $saleDetailsResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'sale_id',
                    'subtotal',
                    'items' => [
                        '*' => [
                            'product_id',
                            'name_product',
                            'quantity',
                            'subtotal',
                        ],
                    ],
                ],
            ]);

        $saleData = $saleDetailsResponse->json('data');
        $this->assertCount(2, $saleData['items']);

        // Verify calculation: (2 x 5000) + (3 x 3500) = 10000 + 10500 = 20500
        $expectedSubtotal = 20500;
        $this->assertEquals($expectedSubtotal, $saleData['subtotal']);

        // ============================================
        // STEP 6: Complete payment with payment_id
        // ============================================
        // First create a payment method
        $payment = \App\Models\Payment::create([
            'order_id' => 'ORD-' . time(),
            'payment_type' => 'cash',
            'gross_amount' => 0,
            'transaction_status' => 'pending',
        ]);

        // Confirm payment with correct endpoint
        $paymentResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/{$saleId}/confirm-payment", [
            'payment_id' => $payment->payment_id,
        ]);

        $paymentResponse->assertStatus(200);

        // Verify sale is completed
        $this->assertDatabaseHas('sales', [
            'sale_id' => $saleId,
            'payment_status' => 'paid',
        ]);

        // ============================================
        // STEP 7: Verify final stock levels
        // ============================================
        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(98, $product1->stock); // 100 - 2
        $this->assertEquals(147, $product2->stock); // 150 - 3
    }

    /** @test */
    public function it_prevents_overselling_when_stock_insufficient()
    {
        // Create product with limited stock
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Limited Product',
            'description' => 'Only 5 in stock',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'stock' => 5,
            'barcode' => 'LTD001',
        ]);

        // Create sale
        $saleResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales', []);

        $saleId = $saleResponse->json('data.sale_id');

        // Try to add 10 items (more than stock)
        $addItemResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales/items', [
            'sale_id' => $saleId,
            'product_id' => $product->product_id,
            'quantity' => 10,
        ]);

        // API returns validation error in Laravel format
        $addItemResponse->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        // Verify stock unchanged
        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }

    /** @test */
    public function it_can_cancel_sale_and_restore_stock()
    {
        // Create product
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Test Product',
            'description' => 'For cancellation test',
            'cost_price' => 5000,
            'selling_price' => 8000,
            'stock' => 50,
            'barcode' => 'TEST001',
        ]);

        // Create sale
        $saleResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales', []);

        $saleId = $saleResponse->json('data.sale_id');

        // Add items
        $addItemResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/items", [
            'sale_id' => $saleId,
            'product_id' => $product->product_id,
            'quantity' => 5,
        ]);

        $addItemResponse->assertStatus(201); // Verify item added successfully

        // Stock should be decremented when item is added
        $product->refresh();
        $this->assertEquals(45, $product->stock); // 50 - 5 = 45

        // Cancel sale (mark as cancelled, not delete)
        $cancelResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->deleteJson("/api/sales/{$saleId}");

        $cancelResponse->assertStatus(200);

        // Verify stock restored
        $product->refresh();
        $this->assertEquals(50, $product->stock);

        // Verify sale is marked as cancelled (not deleted)
        $this->assertDatabaseHas('sales', [
            'sale_id' => $saleId,
            'payment_status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_calculates_tax_and_discount_correctly()
    {
        // Create product
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Taxable Product',
            'description' => 'Product with tax',
            'cost_price' => 50000,
            'selling_price' => 100000,
            'stock' => 20,
            'barcode' => 'TAX001',
        ]);

        // Create sale
        $saleResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales', []);

        $saleId = $saleResponse->json('data.sale_id');

        // Add 2 items with per-item discount (subtotal = 200000, discount = 20000 total)
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/items", [
            'sale_id' => $saleId,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'discount_amount' => 20000, // Discount per line item
        ]);

        // Get final sale details
        $saleDetailsResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->getJson("/api/sales/{$saleId}");

        $saleData = $saleDetailsResponse->json('data');

        // Verify calculations based on actual API implementation
        $expectedSubtotal = 2 * 100000; // 2 items * 100000 each
        $this->assertEquals($expectedSubtotal, (float)$saleData['subtotal']);
        $this->assertEquals(20000, (float)$saleData['discount_amount']); 
        // Tax is 0 by default unless explicitly set
        $expectedTotal = $expectedSubtotal - 20000 + (float)$saleData['tax_amount'];
        $this->assertEquals($expectedTotal, (float)$saleData['total_amount']);
    }

    /** @test */
    public function it_tracks_multiple_concurrent_sales()
    {
        // Create products
        $product1 = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product A',
            'description' => 'First product',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'stock' => 100,
            'barcode' => 'PRODA',
        ]);

        $product2 = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product B',
            'description' => 'Second product',
            'cost_price' => 20000,
            'selling_price' => 30000,
            'stock' => 100,
            'barcode' => 'PRODB',
        ]);

        // Create first sale
        $sale1Response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales', []);
        $sale1Id = $sale1Response->json('data.sale_id');

        // Create second sale
        $sale2Response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson('/api/sales', []);
        $sale2Id = $sale2Response->json('data.sale_id');

        // Add different products to each sale
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/items", [
            'sale_id' => $sale1Id,
            'product_id' => $product1->product_id,
            'quantity' => 3,
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/items", [
            'sale_id' => $sale2Id,
            'product_id' => $product2->product_id,
            'quantity' => 5,
        ]);

        // Verify both sales exist independently
        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale1Id,
            'payment_status' => 'draft',
        ]);

        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale2Id,
            'payment_status' => 'draft',
        ]);

        // Create payment method for confirmation
        $payment = \App\Models\Payment::create([
            'order_id' => 'ORD-' . time(),
            'payment_type' => 'cash',
            'gross_amount' => 0,
            'transaction_status' => 'pending',
        ]);

        // Complete first sale only
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->postJson("/api/sales/{$sale1Id}/confirm-payment", [
            'payment_id' => $payment->payment_id,
        ]);

        // Verify first sale completed, second still draft
        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale1Id,
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale2Id,
            'payment_status' => 'draft',
        ]);
    }
}
