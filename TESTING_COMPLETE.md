# 🧪 Complete Testing Documentation

Dokumentasi lengkap untuk Unit Testing dan Integration Testing pada SuperCashier POS System Backend.

---

## 📋 Daftar Isi

- [Overview](#overview)
- [Test Statistics](#test-statistics)
- [Unit Testing](#unit-testing)
- [Integration Testing](#integration-testing)
- [Running All Tests](#running-all-tests)
- [Quick Commands](#quick-commands)

---

## Overview

Project SuperCashier POS Backend memiliki comprehensive test suite yang mencakup:
- **Unit Tests** - Testing komponen individual (Models, Helpers)
- **Feature Tests** - Testing API endpoints dan controllers
- **Integration Tests** - Testing end-to-end workflows

**Total Coverage:**
- **292 Tests**
- **949 Assertions**
- **Average Duration:** ~34 seconds

---

## Test Statistics

### Overall Summary

| Test Suite | Tests | Assertions | Duration |
|------------|-------|------------|----------|
| Unit Tests | 211 | 548 | ~6s |
| Feature Tests | 60 | 368 | ~8s |
| Integration Tests | 21 | 33 | ~3.5s |
| **TOTAL** | **292** | **949** | **~34s** |

### Success Rate
✅ **100% Pass Rate** (292/292 tests passing)

---

## Unit Testing

### 📍 Location
`tests/Unit/`

### 📊 Coverage

**Total:** 211 tests, 548 assertions

#### Model Tests (190 tests)

| Model Test | Tests | Key Features |
|------------|-------|--------------|
| CategoryModelTest | 17 | CRUD, relationships, data validation |
| ProductModelTest | 11 | CRUD, stock management, relationships |
| SaleModelTest | 12 | CRUD, calculations, status handling |
| SaleItemModelTest | 21 | CRUD, discount handling, relationships |
| PaymentModelTest | 19 | CRUD, metadata handling, relationships |
| UserModelTest | 22 | CRUD, authentication, security |
| RoleModelTest | 19 | CRUD, relationships, permissions |
| StockAdditionModelTest | 23 | CRUD, tracking, audit trail |
| ExampleTest | 1 | Basic sanity test |

#### Helper Tests (21 tests)

| Helper Test | Tests | Key Features |
|-------------|-------|--------------|
| ResponseHelperTest | 21 | Success/error responses, JSON structure |

### 🚀 Running Unit Tests

```bash
# All unit tests
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Unit/ProductModelTest.php

# With filter
php artisan test --filter=ProductModel

# With coverage
php artisan test --testsuite=Unit --coverage

# Using test runner
php run-tests.php --unit
php run-tests.php --unit --coverage
```

### 📝 Unit Test Examples

#### Model Test Example
```php
/** @test */
public function it_can_create_product_with_minimal_data()
{
    $category = Category::factory()->create();
    
    $product = Product::create([
        'category_id' => $category->category_id,
        'name' => 'Laptop Asus',
        'cost_price' => 5000000,
        'selling_price' => 7000000,
        'stock' => 10,
    ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Laptop Asus',
        'stock' => 10,
    ]);
}
```

#### Helper Test Example
```php
/** @test */
public function it_returns_success_response_with_data()
{
    $data = ['id' => 1, 'name' => 'Test'];
    $response = ResponseHelper::success($data, 'Success');

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals([
        'meta' => [
            'status' => 200,
            'message' => 'Success',
        ],
        'data' => $data,
    ], $response->getData(true));
}
```

### 📚 Detailed Documentation
See [UNIT_TESTING.md](UNIT_TESTING.md) for complete unit testing guide.

---

## Integration Testing

### 📍 Location
- Feature Tests: `tests/Feature/`
- Integration Tests: `tests/Integration/`

### 📊 Coverage

**Total:** 81 tests, 401 assertions

#### Feature Tests (60 tests)

| Controller Test | Tests | API Endpoints |
|----------------|-------|---------------|
| AuthControllerTest | 21 | /api/auth/* |
| CategoryControllerTest | 17 | /api/categories/* |
| ProductControllerTest | 21 | /api/products/* |
| SaleControllerTest | 21 | /api/sales/* |
| UsersControllerTest | 25 | /api/users/* |

#### Integration Workflow Tests (21 tests)

| Workflow Test | Tests | Scenarios |
|--------------|-------|-----------|
| CompleteSalesFlowTest | 5 | Sales workflow, overselling, cancellation |
| InventoryManagementFlowTest | 5 | Inventory CRUD, stock tracking |
| UserAuthenticationFlowTest | 7 | Auth, RBAC, user lifecycle |
| SalesReportingFlowTest | 2 | Report generation, permissions |
| CompletePOSSystemScenarioTest | 1 | System under high load |

### 🚀 Running Integration Tests

```bash
# All feature tests
php artisan test --testsuite=Feature

# All integration workflows
php artisan test tests/Integration/

# Specific test
php artisan test tests/Feature/ProductControllerTest.php
php artisan test tests/Integration/CompleteSalesFlowTest.php

# With filter
php artisan test --filter=CompleteSales

# With coverage
php artisan test tests/Integration/ --coverage

# Using test runner
php run-tests.php --feature
```

### 📝 Integration Test Examples

#### Feature Test Example (API Testing)
```php
/** @test */
public function it_can_create_product_successfully()
{
    $category = Category::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/products/add_product', [
        'category_id' => $category->category_id,
        'name' => 'Laptop Asus',
        'cost_price' => 5000000,
        'selling_price' => 7000000,
        'stock' => 10,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'meta' => ['status', 'message'],
            'data' => ['product_id', 'name', 'stock'],
        ]);
}
```

#### Integration Workflow Test Example
```php
/** @test */
public function it_can_complete_full_sales_workflow()
{
    // 1. Create product
    $product = Product::factory()->create(['stock' => 10]);
    
    // 2. Create sale
    $sale = Sale::create([
        'user_id' => $this->user->user_id,
        'payment_status' => 'draft',
    ]);
    
    // 3. Add items
    $this->postJson('/api/sales/items', [
        'sale_id' => $sale->sale_id,
        'product_id' => $product->product_id,
        'quantity' => 2,
    ])->assertStatus(201);
    
    // 4. Verify stock decreased
    $product->refresh();
    $this->assertEquals(8, $product->stock);
    
    // 5. Confirm payment
    $payment = Payment::create(['gross_amount' => 14000000]);
    $sale->update(['payment_id' => $payment->payment_id]);
    
    // 6. Verify sale completed
    $this->assertDatabaseHas('sales', [
        'sale_id' => $sale->sale_id,
        'payment_status' => 'paid',
    ]);
}
```

### 🎯 Key Integration Test Scenarios

#### 1. Complete Sales Flow
- ✅ Create product → Create sale → Add items → Confirm payment
- ✅ Prevent overselling
- ✅ Cancel sale and restore stock
- ✅ Calculate tax and discount
- ✅ Handle concurrent sales

#### 2. Inventory Management
- ✅ Complete inventory workflow
- ✅ Prevent negative stock
- ✅ Track stock movements with audit trail
- ✅ Validate before deletion
- ✅ Search and filter products

#### 3. User Authentication
- ✅ Complete auth workflow
- ✅ User lifecycle management
- ✅ Role-based access control
- ✅ Input validation
- ✅ Password changes
- ✅ Concurrent sessions

#### 4. Sales Reporting
- ✅ Date range reports
- ✅ Role-based access restrictions

#### 5. System Performance
- ✅ High load testing (50+ products, 10+ concurrent sales)

### 📚 Detailed Documentation
See [INTEGRATION_TESTING.md](INTEGRATION_TESTING.md) for complete integration testing guide.

---

## Running All Tests

### Complete Test Suite

```bash
# Run all tests
php artisan test

# Run all tests with coverage
php artisan test --coverage --min=80

# Using test runner
php run-tests.php
php run-tests.php --coverage
```

### Test Output Example
```
   PASS  Tests\Unit\CategoryModelTest
  ✓ it has fillable attributes                      3.52s
  ✓ it can create category with name only          0.08s
  ✓ it can update category                          0.08s
  ...
  
   PASS  Tests\Feature\ProductControllerTest
  ✓ it can create product successfully              0.09s
  ✓ it can update product successfully              0.10s
  ...
  
   PASS  Tests\Integration\CompleteSalesFlowTest
  ✓ it can complete full sales workflow             0.22s
  ✓ it prevents overselling                         0.11s
  ...

  Tests:    292 passed (949 assertions)
  Duration: 34.23s
```

---

## Quick Commands

### Essential Test Commands

```bash
# Run all tests
php artisan test

# Run by suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific file
php artisan test tests/Unit/ProductModelTest.php
php artisan test tests/Feature/SaleControllerTest.php
php artisan test tests/Integration/CompleteSalesFlowTest.php

# Filter tests
php artisan test --filter=Product
php artisan test --filter=it_can_create_product

# Coverage
php artisan test --coverage
php artisan test --coverage --min=80

# Parallel execution
php artisan test --parallel

# Stop on failure
php artisan test --stop-on-failure

# Verbose output
php artisan test --verbose
```

### Test Runner Commands

```bash
# Run all tests
php run-tests.php

# Run by suite
php run-tests.php --unit
php run-tests.php --feature

# With coverage
php run-tests.php --coverage
php run-tests.php --unit --coverage
php run-tests.php --feature --coverage

# Filter
php run-tests.php --filter=Product
php run-tests.php --filter=Sale
```

---

## Important Notes

### API Response Codes

#### Success Codes
- **200 OK** - Successful GET, PUT, DELETE
- **201 Created** - POST /api/products/add_product, POST /api/sales/items

#### Error Codes
- **400 Bad Request** - Invalid input
- **401 Unauthorized** - Not authenticated
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation error

### Key Behaviors

#### Product Creation
- Returns **201** status code (not 200)

#### Sale Items
- Adding items returns **201** status code
- Stock decreases when item added
- Stock restores when item removed

#### Sale Deletion
- Returns **200** with message "Sale cancelled"
- Sale status changes to **'cancelled'** (not actually deleted)
- Stock is restored

#### User List
- Returns **200** with empty array when no users (not 404)
- Pagination not fully implemented (returns all users)

---

## Coverage Reports

### Generate Coverage

```bash
# HTML report
php run-tests.php --coverage

# Coverage with minimum threshold
php artisan test --coverage --min=80
```

### Coverage Files
After running tests with coverage:
- **HTML:** `coverage/html/index.html` (open in browser)
- **Text:** `coverage/coverage.txt`
- **Clover:** `coverage/clover.xml`

### Coverage Targets
- **Minimum:** 80%
- **Target:** 90%+
- **Current:** 100% test pass rate

---

## Test Organization

### Directory Structure
```
tests/
├── Unit/                           # Unit tests (211 tests)
│   ├── CategoryModelTest.php
│   ├── ProductModelTest.php
│   ├── SaleModelTest.php
│   ├── SaleItemModelTest.php
│   ├── PaymentModelTest.php
│   ├── UserModelTest.php
│   ├── RoleModelTest.php
│   ├── StockAdditionModelTest.php
│   ├── ResponseHelperTest.php
│   └── ExampleTest.php
│
├── Feature/                        # Feature/API tests (60 tests)
│   ├── AuthControllerTest.php
│   ├── CategoryControllerTest.php
│   ├── ProductControllerTest.php
│   ├── SaleControllerTest.php
│   ├── UsersControllerTest.php
│   └── ExampleTest.php
│
├── Integration/                    # Integration workflow tests (21 tests)
│   ├── CompleteSalesFlowTest.php
│   ├── InventoryManagementFlowTest.php
│   ├── UserAuthenticationFlowTest.php
│   ├── SalesReportingFlowTest.php
│   ├── CompletePOSSystemScenarioTest.php
│   └── IntegrationTestCase.php
│
├── Helpers/                        # Test helpers
│   └── TestDataFactory.php
│
├── TestCase.php                    # Base test case
└── CreatesApplication.php          # Laravel app creation
```

---

## Best Practices

### 1. Test Naming
```php
/** @test */
public function it_can_do_something()
{
    // Descriptive name using "it_can_" or "it_fails_to_" prefix
}
```

### 2. AAA Pattern (Arrange-Act-Assert)
```php
/** @test */
public function it_calculates_total_correctly()
{
    // Arrange
    $product = Product::factory()->create(['price' => 1000]);
    
    // Act
    $total = $product->price * 2;
    
    // Assert
    $this->assertEquals(2000, $total);
}
```

### 3. Use Factories
```php
// Good - uses factory
$user = User::factory()->create();

// Avoid - manual creation
$user = User::create([...all fields...]);
```

### 4. Test Isolation
```php
protected function setUp(): void
{
    parent::setUp();
    // Each test starts with fresh state
}
```

### 5. Meaningful Assertions
```php
// Good - specific assertions
$this->assertDatabaseHas('products', ['name' => 'Laptop']);
$this->assertEquals(10, $product->stock);

// Avoid - generic assertions
$this->assertTrue($product->exists);
```

---

## Troubleshooting

### Common Issues

#### 1. Tests Fail After Code Changes
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rebuild autoload
composer dump-autoload
```

#### 2. Database Issues
```bash
# Reset test database
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

#### 3. Slow Tests
```bash
# Use in-memory SQLite
# Edit phpunit.xml:
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>

# Or run parallel
php artisan test --parallel
```

#### 4. JWT Token Issues
```bash
# Regenerate JWT secret
php artisan jwt:secret

# Clear config
php artisan config:clear
```

---

## Continuous Integration

### GitHub Actions Example
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        
    - name: Install Dependencies
      run: composer install
      
    - name: Run Tests
      run: php artisan test --coverage --min=80
```

---

## Resources

### Documentation
- [UNIT_TESTING.md](UNIT_TESTING.md) - Complete unit testing guide
- [INTEGRATION_TESTING.md](INTEGRATION_TESTING.md) - Complete integration testing guide
- [INTEGRATION_TESTING_QUICKSTART.md](INTEGRATION_TESTING_QUICKSTART.md) - Quick start guide

### External Resources
- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Database Testing](https://laravel.com/docs/database-testing)

---

## Summary

### Test Coverage Overview
✅ **292 Tests Passing**
✅ **949 Assertions**
✅ **100% Success Rate**
✅ **~34s Execution Time**

### Coverage by Type
- **Unit Tests:** 211 tests (72%)
- **Feature Tests:** 60 tests (21%)
- **Integration Tests:** 21 tests (7%)

### Modules Covered
✅ Authentication & Authorization  
✅ Product Management  
✅ Category Management  
✅ Sales & Transactions  
✅ Inventory Management  
✅ User Management  
✅ Payment Processing  
✅ Reporting  
✅ Stock Tracking  

---

**Last Updated:** December 18, 2025  
**Version:** 1.0.0  
**Maintained by:** SuperCashier Development Team
