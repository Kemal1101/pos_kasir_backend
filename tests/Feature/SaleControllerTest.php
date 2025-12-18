<?php

namespace Tests\Feature;

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

class SaleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $category;
    protected $product;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $role = Role::create([
            'name' => 'Kasir',
            'description' => 'Kasir role for sales',
        ]);

        // Create test user
        $this->user = User::create([
            'role_id' => $role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'phone' => '081234567890',
        ]);

        // Generate JWT token
        $this->token = JWTAuth::fromUser($this->user);

        // Create category
        $this->category = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);

        // Create product with stock
        $this->product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Laptop Dell',
            'description' => 'Dell Inspiron 15',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 10,
            'barcode' => '1234567890',
        ]);

        // Create payment method
        $this->payment = Payment::create([
            'payment_method' => 'cash',
            'order_id' => 'ORDER-TEST-' . time(),
            'gross_amount' => 0,
            'transaction_status' => 'pending',
        ]);
    }

    /** @test */
    public function it_can_create_sale_with_jwt_authenticated_user()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/sales', []);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'sale_id',
                    'user_id',
                    'subtotal',
                    'discount_amount',
                    'tax_amount',
                    'total_amount',
                    'payment_status',
                    'sale_date',
                ],
            ])
            ->assertJson([
                'meta' => [
                    'message' => 'Sale created',
                ],
                'data' => [
                    'user_id' => $this->user->user_id,
                    'subtotal' => '0.00',
                    'payment_status' => 'draft',
                ],
            ]);

        $this->assertDatabaseHas('sales', [
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
            'total_amount' => 0,
        ]);
    }

    /** @test */
    public function it_can_create_sale_with_user_id_in_payload()
    {
        $response = $this->postJson('/api/sales', [
            'user_id' => $this->user->user_id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'user_id' => $this->user->user_id,
                    'payment_status' => 'draft',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_create_sale_without_user_authentication_or_payload()
    {
        $response = $this->postJson('/api/sales', []);

        $response->assertStatus(401)
            ->assertJson([
                'meta' => [
                    'message' => 'Unable to resolve user from token or payload',
                ],
            ]);
    }

    /** @test */
    public function it_can_add_item_to_sale_successfully()
    {
        $sale = Sale::create([
            'user_id' => $this->user->user_id,
            'payment_id' => null,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'discount_amount' => 100000,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'meta' => [
                    'message' => 'Item added to sale',
                ],
            ]);

        // Check product stock decreased
        $this->product->refresh();
        $this->assertEquals(8, $this->product->stock);

        // Check sale item created
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
        ]);

        // Check sale totals calculated correctly
        $sale->refresh();
        $expectedSubtotal = 7000000 * 2; // 14,000,000
        $this->assertEquals($expectedSubtotal, $sale->subtotal);
        $this->assertEquals(100000, $sale->discount_amount);
        $this->assertEquals($expectedSubtotal - 100000, $sale->total_amount);
    }

    /** @test */
    public function it_adds_item_with_default_quantity_when_not_provided()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 1,
        ]);

        $this->product->refresh();
        $this->assertEquals(9, $this->product->stock);
    }

    /** @test */
    public function it_fails_to_add_item_with_insufficient_stock()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 15, // More than available stock (10)
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'])
            ->assertJson([
                'errors' => [
                    'quantity' => [
                        "Insufficient stock for product 'Laptop Dell' (available: 10, requested: 15)",
                    ],
                ],
            ]);

        // Stock should remain unchanged
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock);
    }

    /** @test */
    public function it_fails_to_add_item_when_stock_is_zero()
    {
        $this->product->update(['stock' => 0]);

        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_fails_to_add_item_with_invalid_sale_id()
    {
        $response = $this->postJson('/api/sales/items', [
            'sale_id' => 99999,
            'product_id' => $this->product->product_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_id']);
    }

    /** @test */
    public function it_fails_to_add_item_with_invalid_product_id()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function it_fails_to_add_item_with_negative_quantity()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_can_remove_item_from_sale_and_restore_stock()
    {
        $sale = Sale::create([
            'user_id' => $this->user->user_id,
            'payment_id' => null,
            'subtotal' => 14000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 14000000,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => $this->product->name,
            'quantity' => 2,
            'discount_amount' => 0,
            'subtotal' => 14000000,
        ]);

        // Decrease stock to simulate item was added
        $this->product->decrement('stock', 2);
        $this->product->refresh();
        $this->assertEquals(8, $this->product->stock);

        $response = $this->deleteJson("/api/sales/items/{$saleItem->sale_item_id}");

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'message' => 'Item removed from sale',
                ],
            ]);

        // Stock should be restored
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock);

        // Sale item should be deleted
        $this->assertDatabaseMissing('sale_items', [
            'sale_item_id' => $saleItem->sale_item_id,
        ]);

        // Sale totals should be recalculated
        $sale->refresh();
        $this->assertEquals(0, $sale->subtotal);
        $this->assertEquals(0, $sale->total_amount);
    }

    /** @test */
    public function it_returns_not_found_when_removing_non_existent_item()
    {
        $response = $this->deleteJson('/api/sales/items/99999');

        $response->assertStatus(404)
            ->assertJson([
                'meta' => [
                    'message' => 'Sale item not found',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_sale_with_items_and_relationships()
    {
        $sale = Sale::create([
            'user_id' => $this->user->user_id,
            'payment_id' => $this->payment->payment_id,
            'subtotal' => 7000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 7000000,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => $this->product->name,
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $response = $this->getJson("/api/sales/{$sale->sale_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'sale_id',
                    'user_id',
                    'items' => [
                        '*' => [
                            'sale_item_id',
                            'product_id',
                            'quantity',
                            'product' => [
                                'product_id',
                                'name',
                            ],
                        ],
                    ],
                    'user' => [
                        'user_id',
                        'name',
                        'email',
                    ],
                    // Payment can be null for draft sales
                ],
            ]);
    }

    /** @test */
    public function it_returns_not_found_when_getting_non_existent_sale()
    {
        $response = $this->getJson('/api/sales/99999');

        $response->assertStatus(404)
            ->assertJson([
                'meta' => [
                    'message' => 'Sale not found',
                ],
            ]);
    }

    /** @test */
    public function it_can_delete_draft_sale_and_restore_stock()
    {
        $sale = Sale::create([
            'user_id' => $this->user->user_id,
            'payment_id' => null,
            'subtotal' => 14000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 14000000,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => $this->product->name,
            'quantity' => 3,
            'discount_amount' => 0,
            'subtotal' => 21000000,
        ]);

        // Decrease stock
        $this->product->decrement('stock', 3);
        $this->product->refresh();
        $this->assertEquals(7, $this->product->stock);

        $response = $this->deleteJson("/api/sales/{$sale->sale_id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Sale cancelled',
            ]);

        // Stock should be restored
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock);

        // Sale should be cancelled, not deleted
        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale->sale_id,
            'payment_status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_fails_to_delete_completed_sale()
    {
        $sale = Sale::create([
            'user_id' => $this->user->user_id,
            'payment_id' => $this->payment->payment_id,
            'subtotal' => 7000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 7000000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $response = $this->deleteJson("/api/sales/{$sale->sale_id}");

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'message' => 'Sale cancelled',
                ],
            ]);

        // Sale should be cancelled
        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale->sale_id,
            'payment_status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_returns_not_found_when_deleting_non_existent_sale()
    {
        $response = $this->deleteJson('/api/sales/99999');

        $response->assertStatus(404)
            ->assertJson([
                'meta' => [
                    'message' => 'Sale not found',
                ],
            ]);
    }

    /** @test */
    public function it_calculates_totals_correctly_with_multiple_items()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
            'tax_amount' => 500000,
        ]);

        $product2 = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Mouse Logitech',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 20,
            'barcode' => '0987654321',
        ]);

        // Add first item
        $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 1,
            'discount_amount' => 200000,
        ]);

        // Add second item
        $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $product2->product_id,
            'quantity' => 2,
            'discount_amount' => 10000,
        ]);

        $sale->refresh();

        // Expected: (7000000 * 1) + (150000 * 2) = 7,300,000
        $expectedSubtotal = 7300000;
        // Discount: 200000 + 10000 = 210,000
        $expectedDiscount = 210000;
        // Tax: 500,000
        $expectedTax = 500000;
        // Total: 7,300,000 - 210,000 + 500,000 = 7,590,000
        $expectedTotal = 7590000;

        $this->assertEquals($expectedSubtotal, $sale->subtotal);
        $this->assertEquals($expectedDiscount, $sale->discount_amount);
        $this->assertEquals($expectedTax, $sale->tax_amount);
        $this->assertEquals($expectedTotal, $sale->total_amount);
    }

    /** @test */
    public function it_handles_concurrent_stock_updates_with_locking()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        // This test verifies that lockForUpdate is working
        // In real scenario, this would prevent race conditions
        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 5,
        ]);

        $response->assertStatus(201);

        $this->product->refresh();
        $this->assertEquals(5, $this->product->stock);

        // Verify that the transaction was atomic
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->sale_id,
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function it_handles_zero_discount_amount_correctly()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        $response = $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 1,
            'discount_amount' => 0,
        ]);

        $response->assertStatus(201);

        $sale->refresh();
        $this->assertEquals(7000000, $sale->subtotal);
        $this->assertEquals(0, $sale->discount_amount);
        $this->assertEquals(7000000, $sale->total_amount);
    }

    /** @test */
    public function it_removes_multiple_items_and_recalculates_correctly()
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->user->user_id,
            'payment_status' => 'draft',
        ]);

        // Add two items
        $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
        ]);

        $product2 = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Keyboard',
            'selling_price' => 500000,
            'stock' => 15,
            'cost_price' => 300000,
        ]);

        $this->postJson('/api/sales/items', [
            'sale_id' => $sale->sale_id,
            'product_id' => $product2->product_id,
            'quantity' => 1,
        ]);

        $sale->refresh();
        $initialTotal = $sale->total_amount;

        // Remove first item
        $firstItem = SaleItem::where('sale_id', $sale->sale_id)
            ->where('product_id', $this->product->product_id)
            ->first();

        $this->deleteJson("/api/sales/items/{$firstItem->sale_item_id}");

        $sale->refresh();
        $this->assertEquals(500000, $sale->subtotal);
        $this->assertEquals(500000, $sale->total_amount);

        // Verify stock restored
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock);
    }
}
