# 🧪 Unit Testing Guide

Dokumentasi lengkap untuk Unit Testing pada SuperCashier POS System Backend.

---

## 📋 Daftar Isi

- [Overview](#overview)
- [Menjalankan Tests](#menjalankan-tests)
- [Model Tests](#model-tests)
- [Helper Tests](#helper-tests)
- [Coverage](#coverage)

---

## Overview

Unit tests berfokus pada testing komponen individual secara terisolasi. Setiap test memverifikasi bahwa sebuah unit kode (class, method, function) bekerja sesuai yang diharapkan tanpa ketergantungan dengan sistem eksternal.

**Lokasi:** `tests/Unit/`

**Total Tests:** 211 tests dengan 548 assertions

**Tipe Tests:**
- Model Tests (10 files)
- Helper Tests (1 file)

---

## Menjalankan Tests

### Semua Unit Tests
```bash
php artisan test --testsuite=Unit
```

### Test Spesifik
```bash
# Test satu file
php artisan test tests/Unit/ProductModelTest.php

# Test dengan filter
php artisan test --filter=ProductModelTest

# Test dengan coverage
php artisan test --testsuite=Unit --coverage
```

### Menggunakan Test Runner
```bash
# Unit tests only
php run-tests.php --unit

# Dengan coverage
php run-tests.php --unit --coverage
```

---

## Model Tests

### 1. CategoryModelTest
**File:** `tests/Unit/CategoryModelTest.php`  
**Tests:** 17 tests

#### Test Coverage:
- ✅ Model attributes & configuration
  - Fillable attributes
  - Table name
  - Primary key
  - Timestamps
  
- ✅ CRUD operations
  - Create category with name only
  - Create category with description
  - Update category
  - Delete category
  
- ✅ Relationships
  - Has many products
  - Retrieve associated products
  - Eager load products
  
- ✅ Data validation
  - Special characters handling
  - Unicode characters
  - Whitespace trimming
  - Null description
  - Empty string description
  - Long description

**Contoh Test:**
```php
/** @test */
public function it_can_create_category_with_name_only()
{
    $category = Category::create([
        'name' => 'Electronics',
    ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
    ]);
}
```

---

### 2. ProductModelTest
**File:** `tests/Unit/ProductModelTest.php`  
**Tests:** 11 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Table name
  - Primary key
  
- ✅ Relationships
  - Belongs to category
  - Has many sale items
  - Eager loads category
  
- ✅ CRUD & stock operations
  - Create product with minimal data
  - Update stock
  - Increment stock
  - Decrement stock
  - Store product with all attributes

**Contoh Test:**
```php
/** @test */
public function it_can_increment_stock()
{
    $product = Product::factory()->create([
        'stock' => 10,
    ]);

    $product->increment('stock', 5);
    $product->refresh();

    $this->assertEquals(15, $product->stock);
}
```

---

### 3. SaleModelTest
**File:** `tests/Unit/SaleModelTest.php`  
**Tests:** 12 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Table name
  - Primary key
  - Correct casting (decimal, datetime)
  
- ✅ Relationships
  - Belongs to user
  - Belongs to payment
  - Has many items
  - Eager loads relationships
  
- ✅ Business logic
  - Calculate total correctly
  - Draft status
  - Paid status
  - Null payment ID for draft sales

**Contoh Test:**
```php
/** @test */
public function it_can_calculate_total_correctly()
{
    $sale = Sale::factory()->create([
        'subtotal' => 100000,
        'discount_amount' => 10000,
        'tax_amount' => 5000,
        'total_amount' => 95000,
    ]);

    $this->assertEquals(95000, $sale->total_amount);
}
```

---

### 4. SaleItemModelTest
**File:** `tests/Unit/SaleItemModelTest.php`  
**Tests:** 21 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Table name
  - Primary key
  - Decimal casting
  - Timestamps
  
- ✅ Relationships
  - Belongs to sale
  - Belongs to product
  - Eager loading
  
- ✅ CRUD operations
  - Create with all fields
  - Create without discount
  - Update sale item
  - Delete sale item
  
- ✅ Edge cases
  - Zero discount
  - Large quantity
  - Decimal discount amount
  - Special characters in product name
  - Unicode product name
  - Multiple items for same sale

**Contoh Test:**
```php
/** @test */
public function it_handles_zero_discount()
{
    $item = SaleItem::create([
        'sale_id' => 1,
        'product_id' => 1,
        'name_product' => 'Test Product',
        'quantity' => 1,
        'discount_amount' => 0,
        'subtotal' => 50000,
    ]);

    $this->assertEquals(0, $item->discount_amount);
}
```

---

### 5. PaymentModelTest
**File:** `tests/Unit/PaymentModelTest.php`  
**Tests:** 19 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Primary key
  - Array casting for metadata
  - Decimal casting for gross_amount
  - Timestamps
  
- ✅ Relationships
  - Has many sales
  - Retrieve associated sales
  - Eager loads sales
  
- ✅ Data handling
  - Create with all fields
  - Create with minimal data
  - Null metadata
  - Empty array metadata
  - Complex metadata
  - Zero gross amount
  - Large gross amount
  - Different payment types
  - Different transaction statuses

**Contoh Test:**
```php
/** @test */
public function it_handles_complex_metadata()
{
    $metadata = [
        'bank' => 'BCA',
        'account_number' => '1234567890',
        'transaction_id' => 'TRX-001',
        'timestamp' => now()->toDateTimeString(),
    ];

    $payment = Payment::create([
        'payment_type' => 'bank_transfer',
        'transaction_status' => 'settlement',
        'gross_amount' => 100000,
        'metadata' => $metadata,
    ]);

    $this->assertEquals($metadata, $payment->metadata);
}
```

---

### 6. UserModelTest
**File:** `tests/Unit/UserModelTest.php`  
**Tests:** 22 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Primary key
  - Hidden attributes (password, remember_token)
  - Password hashing
  - Timestamps
  
- ✅ Relationships
  - Belongs to role
  - Has many sales
  - Eager loading
  
- ✅ Security
  - Password hashing on create
  - Password not exposed in array
  - JWT identifier
  - JWT custom claims
  
- ✅ Data validation
  - Unique UUID
  - Special characters in name
  - Unicode in name
  - Email format
  - Create without role

**Contoh Test:**
```php
/** @test */
public function it_hashes_password_when_creating_user()
{
    $user = User::create([
        'role_id' => 1,
        'uuid' => 'test-uuid',
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'plain-password',
    ]);

    $this->assertNotEquals('plain-password', $user->password);
    $this->assertTrue(Hash::check('plain-password', $user->password));
}
```

---

### 7. RoleModelTest
**File:** `tests/Unit/RoleModelTest.php`  
**Tests:** 19 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Table name
  - Primary key
  - Timestamps
  
- ✅ Relationships
  - Has many users
  - Retrieve associated users
  - Eager loads users
  - Role without users
  
- ✅ CRUD operations
  - Create with name only
  - Create with description
  - Update role
  - Delete role
  - Create multiple roles
  
- ✅ Data validation
  - Special characters
  - Unicode characters
  - Null description
  - Empty string description
  - Long description
  - Find by name

---

### 8. StockAdditionModelTest
**File:** `tests/Unit/StockAdditionModelTest.php`  
**Tests:** 23 tests

#### Test Coverage:
- ✅ Model configuration
  - Fillable attributes
  - Table name
  - Primary key
  - DateTime casting
  - Timestamps
  
- ✅ Relationships
  - Belongs to product
  - Belongs to user
  - Eager loading
  
- ✅ CRUD operations
  - Create with all fields
  - Create without notes
  - Update stock addition
  - Delete stock addition
  
- ✅ Data validation
  - Small quantity
  - Large quantity
  - Empty notes
  - Long notes
  - Special characters
  - Unicode
  - Past dates
  - Future dates
  - Multiple additions for same product
  - Track additions by different users

---

## Helper Tests

### ResponseHelperTest
**File:** `tests/Unit/ResponseHelperTest.php`  
**Tests:** 21 tests

#### Test Coverage:
- ✅ Success responses
  - With data
  - Without data
  - Custom status code
  - Default message
  - Null data
  - Empty array
  - Nested data structures
  - Type preservation
  
- ✅ Error responses
  - Validation exception
  - Model not found exception
  - Authentication exception
  - Generic exception
  - Without exception
  - Empty validation errors
  - Multiple validation errors
  
- ✅ Special responses
  - Token response
  - Not found response
  - Unauthorized response
  
- ✅ Response structure
  - Consistent structure for success
  - Consistent structure for error
  - JSON response type

**Contoh Test:**
```php
/** @test */
public function it_returns_success_response_with_data()
{
    $data = ['id' => 1, 'name' => 'Test'];
    $response = ResponseHelper::success($data, 'Success message');

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals([
        'meta' => [
            'status' => 200,
            'message' => 'Success message',
        ],
        'data' => $data,
    ], $response->getData(true));
}
```

---

## Coverage

### Menjalankan Coverage Report
```bash
# Generate coverage report
php run-tests.php --unit --coverage

# Coverage dengan minimum threshold
php artisan test --testsuite=Unit --coverage --min=80
```

### Coverage Target
- **Minimum:** 80%
- **Target:** 90%+

### Coverage Report Files
- **HTML:** `coverage/html/index.html`
- **Text:** `coverage/coverage.txt`
- **Clover:** `coverage/clover.xml`

---

## Best Practices

### 1. Naming Convention
```php
/** @test */
public function it_can_do_something()
{
    // Test implementation
}
```

### 2. Arrange-Act-Assert Pattern
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

### 3. Factory Usage
```php
// Gunakan factories untuk test data
$user = User::factory()->create();
$product = Product::factory()->create(['stock' => 10]);
```

### 4. Database Assertions
```php
// Assert database has record
$this->assertDatabaseHas('products', ['name' => 'Laptop']);

// Assert database missing record
$this->assertDatabaseMissing('products', ['name' => 'Deleted']);
```

---

## Troubleshooting

### Test Gagal dengan Database Error
```bash
# Refresh database
php artisan migrate:fresh --env=testing

# Atau gunakan in-memory SQLite di phpunit.xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Slow Tests
```bash
# Run parallel testing
php artisan test --parallel

# Atau gunakan specific test file
php artisan test tests/Unit/ProductModelTest.php
```

### Factory Issues
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Regenerate autoload
composer dump-autoload
```

---

## Quick Reference

| Command | Description |
|---------|-------------|
| `php artisan test --testsuite=Unit` | Run all unit tests |
| `php run-tests.php --unit` | Run with custom script |
| `php artisan test --filter=Product` | Run tests matching "Product" |
| `php artisan test --coverage` | Generate coverage report |
| `php artisan test --parallel` | Run tests in parallel |

---

## Statistik Tests

### Total Coverage
- **Total Tests:** 211
- **Total Assertions:** 548
- **Average Duration:** ~5-6 seconds

### Breakdown by Category
| Category | Tests | Assertions |
|----------|-------|------------|
| Model Tests | 190 | 527 |
| Helper Tests | 21 | 21 |

### Model Tests Detail
| Model | Tests |
|-------|-------|
| CategoryModel | 17 |
| ProductModel | 11 |
| SaleModel | 12 |
| SaleItemModel | 21 |
| PaymentModel | 19 |
| UserModel | 22 |
| RoleModel | 19 |
| StockAdditionModel | 23 |
| ExampleTest | 1 |

---

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Laravel Database Testing](https://laravel.com/docs/database-testing)
- [Laravel Factories](https://laravel.com/docs/eloquent-factories)

---

**Last Updated:** December 18, 2025  
**Version:** 1.0.0
