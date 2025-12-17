<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleItemModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $sale;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin']);

        $this->user = User::create([
            'role_id' => $role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $this->product = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Laptop',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 100,
        ]);

        $this->sale = Sale::create([
            'user_id' => $this->user->user_id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'sale_id',
            'product_id',
            'name_product',
            'quantity',
            'discount_amount',
            'subtotal',
        ];

        $saleItem = new SaleItem();

        $this->assertEquals($fillable, $saleItem->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $saleItem = new SaleItem();

        $this->assertEquals('sale_items', $saleItem->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $saleItem = new SaleItem();

        $this->assertEquals('sale_item_id', $saleItem->getKeyName());
    }

    /** @test */
    public function it_casts_discount_amount_to_decimal()
    {
        $saleItem = new SaleItem();

        $this->assertArrayHasKey('discount_amount', $saleItem->getCasts());
        $this->assertEquals('decimal:2', $saleItem->getCasts()['discount_amount']);
    }

    /** @test */
    public function it_casts_subtotal_to_decimal()
    {
        $saleItem = new SaleItem();

        $this->assertArrayHasKey('subtotal', $saleItem->getCasts());
        $this->assertEquals('decimal:2', $saleItem->getCasts()['subtotal']);
    }

    /** @test */
    public function it_can_create_sale_item_with_all_fields()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 2,
            'discount_amount' => 500000,
            'subtotal' => 13500000,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 2,
        ]);

        $this->assertEquals(500000, $saleItem->discount_amount);
        $this->assertEquals(13500000, $saleItem->subtotal);
    }

    /** @test */
    public function it_can_create_sale_item_without_discount()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertEquals(0, $saleItem->discount_amount);
        $this->assertEquals(7000000, $saleItem->subtotal);
    }

    /** @test */
    public function it_belongs_to_sale()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $saleItem->sale()
        );

        $this->assertEquals($this->sale->sale_id, $saleItem->sale->sale_id);
    }

    /** @test */
    public function it_belongs_to_product()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $saleItem->product()
        );

        $this->assertEquals($this->product->product_id, $saleItem->product->product_id);
    }

    /** @test */
    public function it_can_update_sale_item()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $saleItem->update([
            'quantity' => 3,
            'subtotal' => 21000000,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_item_id' => $saleItem->sale_item_id,
            'quantity' => 3,
            'subtotal' => 21000000,
        ]);
    }

    /** @test */
    public function it_can_delete_sale_item()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $saleItemId = $saleItem->sale_item_id;
        $saleItem->delete();

        $this->assertDatabaseMissing('sale_items', [
            'sale_item_id' => $saleItemId,
        ]);
    }

    /** @test */
    public function it_handles_zero_discount()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertEquals(0, $saleItem->discount_amount);
    }

    /** @test */
    public function it_handles_large_quantity()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1000,
            'discount_amount' => 0,
            'subtotal' => 7000000000,
        ]);

        $this->assertEquals(1000, $saleItem->quantity);
        $this->assertEquals(7000000000, $saleItem->subtotal);
    }

    /** @test */
    public function it_handles_decimal_discount_amount()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 250000.50,
            'subtotal' => 6749999.50,
        ]);

        $this->assertEquals(250000.50, $saleItem->discount_amount);
        $this->assertEquals(6749999.50, $saleItem->subtotal);
    }

    /** @test */
    public function it_handles_special_characters_in_product_name()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop Dell™ XPS 15" (2024)',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertEquals('Laptop Dell™ XPS 15" (2024)', $saleItem->name_product);
    }

    /** @test */
    public function it_handles_unicode_product_name()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => '笔记本电脑 Ноутбук',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertEquals('笔记本电脑 Ноутбук', $saleItem->name_product);
    }

    /** @test */
    public function it_eager_loads_sale()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $saleItemWithSale = SaleItem::with('sale')->find($saleItem->sale_item_id);

        $this->assertTrue($saleItemWithSale->relationLoaded('sale'));
        $this->assertEquals($this->sale->sale_id, $saleItemWithSale->sale->sale_id);
    }

    /** @test */
    public function it_eager_loads_product()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $saleItemWithProduct = SaleItem::with('product')->find($saleItem->sale_item_id);

        $this->assertTrue($saleItemWithProduct->relationLoaded('product'));
        $this->assertEquals($this->product->product_id, $saleItemWithProduct->product->product_id);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertNotNull($saleItem->created_at);
        $this->assertNotNull($saleItem->updated_at);
    }

    /** @test */
    public function it_can_create_multiple_items_for_same_sale()
    {
        $product2 = Product::create([
            'categories_id' => $this->product->categories_id,
            'name' => 'Mouse',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 50,
        ]);

        $saleItem1 = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $saleItem2 = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $product2->product_id,
            'name_product' => 'Mouse',
            'quantity' => 2,
            'discount_amount' => 0,
            'subtotal' => 300000,
        ]);

        $this->assertCount(2, $this->sale->items);
    }

    /** @test */
    public function it_handles_zero_discount_as_minimum()
    {
        $saleItem = SaleItem::create([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->product->product_id,
            'name_product' => 'Laptop',
            'quantity' => 1,
            'discount_amount' => 0,
            'subtotal' => 7000000,
        ]);

        $this->assertEquals(0, $saleItem->discount_amount);
    }
}
