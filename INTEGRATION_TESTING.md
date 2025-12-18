# Integration Testing Documentation

## 📋 Daftar Isi
- [Overview](#overview)
- [Test Files](#test-files)
- [Setup & Configuration](#setup--configuration)
- [Running Tests](#running-tests)
- [Test Coverage](#test-coverage)
- [Best Practices](#best-practices)

---

## 🎯 Overview

Integration tests untuk project POS SuperCashier mencakup end-to-end testing dari semua fitur utama sistem. Tests ini memverifikasi bahwa komponen-komponen sistem bekerja sama dengan benar.

### Struktur Testing

```
tests/
├── Integration/
│   ├── IntegrationTestCase.php              # Base class untuk semua integration tests
│   ├── CompleteSalesFlowTest.php            # Testing complete sales workflow
│   ├── InventoryManagementFlowTest.php      # Testing inventory management
│   ├── UserAuthenticationFlowTest.php       # Testing authentication & user management
│   └── SalesReportingFlowTest.php           # Testing reporting system
├── Helpers/
│   └── TestDataFactory.php                  # Helper untuk generate test data
└── Feature/
    └── (existing feature tests)
```

---

## 📦 Test Files

### 1. CompleteSalesFlowTest.php
**Menguji:** Complete sales workflow dari product ke payment

**Test Cases:**
- ✅ Full sales workflow (product → sale → items → payment)
- ✅ Prevent overselling (insufficient stock)
- ✅ Cancel sale and restore stock
- ✅ Calculate tax and discount correctly
- ✅ Track multiple concurrent sales

**Contoh:**
```php
/** @test */
public function it_can_complete_full_sales_workflow_from_product_to_payment()
{
    // 1. Admin creates products
    // 2. Kasir creates a new sale
    // 3. Add items to sale
    // 4. Update quantities
    // 5. Apply discount
    // 6. Complete payment
    // 7. Verify stock levels
    // 8. Get sales history
}
```

---

### 2. InventoryManagementFlowTest.php
**Menguji:** Complete inventory management workflow

**Test Cases:**
- ✅ Full inventory workflow (category → product → stock addition)
- ✅ Prevent negative stock adjustments
- ✅ Track stock movements with audit trail
- ✅ Manage products across multiple categories
- ✅ Validate stock before product deletion
- ✅ Search and filter products
- ✅ Bulk update product prices

**Contoh:**
```php
/** @test */
public function it_can_complete_full_inventory_management_workflow()
{
    // 1. Admin creates product categories
    // 2. Admin creates product with zero stock
    // 3. Gudang staff adds initial stock
    // 4. Add more stock from different batch
    // 5. Get stock addition history
    // 6. Admin updates product details
    // 7. Get low stock products
}
```

---

### 3. UserAuthenticationFlowTest.php
**Menguji:** User lifecycle dan authentication flow

**Test Cases:**
- ✅ Complete authentication workflow (login → token → refresh → logout)
- ✅ Manage user lifecycle by admin
- ✅ Enforce role-based access control (RBAC)
- ✅ Validate user input during registration
- ✅ Change user password
- ✅ Handle concurrent login sessions
- ✅ Filter users by role

**Contoh:**
```php
/** @test */
public function it_can_complete_full_authentication_workflow()
{
    // 1. Login with wrong credentials (fail)
    // 2. Login with correct credentials (success)
    // 3. Get authenticated user info
    // 4. Refresh JWT token
    // 5. Access protected endpoint
    // 6. Logout and invalidate token
    // 7. Attempt to use invalidated token (fail)
}
```

---

### 4. SalesReportingFlowTest.php
**Menguji:** Complete reporting system

**Test Cases:**
- ✅ Generate daily sales report
- ✅ Generate date range sales report
- ✅ Generate product performance report
- ✅ Generate cashier performance report
- ✅ Generate profit analysis report
- ✅ Export report to Excel
- ✅ Get top selling products
- ✅ Get slow moving products
- ✅ Compare sales between periods
- ✅ Restrict report access by role

**Contoh:**
```php
/** @test */
public function it_can_generate_daily_sales_report()
{
    // 1. Create sales for today (morning, afternoon, evening)
    // 2. Get daily sales report
    // 3. Verify calculations (revenue, profit, count)
    // 4. Get report in PDF format
}
```

---

## ⚙️ Setup & Configuration

### Prerequisites

1. **Database Testing:**
```env
DB_CONNECTION=mysql
DB_DATABASE=pos_kasir_testing
JWT_SECRET=TEST_JWT_SECRET_KEY_FOR_TESTING
```

2. **Create Testing Database:**
```bash
mysql -u root -p
CREATE DATABASE pos_kasir_testing;
```

### Base Test Class

Semua integration tests extends dari `IntegrationTestCase`:

```php
use Tests\Integration\IntegrationTestCase;

class MyIntegrationTest extends IntegrationTestCase
{
    /** @test */
    public function it_does_something()
    {
        // Test automatically has:
        // - $this->adminUser, $this->kasirUser, $this->gudangUser
        // - $this->adminToken, $this->kasirToken, $this->gudangToken
        // - Helper methods: asAdmin(), asKasir(), asGudang()
    }
}
```

---

## 🚀 Running Tests

### Run All Integration Tests
```bash
php artisan test --testsuite=Integration
```

### Run Specific Test File
```bash
php artisan test tests/Integration/CompleteSalesFlowTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter it_can_complete_full_sales_workflow_from_product_to_payment
```

### Run with Coverage
```bash
php artisan test --coverage
```

### Run in Parallel
```bash
php artisan test --parallel
```

---

## 📊 Test Coverage

### Current Integration Test Coverage:

| Module | Test File | Test Cases | Coverage |
|--------|-----------|------------|----------|
| Sales Flow | CompleteSalesFlowTest | 6 tests | ✅ Complete |
| Inventory | InventoryManagementFlowTest | 8 tests | ✅ Complete |
| Authentication | UserAuthenticationFlowTest | 8 tests | ✅ Complete |
| Reporting | SalesReportingFlowTest | 11 tests | ✅ Complete |

**Total:** 33 integration test cases

---

## 💡 Best Practices

### 1. Use Helper Methods
```php
// ✅ Good - Use helper methods
$response = $this->asAdmin()->getJson('/api/users');

// ❌ Bad - Manually set headers
$response = $this->withHeaders([
    'Authorization' => 'Bearer ' . $token
])->getJson('/api/users');
```

### 2. Use Test Data Factory
```php
// ✅ Good - Use factory
use Tests\Helpers\TestDataFactory;

$users = TestDataFactory::createUsers(5, 'Kasir');
$products = TestDataFactory::createProducts(10);

// ❌ Bad - Create manually in each test
$user1 = User::create([...]);
$user2 = User::create([...]);
```

### 3. Assert Responses Properly
```php
// ✅ Good - Use custom assertions
$this->assertSuccessResponse($response, 'Sale created');
$this->assertProductStock($productId, 50);

// ❌ Bad - Manual assertions
$response->assertStatus(200);
$response->assertJson(['meta' => ['status' => 'success']]);
```

### 4. Test Workflow Completeness
```php
// ✅ Good - Test complete workflow
public function it_can_complete_full_workflow()
{
    // Step 1: Create resource
    // Step 2: Update resource
    // Step 3: Verify changes
    // Step 4: Delete resource
    // Step 5: Verify deletion
}

// ❌ Bad - Test only one action
public function it_can_create_resource()
{
    // Only create, no follow-up
}
```

### 5. Clean Test Names
```php
// ✅ Good - Descriptive test names
public function it_prevents_overselling_when_stock_insufficient()
public function it_can_cancel_sale_and_restore_stock()

// ❌ Bad - Vague test names
public function test_sales()
public function test_stock()
```

---

## 🔧 Helper Classes

### IntegrationTestCase

Base class dengan utilities:

**Properties:**
- `$adminUser`, `$kasirUser`, `$gudangUser`
- `$adminToken`, `$kasirToken`, `$gudangToken`
- `$adminRole`, `$kasirRole`, `$gudangRole`

**Methods:**
- `asAdmin()` - Make request as admin
- `asKasir()` - Make request as kasir
- `asGudang()` - Make request as gudang
- `createCategory()` - Create test category
- `createProduct()` - Create test product
- `createSale()` - Create test sale
- `assertSuccessResponse()` - Assert success response
- `assertErrorResponse()` - Assert error response
- `assertProductStock()` - Assert product stock level

### TestDataFactory

Factory untuk generate test data:

**Methods:**
- `createUsers($count, $role)` - Create multiple users
- `createProducts($count, $categoryId)` - Create multiple products
- `createRandomSales($count, $userId)` - Create sales with random data
- `createStockAdditions($productId, $count)` - Create stock additions
- `generateTokenForUser($userId)` - Generate JWT token
- `createCompleteTestEnvironment()` - Create complete test setup

---

## 📝 Example Usage

### Example 1: Test Complete Sales Flow
```php
use Tests\Integration\IntegrationTestCase;

class MySalesTest extends IntegrationTestCase
{
    /** @test */
    public function it_can_process_sale_with_multiple_items()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        $response = $this->asKasir()->postJson('/api/sales', []);
        $saleId = $response->json('data.sale_id');

        $this->asKasir()->postJson("/api/sales/{$saleId}/items", [
            'product_id' => $product1->product_id,
            'quantity' => 2,
        ]);

        $this->assertProductStock($product1->product_id, $product1->stock - 2);
    }
}
```

### Example 2: Test with Factory
```php
use Tests\Helpers\TestDataFactory;

/** @test */
public function it_can_generate_sales_report()
{
    // Create 50 random sales
    $sales = TestDataFactory::createRandomSales(50);

    $response = $this->asAdmin()->getJson('/api/reports/sales/daily');
    
    $this->assertSuccessResponse($response);
    $this->assertEquals(50, $response->json('data.sales_count'));
}
```

---

## 🐛 Debugging Tests

### Enable Test Debugging
```php
// In test method
$this->dumpResponse($response);

// Or dump specific data
dump($response->json());
dd($response->status());
```

### Check Database State
```php
// Check if record exists
$this->assertDatabaseHas('sales', [
    'sale_id' => $saleId,
    'payment_status' => 'paid',
]);

// Check if record doesn't exist
$this->assertDatabaseMissing('products', [
    'product_id' => $deletedId,
]);
```

---

## 📚 Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [JWT Auth Testing](https://jwt-auth.readthedocs.io/en/develop/)

---

**Created:** December 2024  
**Version:** 1.0  
**Author:** SuperCashier Development Team
