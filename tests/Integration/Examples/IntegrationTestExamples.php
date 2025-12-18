<?php

/**
 * INTEGRATION TEST EXAMPLES
 * 
 * This file contains example templates for creating integration tests
 * Copy and modify these examples for your own tests
 */

namespace Tests\Integration\Examples;

use Tests\Integration\IntegrationTestCase;
use Tests\Helpers\TestDataFactory;

// ============================================
// EXAMPLE 1: Basic Integration Test
// ============================================

class BasicIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_can_perform_basic_crud_operations()
    {
        // ARRANGE: Setup test data
        $category = $this->createCategory('Test Category');

        // ACT: Perform the operation
        $response = $this->asAdmin()->getJson('/api/categories');

        // ASSERT: Verify the results
        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
        ]);
    }
}

// ============================================
// EXAMPLE 2: Testing Complete Workflow
// ============================================

class WorkflowIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_can_complete_product_to_sale_workflow()
    {
        // STEP 1: Create product
        $product = $this->createProduct(null, [
            'name' => 'Test Product',
            'selling_price' => 10000,
            'stock' => 100,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'stock' => 100,
        ]);

        // STEP 2: Create sale
        $saleResponse = $this->asKasir()->postJson('/api/sales', []);
        $saleResponse->assertStatus(201);
        $saleId = $saleResponse->json('data.sale_id');

        // STEP 3: Add product to sale
        $addItemResponse = $this->asKasir()->postJson("/api/sales/{$saleId}/items", [
            'product_id' => $product->product_id,
            'quantity' => 5,
        ]);

        $addItemResponse->assertStatus(201);

        // STEP 4: Verify stock reduced
        $this->assertProductStock($product->product_id, 95);

        // STEP 5: Complete payment
        $paymentResponse = $this->asKasir()->postJson("/api/sales/{$saleId}/payment", [
            'payment_method' => 'cash',
            'amount_paid' => 50000,
        ]);

        $paymentResponse->assertStatus(200);

        // STEP 6: Verify sale completed
        $this->assertDatabaseHas('sales', [
            'sale_id' => $saleId,
            'payment_status' => 'paid',
        ]);
    }
}

// ============================================
// EXAMPLE 3: Using TestDataFactory
// ============================================

class FactoryIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_can_handle_bulk_operations_using_factory()
    {
        // Create 10 products at once
        $products = TestDataFactory::createProducts(10);

        $this->assertCount(10, $products);

        // Create 5 kasir users
        $kasirs = TestDataFactory::createUsers(5, 'Kasir');

        $this->assertCount(5, $kasirs);

        // Create 20 random sales
        $sales = TestDataFactory::createRandomSales(20, $kasirs[0]->user_id);

        $this->assertCount(20, $sales);

        // Verify sales in database
        $this->assertEquals(20, \App\Models\Sale::count());
    }
}

// ============================================
// EXAMPLE 4: Testing Error Scenarios
// ============================================

class ErrorHandlingIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_handles_validation_errors_correctly()
    {
        // Try to create product without required fields
        $response = $this->asAdmin()->postJson('/api/products/add_product', [
            'name' => 'Incomplete Product',
            // Missing: categories_id, cost_price, selling_price, stock
        ]);

        // Should return 422 Unprocessable Entity
        $response->assertStatus(422);
        
        // Should have validation errors
        $response->assertJsonValidationErrors([
            'categories_id',
            'cost_price',
            'selling_price',
            'stock',
        ]);

        // Verify product was NOT created
        $this->assertDatabaseMissing('products', [
            'name' => 'Incomplete Product',
        ]);
    }

    /** @test */
    public function it_prevents_unauthorized_access()
    {
        // Kasir tries to access admin-only endpoint
        $response = $this->asKasir()->postJson('/api/users/add_user', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password',
            'role_id' => 2,
        ]);

        // Should be forbidden
        $response->assertStatus(403);

        // Verify user was NOT created
        $this->assertDatabaseMissing('users', [
            'email' => 'new@test.com',
        ]);
    }
}

// ============================================
// EXAMPLE 5: Testing Business Logic
// ============================================

class BusinessLogicIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_calculates_profit_correctly()
    {
        // Create product with known cost and price
        $product = $this->createProduct(null, [
            'name' => 'Profitable Product',
            'cost_price' => 5000,
            'selling_price' => 10000,
            'stock' => 100,
        ]);

        // Create and complete sale
        $sale = $this->createSale();
        $this->createSaleItem($sale->sale_id, $product->product_id, 10);

        // Update sale totals
        $sale->update([
            'subtotal' => 100000, // 10 x 10000
            'total_amount' => 100000,
            'payment_status' => 'paid',
        ]);

        // Get profit report
        $response = $this->asAdmin()->getJson('/api/reports/profit/analysis');
        $response->assertStatus(200);

        $profit = $response->json('data.total_profit');

        // Profit should be: (10000 - 5000) * 10 = 50000
        $this->assertEquals(50000, $profit);
    }

    /** @test */
    public function it_applies_discount_correctly()
    {
        // Create sale with known subtotal
        $sale = $this->createSale();
        $sale->update(['subtotal' => 100000]);

        // Apply 10% discount (10000)
        $response = $this->asKasir()->putJson("/api/sales/{$sale->sale_id}/discount", [
            'discount_amount' => 10000,
        ]);

        $response->assertStatus(200);

        // Verify discount applied
        $sale->refresh();
        $this->assertEquals(10000, $sale->discount_amount);
        $this->assertEquals(90000, $sale->total_amount);
    }
}

// ============================================
// EXAMPLE 6: Testing with Custom Assertions
// ============================================

class CustomAssertionsIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_uses_custom_assertions()
    {
        // Create and complete sale
        $sale = $this->createCompletedSaleWithItems([
            ['price' => 10000, 'quantity' => 2],
            ['price' => 5000, 'quantity' => 3],
        ]);

        // Use custom assertions from IntegrationTestCase
        $this->assertSaleExists($sale->sale_id, 2); // 2 items

        // Get sale details
        $response = $this->asKasir()->getJson("/api/sales/{$sale->sale_id}");

        // Use custom response assertions
        $this->assertSuccessResponse($response);
    }
}

// ============================================
// EXAMPLE 7: Testing Date-Based Operations
// ============================================

class DateBasedIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_filters_sales_by_date_range()
    {
        $today = \Carbon\Carbon::today();
        $yesterday = $today->copy()->subDay();
        $lastWeek = $today->copy()->subWeek();

        // Create sales on different dates
        TestDataFactory::createRandomSales(5, null, $lastWeek, $lastWeek);
        TestDataFactory::createRandomSales(3, null, $yesterday, $yesterday);
        TestDataFactory::createRandomSales(2, null, $today, $today);

        // Get sales for today only
        $response = $this->asAdmin()->getJson('/api/reports/sales/daily?date=' . $today->toDateString());

        $response->assertStatus(200);
        $salesCount = $response->json('data.sales_count');

        $this->assertEquals(2, $salesCount);
    }
}

// ============================================
// EXAMPLE 8: Testing Concurrent Operations
// ============================================

class ConcurrentOperationsIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_handles_concurrent_stock_updates()
    {
        $product = $this->createProduct(null, [
            'stock' => 100,
        ]);

        // Create 3 sales simultaneously
        $sale1 = $this->createSale();
        $sale2 = $this->createSale();
        $sale3 = $this->createSale();

        // Add same product to all sales
        $this->asKasir()->postJson("/api/sales/{$sale1->sale_id}/items", [
            'product_id' => $product->product_id,
            'quantity' => 10,
        ]);

        $this->asKasir()->postJson("/api/sales/{$sale2->sale_id}/items", [
            'product_id' => $product->product_id,
            'quantity' => 15,
        ]);

        $this->asKasir()->postJson("/api/sales/{$sale3->sale_id}/items", [
            'product_id' => $product->product_id,
            'quantity' => 20,
        ]);

        // Verify final stock: 100 - 10 - 15 - 20 = 55
        $this->assertProductStock($product->product_id, 55);
    }
}

// ============================================
// EXAMPLE 9: Testing Rollback Scenarios
// ============================================

class RollbackIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_rolls_back_on_payment_failure()
    {
        $product = $this->createProduct(null, [
            'stock' => 50,
        ]);

        // Create sale and add items
        $sale = $this->createSale();
        
        $this->asKasir()->postJson("/api/sales/{$sale->sale_id}/items", [
            'product_id' => $product->product_id,
            'quantity' => 5,
        ]);

        // Stock should be reduced
        $this->assertProductStock($product->product_id, 45);

        // Cancel the sale
        $this->asKasir()->deleteJson("/api/sales/{$sale->sale_id}");

        // Stock should be restored
        $this->assertProductStock($product->product_id, 50);

        // Sale should be cancelled
        $this->assertDatabaseHas('sales', [
            'sale_id' => $sale->sale_id,
            'payment_status' => 'cancelled',
        ]);
    }
}

// ============================================
// EXAMPLE 10: Performance Testing
// ============================================

class PerformanceIntegrationTestExample extends IntegrationTestCase
{
    /** @test */
    public function it_handles_large_dataset_efficiently()
    {
        $startTime = microtime(true);

        // Create 100 products
        $products = TestDataFactory::createProducts(100);

        // Create 200 sales
        $sales = TestDataFactory::createRandomSales(200);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete in reasonable time (< 10 seconds)
        $this->assertLessThan(10, $executionTime);

        // Verify data integrity
        $this->assertEquals(100, \App\Models\Product::count());
        $this->assertEquals(200, \App\Models\Sale::count());
    }
}

// ============================================
// TIPS FOR WRITING INTEGRATION TESTS
// ============================================

/*
1. USE DESCRIPTIVE TEST NAMES
   ✅ it_can_complete_full_sales_workflow
   ❌ test_sales

2. FOLLOW AAA PATTERN
   - Arrange: Setup test data
   - Act: Perform the operation
   - Assert: Verify the results

3. TEST ONE THING PER TEST
   - Each test should verify one specific behavior
   - Keep tests focused and simple

4. USE HELPER METHODS
   - Use IntegrationTestCase helpers
   - Use TestDataFactory for bulk data
   - Create custom helpers for repeated operations

5. CLEAN UP PROPERLY
   - Use RefreshDatabase trait
   - Don't rely on previous test state
   - Each test should be independent

6. ASSERT THOROUGHLY
   - Check database state
   - Verify response structure
   - Test edge cases

7. HANDLE ERRORS GRACEFULLY
   - Test both success and failure scenarios
   - Verify error messages
   - Check rollback behavior

8. DOCUMENT COMPLEX TESTS
   - Add comments for complex logic
   - Explain why, not just what
   - Include examples in docblocks
*/
