<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'description' => 'Admin role']);

        $this->user = User::create([
            'role_id' => $role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
        ]);

        $this->token = JWTAuth::fromUser($this->user);

        $this->category = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);
    }

    /** @test */
    public function it_can_create_product_successfully()
    {
        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Laptop Asus',
            'cost_price' => 5000000,
            'selling_price' => 7500000,
            'stock' => 15,
            'description' => 'Asus ROG Gaming Laptop',
            'barcode' => '1234567890123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'product_id',
                    'name',
                    'cost_price',
                    'selling_price',
                    'stock',
                ],
            ])
            ->assertJson([
                'meta' => ['message' => 'Product created'],
                'data' => [
                    'name' => 'Laptop Asus',
                    'stock' => 15,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Laptop Asus',
            'stock' => 15,
            'barcode' => '1234567890123',
        ]);
    }

    /** @test */
    public function it_can_create_product_with_image_upload()
    {
        Storage::fake('public');

        Cloudinary::shouldReceive('upload')
            ->once()
            ->andReturnSelf();

        Cloudinary::shouldReceive('getSecurePath')
            ->once()
            ->andReturn('https://cloudinary.com/products/test-image.jpg');

        $file = UploadedFile::fake()->image('product.jpg');

        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Product with Image',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'product_images' => $file,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'name' => 'Product with Image',
            'product_images' => 'https://cloudinary.com/products/test-image.jpg',
        ]);
    }

    /** @test */
    public function it_fails_to_create_product_without_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'categories_id',
                'name',
                'cost_price',
                'selling_price',
                'stock',
            ]);
    }

    /** @test */
    public function it_fails_to_create_product_with_invalid_category_id()
    {
        $productData = [
            'categories_id' => 99999,
            'name' => 'Invalid Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['categories_id']);
    }

    /** @test */
    public function it_fails_to_create_product_with_negative_price()
    {
        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Invalid Product',
            'cost_price' => -100000,
            'selling_price' => -150000,
            'stock' => 10,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_price', 'selling_price']);
    }

    /** @test */
    public function it_fails_to_create_product_with_negative_stock()
    {
        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Invalid Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => -5,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock']);
    }

    /** @test */
    public function it_can_update_product_successfully()
    {
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Old Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'selling_price' => 200000,
            'stock' => 20,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/products/{$product->product_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['message' => 'Product updated'],
                'data' => [
                    'name' => 'Updated Product',
                    'selling_price' => 200000,
                    'stock' => 20,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'product_id' => $product->product_id,
            'name' => 'Updated Product',
            'stock' => 20,
        ]);
    }

    /** @test */
    public function it_can_update_product_with_new_image()
    {
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'product_images' => 'old-image.jpg',
        ]);

        Cloudinary::shouldReceive('upload')
            ->once()
            ->andReturnSelf();

        Cloudinary::shouldReceive('getSecurePath')
            ->once()
            ->andReturn('https://cloudinary.com/products/new-image.jpg');

        $file = UploadedFile::fake()->image('new-product.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/products/{$product->product_id}", [
            'product_images' => $file,
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals('https://cloudinary.com/products/new-image.jpg', $product->product_images);
    }

    /** @test */
    public function it_returns_not_found_when_updating_non_existent_product()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/products/99999', [
            'name' => 'Updated Product',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'meta' => ['message' => 'Product not found'],
            ]);
    }

    /** @test */
    public function it_can_delete_product_successfully()
    {
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product to Delete',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/products/{$product->product_id}");

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['message' => 'Product deleted'],
            ]);

        $this->assertDatabaseMissing('products', [
            'product_id' => $product->product_id,
        ]);
    }

    /** @test */
    public function it_returns_not_found_when_deleting_non_existent_product()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/products/99999');

        $response->assertStatus(404)
            ->assertJson([
                'meta' => ['message' => 'Product not found'],
            ]);
    }

    /** @test */
    public function it_can_add_stock_to_product()
    {
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/products/{$product->product_id}/add_stock", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['message' => 'Stock added successfully'],
                'data' => [
                    'stock' => 15,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'product_id' => $product->product_id,
            'stock' => 15,
        ]);
    }

    /** @test */
    public function it_fails_to_add_stock_with_invalid_quantity()
    {
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/products/{$product->product_id}/add_stock", [
            'quantity' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_fails_to_add_stock_to_non_existent_product()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/99999/add_stock', [
            'quantity' => 5,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'meta' => ['message' => 'Product not found'],
            ]);
    }

    /** @test */
    public function it_can_list_all_products()
    {
        Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product 1',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Product 2',
            'cost_price' => 200000,
            'selling_price' => 250000,
            'stock' => 5,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta',
                'data' => [
                    '*' => [
                        'product_id',
                        'name',
                        'selling_price',
                        'stock',
                        'category',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_filter_products_by_category()
    {
        $category2 = Category::create([
            'name' => 'Books',
            'description' => 'Book products',
        ]);

        Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Laptop',
            'cost_price' => 5000000,
            'selling_price' => 7000000,
            'stock' => 10,
        ]);

        Product::create([
            'categories_id' => $category2->categories_id,
            'name' => 'Novel',
            'cost_price' => 50000,
            'selling_price' => 75000,
            'stock' => 20,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products?category_id=' . $category2->categories_id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Novel',
                        'categories_id' => $category2->categories_id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_get_product_detail()
    {
        $product = Product::create([
            'categories_id' => $this->category->categories_id,
            'name' => 'Detailed Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'description' => 'Product description',
            'barcode' => '1234567890',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/products/{$product->product_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta',
                'data' => [
                    'product_id',
                    'name',
                    'description',
                    'cost_price',
                    'selling_price',
                    'stock',
                    'barcode',
                    'category',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Detailed Product',
                    'barcode' => '1234567890',
                ],
            ]);
    }

    /** @test */
    public function it_returns_not_found_for_non_existent_product_detail()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_product_with_zero_stock()
    {
        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Out of Stock Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 0,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'name' => 'Out of Stock Product',
            'stock' => 0,
        ]);
    }

    /** @test */
    public function it_rejects_invalid_image_file_types()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'product_images' => $file,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_images']);
    }

    /** @test */
    public function it_rejects_oversized_image_files()
    {
        $file = UploadedFile::fake()->image('large-image.jpg')->size(3000); // 3MB

        $productData = [
            'categories_id' => $this->category->categories_id,
            'name' => 'Product',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'product_images' => $file,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products/add_product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_images']);
    }
}
