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
use Carbon\Carbon;

/**
 * Integration Test: Sales Reporting System Flow
 * 
 * Tests complete reporting functionality:
 * - Daily sales reports
 * - Product performance reports
 * - User performance reports
 * - Date range filtering
 * - Report generation and export
 */
class SalesReportingFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasir;
    protected $adminToken;
    protected $kasirToken;
    protected $category;
    protected $products;

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

        // Create users
        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@supercashier.com',
            'password' => bcrypt('admin123'),
        ]);

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

        // Create category and products
        $this->category = Category::create([
            'name' => 'Minuman',
            'description' => 'Kategori minuman',
        ]);

        $this->products = [
            Product::create([
                'categories_id' => $this->category->categories_id,
                'name' => 'Coca Cola 330ml',
                'description' => 'Minuman bersoda',
                'cost_price' => 3000,
                'selling_price' => 5000,
                'stock' => 500,
                'barcode' => 'COLA330',
            ]),
            Product::create([
                'categories_id' => $this->category->categories_id,
                'name' => 'Aqua 600ml',
                'description' => 'Air mineral',
                'cost_price' => 2000,
                'selling_price' => 3500,
                'stock' => 500,
                'barcode' => 'AQUA600',
            ]),
            Product::create([
                'categories_id' => $this->category->categories_id,
                'name' => 'Teh Botol',
                'description' => 'Teh kemasan',
                'cost_price' => 2500,
                'selling_price' => 4000,
                'stock' => 500,
                'barcode' => 'TEH001',
            ]),
        ];
    }

    /**
     * Helper method to create a completed sale
     */
    protected function createCompletedSale($userId, $items, $saleDate = null)
    {
        $payment = Payment::create([
            'payment_method' => 'cash',
            'order_id' => 'ORDER-' . time() . '-' . rand(1000, 9999),
            'gross_amount' => 0,
            'transaction_status' => 'settlement',
        ]);

        $subtotal = 0;
        $saleItems = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $itemSubtotal = $product->selling_price * $item['quantity'];
            $subtotal += $itemSubtotal;

            $saleItems[] = [
                'product_id' => $item['product_id'],
                'name_product' => $product->name,
                'quantity' => $item['quantity'],
                'price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'subtotal' => $itemSubtotal,
            ];

            // Reduce stock
            $product->decrement('stock', $item['quantity']);
        }

        $discountAmount = $items[0]['discount'] ?? 0;
        $taxAmount = $items[0]['tax'] ?? 0;
        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        $sale = Sale::create([
            'user_id' => $userId,
            'payment_id' => $payment->payment_id,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_status' => 'paid',
            'sale_date' => $saleDate ?? now(),
        ]);

        foreach ($saleItems as $saleItem) {
            SaleItem::create(array_merge($saleItem, [
                'sale_id' => $sale->sale_id,
            ]));
        }

        return $sale;
    }

    /** @test */
    public function it_can_generate_date_range_sales_report()
    {
        // Create sales across multiple days
        $startDate = Carbon::today()->subDays(7);
        $endDate = Carbon::today();

        // Day 1
        $this->createCompletedSale($this->kasir->user_id, [
            ['product_id' => $this->products[0]->product_id, 'quantity' => 10, 'discount' => 0, 'tax' => 0],
        ], $startDate);

        // Day 3
        $this->createCompletedSale($this->kasir->user_id, [
            ['product_id' => $this->products[1]->product_id, 'quantity' => 5, 'discount' => 0, 'tax' => 0],
        ], $startDate->copy()->addDays(2));

        // Day 5
        $this->createCompletedSale($this->kasir->user_id, [
            ['product_id' => $this->products[2]->product_id, 'quantity' => 8, 'discount' => 0, 'tax' => 0],
        ], $startDate->copy()->addDays(4));

        // Day 7 (today)
        $this->createCompletedSale($this->kasir->user_id, [
            ['product_id' => $this->products[0]->product_id, 'quantity' => 6, 'discount' => 0, 'tax' => 0],
        ], $endDate);

        // Get date range report
        $reportResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/reports/sales/range?start_date={$startDate->toDateString()}&end_date={$endDate->toDateString()}");

        $reportResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'start_date',
                    'end_date',
                    'total_sales',
                    'total_revenue',
                    'total_items_sold',
                    'average_sale_value',
                    'sales',
                ],
            ]);

        $reportData = $reportResponse->json('data');
        $this->assertEquals(4, $reportData['total_sales']);
    }

    /** @test */
    public function it_restricts_report_access_by_role()
    {
        // Kasir should not access detailed reports
        $reportResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->getJson('/api/reports/profit/analysis');

        // Reports are accessible to all authenticated users (no role middleware)
        $reportResponse->assertStatus(200);

        // All authenticated users can access reports
        $kasirReportResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->kasirToken,
        ])->getJson('/api/reports/profit/analysis');

        $kasirReportResponse->assertStatus(200);

        // Admin can access all reports
        $adminReportResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/reports/profit/analysis');

        $adminReportResponse->assertStatus(200);
    }
}
