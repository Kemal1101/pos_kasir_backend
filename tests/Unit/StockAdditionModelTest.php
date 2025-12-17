<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockAddition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdditionModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

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
            'stock' => 10,
        ]);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'product_id',
            'user_id',
            'quantity',
            'notes',
            'added_at'
        ];

        $stockAddition = new StockAddition();

        $this->assertEquals($fillable, $stockAddition->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $stockAddition = new StockAddition();

        $this->assertEquals('stock_additions', $stockAddition->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $stockAddition = new StockAddition();

        $this->assertEquals('stock_addition_id', $stockAddition->getKeyName());
    }

    /** @test */
    public function it_casts_added_at_to_datetime()
    {
        $stockAddition = new StockAddition();

        $this->assertArrayHasKey('added_at', $stockAddition->getCasts());
        $this->assertEquals('datetime', $stockAddition->getCasts()['added_at']);
    }

    /** @test */
    public function it_can_create_stock_addition_with_all_fields()
    {
        $addedAt = now();

        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 50,
            'notes' => 'Restock from supplier',
            'added_at' => $addedAt,
        ]);

        $this->assertDatabaseHas('stock_additions', [
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 50,
            'notes' => 'Restock from supplier',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $stockAddition->added_at);
    }

    /** @test */
    public function it_can_create_stock_addition_without_notes()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 25,
            'added_at' => now(),
        ]);

        $this->assertDatabaseHas('stock_additions', [
            'product_id' => $this->product->product_id,
            'quantity' => 25,
        ]);

        $this->assertNull($stockAddition->notes);
    }

    /** @test */
    public function it_belongs_to_product()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $stockAddition->product()
        );

        $this->assertEquals($this->product->product_id, $stockAddition->product->product_id);
        $this->assertEquals('Laptop', $stockAddition->product->name);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $stockAddition->user()
        );

        $this->assertEquals($this->user->user_id, $stockAddition->user->user_id);
        $this->assertEquals('Admin User', $stockAddition->user->name);
    }

    /** @test */
    public function it_can_update_stock_addition()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'notes' => 'Initial stock',
            'added_at' => now(),
        ]);

        $stockAddition->update([
            'quantity' => 20,
            'notes' => 'Updated stock',
        ]);

        $this->assertDatabaseHas('stock_additions', [
            'stock_addition_id' => $stockAddition->stock_addition_id,
            'quantity' => 20,
            'notes' => 'Updated stock',
        ]);
    }

    /** @test */
    public function it_can_delete_stock_addition()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $stockAdditionId = $stockAddition->stock_addition_id;
        $stockAddition->delete();

        $this->assertDatabaseMissing('stock_additions', [
            'stock_addition_id' => $stockAdditionId,
        ]);
    }

    /** @test */
    public function it_handles_small_quantity()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 1,
            'added_at' => now(),
        ]);

        $this->assertEquals(1, $stockAddition->quantity);
    }

    /** @test */
    public function it_handles_large_quantity()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 999999,
            'added_at' => now(),
        ]);

        $this->assertEquals(999999, $stockAddition->quantity);
    }

    /** @test */
    public function it_handles_empty_notes()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'notes' => '',
            'added_at' => now(),
        ]);

        $this->assertEquals('', $stockAddition->notes);
    }

    /** @test */
    public function it_handles_long_notes()
    {
        $longNotes = str_repeat('Stock addition note. ', 50);

        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'notes' => $longNotes,
            'added_at' => now(),
        ]);

        $this->assertEquals($longNotes, $stockAddition->notes);
    }

    /** @test */
    public function it_handles_special_characters_in_notes()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'notes' => 'Stock from supplier @ABC Ltd. (Invoice #12345) - 50% discount!',
            'added_at' => now(),
        ]);

        $this->assertEquals('Stock from supplier @ABC Ltd. (Invoice #12345) - 50% discount!', $stockAddition->notes);
    }

    /** @test */
    public function it_handles_unicode_in_notes()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'notes' => '库存补充 Пополнение запасов',
            'added_at' => now(),
        ]);

        $this->assertEquals('库存补充 Пополнение запасов', $stockAddition->notes);
    }

    /** @test */
    public function it_handles_past_dates()
    {
        $pastDate = now()->subDays(30);

        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => $pastDate,
        ]);

        $this->assertEquals($pastDate->format('Y-m-d H:i:s'), $stockAddition->added_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_handles_future_dates()
    {
        $futureDate = now()->addDays(7);

        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => $futureDate,
        ]);

        $this->assertEquals($futureDate->format('Y-m-d H:i:s'), $stockAddition->added_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_eager_loads_product()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $stockAdditionWithProduct = StockAddition::with('product')->find($stockAddition->stock_addition_id);

        $this->assertTrue($stockAdditionWithProduct->relationLoaded('product'));
        $this->assertEquals('Laptop', $stockAdditionWithProduct->product->name);
    }

    /** @test */
    public function it_eager_loads_user()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $stockAdditionWithUser = StockAddition::with('user')->find($stockAddition->stock_addition_id);

        $this->assertTrue($stockAdditionWithUser->relationLoaded('user'));
        $this->assertEquals('Admin User', $stockAdditionWithUser->user->name);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $stockAddition = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $this->assertNotNull($stockAddition->created_at);
        $this->assertNotNull($stockAddition->updated_at);
    }

    /** @test */
    public function it_can_create_multiple_additions_for_same_product()
    {
        $addition1 = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'notes' => 'First addition',
            'added_at' => now(),
        ]);

        $addition2 = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 20,
            'notes' => 'Second addition',
            'added_at' => now()->addHours(1),
        ]);

        $additions = StockAddition::where('product_id', $this->product->product_id)->get();

        $this->assertCount(2, $additions);
    }

    /** @test */
    public function it_can_track_additions_by_different_users()
    {
        $user2 = User::create([
            'role_id' => $this->user->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        $addition1 = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $this->user->user_id,
            'quantity' => 10,
            'added_at' => now(),
        ]);

        $addition2 = StockAddition::create([
            'product_id' => $this->product->product_id,
            'user_id' => $user2->user_id,
            'quantity' => 15,
            'added_at' => now(),
        ]);

        $this->assertEquals($this->user->user_id, $addition1->user_id);
        $this->assertEquals($user2->user_id, $addition2->user_id);
    }
}
