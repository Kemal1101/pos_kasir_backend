<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'categories_id',
            'name',
            'description',
            'cost_price',
            'selling_price',
            'product_images',
            'stock',
            'barcode',
        ];

        $product = new Product();

        $this->assertEquals($fillable, $product->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $product = new Product();

        $this->assertEquals('products', $product->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $product = new Product();

        $this->assertEquals('product_id', $product->getKeyName());
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $category = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Laptop',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 10,
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->categories_id, $product->category->categories_id);
    }

    /** @test */
    public function it_has_many_sale_items()
    {
        // Create role first for user factory
        $role = \App\Models\Role::create(['name' => 'Admin', 'description' => 'Admin']);

        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Laptop',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 10,
        ]);

        // Create user with the role we just created
        $user = \App\Models\User::factory()->withRole($role->role_id)->create();

        $sale = Sale::factory()->create([
            'user_id' => $user->user_id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->sale_id,
            'product_id' => $product->product_id,
            'name_product' => $product->name,
            'quantity' => 2,
            'discount_amount' => 0,
            'subtotal' => 14000000,
        ]);

        $this->assertCount(1, $product->saleItems);
        $this->assertInstanceOf(SaleItem::class, $product->saleItems->first());
    }

    /** @test */
    public function it_can_create_product_with_minimal_data()
    {
        $category = Category::create(['name' => 'Test']);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Test Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'stock' => 10,
        ]);
    }

    /** @test */
    public function it_can_update_stock()
    {
        $category = Category::create(['name' => 'Test']);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Test Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $product->update(['stock' => 20]);

        $this->assertEquals(20, $product->fresh()->stock);
    }

    /** @test */
    public function it_can_increment_stock()
    {
        $category = Category::create(['name' => 'Test']);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Test Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $product->increment('stock', 5);

        $this->assertEquals(15, $product->fresh()->stock);
    }

    /** @test */
    public function it_can_decrement_stock()
    {
        $category = Category::create(['name' => 'Test']);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Test Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $product->decrement('stock', 3);

        $this->assertEquals(7, $product->fresh()->stock);
    }

    /** @test */
    public function it_can_store_product_with_all_attributes()
    {
        $category = Category::create(['name' => 'Electronics']);

        $product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Full Product',
            'description' => 'Complete product description',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'product_images' => 'https://example.com/image.jpg',
            'stock' => 15,
            'barcode' => '1234567890123',
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Full Product',
            'description' => 'Complete product description',
            'barcode' => '1234567890123',
        ]);
    }

    /** @test */
    public function it_eager_loads_category_relationship()
    {
        $category = Category::create(['name' => 'Electronics']);

        Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $product = Product::with('category')->first();

        $this->assertTrue($product->relationLoaded('category'));
        $this->assertEquals('Electronics', $product->category->name);
    }
}
