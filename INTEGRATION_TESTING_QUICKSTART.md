# Integration Testing - Quick Start Guide

## 🚀 Quick Start

### 1. Setup Testing Database
```bash
# Login ke MySQL
mysql -u root -p

# Create database testing
CREATE DATABASE pos_kasir_testing;
exit;
```

### 2. Configure Environment
Pastikan file `phpunit.xml` sudah memiliki konfigurasi testing database:
```xml
<env name="DB_DATABASE" value="pos_kasir_testing"/>
<env name="JWT_SECRET" value="TEST_JWT_SECRET_KEY_FOR_TESTING"/>
```

### 3. Run Integration Tests
```bash
# Run semua integration tests
php artisan test --testsuite=Integration

# Run specific test file
php artisan test tests/Integration/CompleteSalesFlowTest.php

# Run dengan coverage
php artisan test --testsuite=Integration --coverage
```

---

## 📋 Available Integration Tests

| Test Suite | File | Test Cases |
|------------|------|------------|
| **Sales Flow** | `CompleteSalesFlowTest.php` | 6 tests |
| **Inventory** | `InventoryManagementFlowTest.php` | 8 tests |
| **Authentication** | `UserAuthenticationFlowTest.php` | 8 tests |
| **Reporting** | `SalesReportingFlowTest.php` | 11 tests |

**Total: 33 Integration Test Cases** ✅

---

## 🧪 Test Examples

### Run Sales Flow Tests
```bash
php artisan test tests/Integration/CompleteSalesFlowTest.php
```

**Expected Output:**
```
✓ it can complete full sales workflow from product to payment
✓ it prevents overselling when stock insufficient
✓ it can cancel sale and restore stock
✓ it calculates tax and discount correctly
✓ it tracks multiple concurrent sales

Tests:  6 passed
Time:   2.35s
```

### Run Inventory Tests
```bash
php artisan test tests/Integration/InventoryManagementFlowTest.php
```

### Run Authentication Tests
```bash
php artisan test tests/Integration/UserAuthenticationFlowTest.php
```

### Run Reporting Tests
```bash
php artisan test tests/Integration/SalesReportingFlowTest.php
```

---

## 🔧 Using Test Helpers

### IntegrationTestCase
All integration tests extend from `IntegrationTestCase`:

```php
use Tests\Integration\IntegrationTestCase;

class MyTest extends IntegrationTestCase
{
    /** @test */
    public function example_test()
    {
        // Ready-to-use authenticated requests
        $response = $this->asAdmin()->getJson('/api/users');
        $response = $this->asKasir()->postJson('/api/sales', []);
        $response = $this->asGudang()->postJson('/api/stock-additions', []);
        
        // Helper assertions
        $this->assertSuccessResponse($response);
        $this->assertProductStock($productId, 50);
    }
}
```

### TestDataFactory
Generate test data quickly:

```php
use Tests\Helpers\TestDataFactory;

// Create 5 kasir users
$kasirs = TestDataFactory::createUsers(5, 'Kasir');

// Create 20 products
$products = TestDataFactory::createProducts(20);

// Create 100 random sales
$sales = TestDataFactory::createRandomSales(100);

// Create complete test environment
$env = TestDataFactory::createCompleteTestEnvironment();
// Returns: roles, users, categories, products
```

---

## 📊 Understanding Test Results

### Success Output
```
PASS  Tests\Integration\CompleteSalesFlowTest
✓ it can complete full sales workflow from product to payment (0.85s)
✓ it prevents overselling when stock insufficient (0.12s)
✓ it can cancel sale and restore stock (0.15s)

Tests:  3 passed
Time:   1.12s
```

### Failure Output
```
FAIL  Tests\Integration\CompleteSalesFlowTest
✗ it can complete full sales workflow from product to payment

Failed asserting that 422 matches expected 201.

Expected response status code [201] but received 422.
Response: {"meta":{"status":"error","message":"Insufficient stock"}}
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Database Not Found
```
SQLSTATE[HY000] [1049] Unknown database 'pos_kasir_testing'
```

**Solution:**
```bash
mysql -u root -p
CREATE DATABASE pos_kasir_testing;
```

### Issue 2: JWT Token Error
```
Token could not be parsed from the request
```

**Solution:**
Check `phpunit.xml` has:
```xml
<env name="JWT_SECRET" value="TEST_JWT_SECRET_KEY_FOR_TESTING"/>
```

### Issue 3: Migration Errors
```
Base table or view not found
```

**Solution:**
```bash
# Reset testing database
php artisan migrate:fresh --env=testing
```

---

## 📈 Coverage Report

Generate HTML coverage report:

```bash
# Generate coverage (requires Xdebug)
php artisan test --coverage --coverage-html coverage/html

# Open report
start coverage/html/index.html  # Windows
open coverage/html/index.html   # Mac
```

---

## 🎯 Writing Your Own Integration Tests

### Template

```php
<?php

namespace Tests\Integration;

use Tests\Integration\IntegrationTestCase;

class MyNewIntegrationTest extends IntegrationTestCase
{
    /** @test */
    public function it_can_do_something()
    {
        // Arrange: Setup test data
        $product = $this->createProduct();
        
        // Act: Perform action
        $response = $this->asKasir()->postJson('/api/sales', []);
        
        // Assert: Verify results
        $this->assertSuccessResponse($response);
        $this->assertDatabaseHas('sales', [
            'payment_status' => 'pending',
        ]);
    }
}
```

### Run Your Test

```bash
php artisan test tests/Integration/MyNewIntegrationTest.php
```

---

## 📚 Documentation

- **Full Documentation:** [INTEGRATION_TESTING.md](INTEGRATION_TESTING.md)
- **Laravel Testing Docs:** https://laravel.com/docs/testing
- **PHPUnit Docs:** https://phpunit.de/documentation.html

---

## ✅ Checklist Before Running Tests

- [ ] Testing database created (`pos_kasir_testing`)
- [ ] `phpunit.xml` configured correctly
- [ ] Migrations run on testing database
- [ ] Dependencies installed (`composer install`)
- [ ] JWT secret configured for testing

---

## 🆘 Need Help?

1. Check full documentation: `INTEGRATION_TESTING.md`
2. Review test examples in `tests/Integration/`
3. Use helper classes in `tests/Helpers/`
4. Debug with `$this->dumpResponse($response)`

---

**Happy Testing! 🧪**
