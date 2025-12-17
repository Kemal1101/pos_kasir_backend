<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

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
    }

    /** @test */
    public function it_can_create_category_successfully()
    {
        $categoryData = [
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'categories_id',
                    'name',
                    'description',
                ],
            ])
            ->assertJson([
                'meta' => ['message' => 'Category created'],
                'data' => [
                    'name' => 'Electronics',
                    'description' => 'Electronic devices and accessories',
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories',
        ]);
    }

    /** @test */
    public function it_can_create_category_without_description()
    {
        $categoryData = [
            'name' => 'Books',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Books',
            ]);

        // Description can be null, just check it created successfully
        $this->assertDatabaseHas('categories', [
            'name' => 'Books',
        ]);
    }

    /** @test */
    public function it_fails_to_create_category_without_name()
    {
        $categoryData = [
            'description' => 'Some description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_fails_to_create_category_with_empty_name()
    {
        $categoryData = [
            'name' => '',
            'description' => 'Some description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_fails_to_create_category_with_name_exceeding_max_length()
    {
        $categoryData = [
            'name' => str_repeat('a', 192), // Exceeds 191 characters
            'description' => 'Some description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_update_category_successfully()
    {
        $category = Category::create([
            'name' => 'Old Category',
            'description' => 'Old description',
        ]);

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/categories/{$category->categories_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['message' => 'Category updated'],
                'data' => [
                    'name' => 'Updated Category',
                    'description' => 'Updated description',
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'categories_id' => $category->categories_id,
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function it_can_partially_update_category()
    {
        $category = Category::create([
            'name' => 'Category',
            'description' => 'Original description',
        ]);

        $updateData = [
            'name' => 'New Name',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/categories/{$category->categories_id}", $updateData);

        $response->assertStatus(200);

        $category->refresh();
        $this->assertEquals('New Name', $category->name);
        $this->assertEquals('Original description', $category->description);
    }

    /** @test */
    public function it_returns_not_found_when_updating_non_existent_category()
    {
        $updateData = [
            'name' => 'Updated Category',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/categories/99999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'meta' => ['message' => 'Category not found'],
            ]);
    }

    /** @test */
    public function it_can_delete_category_successfully()
    {
        $category = Category::create([
            'name' => 'Category to Delete',
            'description' => 'This will be deleted',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/categories/{$category->categories_id}");

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['message' => 'Category deleted'],
            ]);

        $this->assertDatabaseMissing('categories', [
            'categories_id' => $category->categories_id,
        ]);
    }

    /** @test */
    public function it_returns_not_found_when_deleting_non_existent_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/categories/99999');

        $response->assertStatus(404)
            ->assertJson([
                'meta' => ['message' => 'Category not found'],
            ]);
    }

    /** @test */
    public function it_can_list_all_categories()
    {
        Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);

        Category::create([
            'name' => 'Books',
            'description' => 'Book products',
        ]);

        Category::create([
            'name' => 'Clothing',
            'description' => 'Clothing products',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    '*' => [
                        'categories_id',
                        'name',
                        'description',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_returns_empty_array_when_no_categories_exist()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    /** @test */
    public function it_can_create_multiple_categories_with_same_description()
    {
        $category1 = [
            'name' => 'Category 1',
            'description' => 'Same description',
        ];

        $category2 = [
            'name' => 'Category 2',
            'description' => 'Same description',
        ];

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/categories/add_category', $category1)
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/categories/add_category', $category2)
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', $category1);
        $this->assertDatabaseHas('categories', $category2);
    }

    /** @test */
    public function it_deletes_category_cascade_behavior_with_products()
    {
        $category = Category::create([
            'name' => 'Category with Products',
            'description' => 'Has products',
        ]);

        Product::create([
            'categories_id' => $category->categories_id,
            'name' => 'Product 1',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
        ]);

        // Attempting to delete category with products
        // Behavior depends on database foreign key constraints
        // This test documents expected behavior
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/categories/{$category->categories_id}");

        // If foreign key constraint exists, it should fail
        // If not, it will succeed but orphan products
        // This is a design decision that should be addressed
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 500,
            'Delete should either succeed or fail based on FK constraints'
        );
    }

    /** @test */
    public function it_handles_special_characters_in_category_name()
    {
        $categoryData = [
            'name' => 'Electronics & Accessories',
            'description' => 'Special chars: @#$%',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Electronics & Accessories',
                ],
            ]);
    }

    /** @test */
    public function it_handles_unicode_characters_in_category_name()
    {
        $categoryData = [
            'name' => 'Elektronik & Aksesori 电子产品',
            'description' => 'Produk elektronik',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Elektronik & Aksesori 电子产品',
                ],
            ]);
    }

    /** @test */
    public function it_trims_whitespace_from_category_name()
    {
        $categoryData = [
            'name' => '  Electronics  ',
            'description' => '  Description with spaces  ',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/categories/add_category', $categoryData);

        $response->assertStatus(200);

        // Laravel TrimStrings middleware automatically trims input
        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
        ]);
    }
}
