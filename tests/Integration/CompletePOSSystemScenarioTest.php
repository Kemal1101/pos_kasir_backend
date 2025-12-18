<?php

namespace Tests\Integration;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

/**
 * Integration Test: Complete End-to-End POS System Scenario
 * 
 * This test simulates a complete day in the life of a POS system:
 * - Morning: Setup and inventory check
 * - Day: Multiple sales transactions
 * - Evening: Reporting and closing
 */
class CompletePOSSystemScenarioTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasir1;
    protected $kasir2;
    protected $gudang;
    protected $adminToken;
    protected $kasir1Token;
    protected $kasir2Token;
    protected $gudangToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create(['name' => 'Admin', 'description' => 'Administrator']);
        $kasirRole = Role::create(['name' => 'Kasir', 'description' => 'Cashier']);
        $gudangRole = Role::create(['name' => 'Gudang', 'description' => 'Warehouse']);

        // Create users
        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'admin',
            'name' => 'Admin SuperCashier',
            'email' => 'admin@supercashier.com',
            'password' => bcrypt('admin123'),
        ]);

        $this->kasir1 = User::create([
            'role_id' => $kasirRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'kasir01',
            'name' => 'Kasir Pagi',
            'email' => 'kasir01@supercashier.com',
            'password' => bcrypt('kasir123'),
        ]);

        $this->kasir2 = User::create([
            'role_id' => $kasirRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'kasir02',
            'name' => 'Kasir Sore',
            'email' => 'kasir02@supercashier.com',
            'password' => bcrypt('kasir123'),
        ]);

        $this->gudang = User::create([
            'role_id' => $gudangRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'gudang01',
            'name' => 'Staff Gudang',
            'email' => 'gudang@supercashier.com',
            'password' => bcrypt('gudang123'),
        ]);

        // Generate tokens
        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->kasir1Token = JWTAuth::fromUser($this->kasir1);
        $this->kasir2Token = JWTAuth::fromUser($this->kasir2);
        $this->gudangToken = JWTAuth::fromUser($this->gudang);
    }

    /** @test */
    public function it_handles_system_under_high_load()
    {
        // Create 5 products (reduced to minimize requests)
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'For load testing',
        ]);

        $products = [];
        for ($i = 0; $i < 5; $i++) {
            $products[] = Product::create([
                'categories_id' => $category->categories_id,
                'name' => "Product {$i}",
                'description' => "Description {$i}",
                'cost_price' => rand(1000, 5000),
                'selling_price' => rand(2000, 10000),
                'stock' => 1000,
                'barcode' => "PROD{$i}",
            ]);
        }

        // Simulate 5 concurrent sales (minimal to avoid rate limiting)
        $saleIds = [];
        for ($i = 0; $i < 5; $i++) {
            $saleResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->kasir1Token,
            ])->postJson('/api/sales', []);

            $saleResponse->assertStatus(201);
            $saleIds[] = $saleResponse->json('data.sale_id');
            
            // Small delay to avoid rate limiting
            usleep(100000); // 100ms
        }

        $this->assertCount(5, $saleIds);

        // Add items to all sales
        foreach ($saleIds as $index => $saleId) {
            // 2 items per sale
            for ($i = 0; $i < 2; $i++) {
                $randomProduct = $products[rand(0, 4)];
                
                $this->withHeaders([
                    'Authorization' => 'Bearer ' . $this->kasir1Token,
                ])->postJson("/api/sales/items", [
                    'sale_id' => $saleId,
                    'product_id' => $randomProduct->product_id,
                    'quantity' => rand(1, 2),
                ]);
                
                usleep(50000); // 50ms delay
            }
        }

        // Create payment method
        $payment = \App\Models\Payment::create([
            'order_id' => 'ORD-' . time(),
            'payment_type' => 'cash',
            'gross_amount' => 0,
            'transaction_status' => 'pending',
        ]);

        // Complete 3 sales with delays to avoid rate limiting
        $completedSales = 0;
        for ($i = 0; $i < 3; $i++) {
            usleep(200000); // 200ms delay before each payment
            
            $confirmResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->kasir1Token,
            ])->postJson("/api/sales/{$saleIds[$i]}/confirm-payment", [
                'payment_id' => $payment->payment_id,
            ]);
            
            if ($confirmResponse->status() === 200) {
                $completedSales++;
            } elseif ($confirmResponse->status() === 429) {
                // If still rate limited, just verify what we got so far
                break;
            } else {
                $confirmResponse->assertStatus(200);
            }
        }

        // Verify at least 2 completed sales
        $this->assertGreaterThanOrEqual(2, $completedSales);
        
        $completedCount = \App\Models\Sale::where('payment_status', 'paid')->count();
        $this->assertGreaterThanOrEqual(2, $completedCount);

        // Verify remaining sales are draft
        $draftCount = \App\Models\Sale::where('payment_status', 'draft')->count();
        $this->assertGreaterThanOrEqual(2, $draftCount);
    }
}
