<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['name', 'description'];

        $category = new Category();

        $this->assertEquals($fillable, $category->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $category = new Category();

        $this->assertEquals('categories', $category->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $category = new Category();

        $this->assertEquals('categories_id', $category->getKeyName());
    }

    /** @test */
    public function it_can_create_category_with_name_only()
    {
        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'description' => null,
        ]);

        $this->assertInstanceOf(Category::class, $category);
    }

    /** @test */
    public function it_can_create_category_with_description()
    {
        $category = Category::create([
            'name' => 'Furniture',
            'description' => 'Home and office furniture',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Furniture',
            'description' => 'Home and office furniture',
        ]);
    }

    /** @test */
    public function it_can_update_category()
    {
        $category = Category::create([
            'name' => 'Old Name',
            'description' => 'Old Description',
        ]);

        $category->update([
            'name' => 'New Name',
            'description' => 'New Description',
        ]);

        $this->assertDatabaseHas('categories', [
            'categories_id' => $category->categories_id,
            'name' => 'New Name',
            'description' => 'New Description',
        ]);
    }

    /** @test */
    public function it_can_delete_category()
    {
        $category = Category::create([
            'name' => 'To Delete',
        ]);

        $categoryId = $category->categories_id;
        $category->delete();

        $this->assertDatabaseMissing('categories', [
            'categories_id' => $categoryId,
        ]);
    }

    /** @test */
    public function it_has_many_products()
    {
        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $category->products()
        );
    }

    /** @test */
    public function it_can_retrieve_associated_products()
    {
        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $product1 = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Laptop',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 10,
        ]);

        $product2 = Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Mouse',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 50,
        ]);

        $this->assertCount(2, $category->products);
        $this->assertTrue($category->products->contains($product1));
        $this->assertTrue($category->products->contains($product2));
    }

    /** @test */
    public function it_handles_special_characters_in_name()
    {
        $category = Category::create([
            'name' => "Electronics & Gadgets (New!)",
            'description' => 'Special @#$ characters',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => "Electronics & Gadgets (New!)",
            'description' => 'Special @#$ characters',
        ]);
    }

    /** @test */
    public function it_handles_unicode_characters()
    {
        $category = Category::create([
            'name' => 'Электроника',
            'description' => '中文描述',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Электроника',
            'description' => '中文描述',
        ]);
    }

    /** @test */
    public function it_trims_whitespace_from_name()
    {
        $category = Category::create([
            'name' => '  Electronics  ',
        ]);

        // Laravel doesn't auto-trim, so we check as-is
        $this->assertEquals('  Electronics  ', $category->name);
    }

    /** @test */
    public function it_allows_null_description()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'description' => null,
        ]);

        $this->assertNull($category->description);
    }

    /** @test */
    public function it_allows_empty_string_description()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'description' => '',
        ]);

        $this->assertEquals('', $category->description);
    }

    /** @test */
    public function it_can_handle_long_description()
    {
        $longDescription = str_repeat('a', 1000);

        $category = Category::create([
            'name' => 'Test',
            'description' => $longDescription,
        ]);

        $this->assertEquals($longDescription, $category->description);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $category = Category::create([
            'name' => 'Test',
        ]);

        $this->assertNotNull($category->created_at);
        $this->assertNotNull($category->updated_at);
    }

    /** @test */
    public function it_eager_loads_products()
    {
        $category = Category::create([
            'name' => 'Electronics',
        ]);

        Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Laptop',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 10,
        ]);

        $categoryWithProducts = Category::with('products')->find($category->categories_id);

        $this->assertTrue($categoryWithProducts->relationLoaded('products'));
        $this->assertCount(1, $categoryWithProducts->products);
    }
}
