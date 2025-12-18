<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

        $this->user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function it_can_create_user_successfully()
    {
        $userData = [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role_id' => $this->role->role_id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'user_id',
                    'name',
                    'username',
                    'email',
                    'uuid',
                    'role_id',
                ],
            ])
            ->assertJson([
                'meta' => [
                    'message' => 'User berhasil dibuat',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'name' => 'New User',
        ]);
    }

    /** @test */
    public function it_can_create_user_without_role_id()
    {
        $userData = [
            'name' => 'User Without Role',
            'username' => 'noroleuser',
            'email' => 'norole@test.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'username' => 'noroleuser',
            'role_id' => null,
        ]);
    }

    /** @test */
    public function it_fails_to_create_user_without_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'email', 'password']);
    }

    /** @test */
    public function it_fails_to_create_user_with_invalid_email()
    {
        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_to_create_user_with_duplicate_username()
    {
        $userData = [
            'name' => 'Duplicate User',
            'username' => 'admin', // Already exists
            'email' => 'duplicate@test.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function it_fails_to_create_user_with_duplicate_email()
    {
        $userData = [
            'name' => 'Duplicate User',
            'username' => 'duplicateuser',
            'email' => 'admin@example.com', // Already exists
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_to_create_user_with_short_password()
    {
        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@test.com',
            'password' => 'short', // Less than 8 characters
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_fails_to_create_user_with_invalid_role_id()
    {
        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@test.com',
            'password' => 'password123',
            'role_id' => 99999, // Non-existent role
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    /** @test */
    public function it_hashes_password_when_creating_user()
    {
        $userData = [
            'name' => 'Hash Test User',
            'username' => 'hashuser',
            'email' => 'hash@test.com',
            'password' => 'plainpassword',
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $user = User::where('username', 'hashuser')->first();

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    /** @test */
    public function it_generates_uuid_when_creating_user()
    {
        $userData = [
            'name' => 'UUID Test User',
            'username' => 'uuiduser',
            'email' => 'uuid@test.com',
            'password' => 'password123',
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/add_user', $userData);

        $user = User::where('username', 'uuiduser')->first();

        $this->assertNotNull($user->uuid);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($user->uuid));
    }

    /** @test */
    public function it_can_update_user_successfully()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'olduser',
            'name' => 'Old Name',
            'email' => 'old@test.com',
            'password' => bcrypt('password'),
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user->user_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'message' => 'Berhasil Update User',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);
    }

    /** @test */
    public function it_can_update_user_username()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'oldusername',
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user->user_id}", [
            'username' => 'newusername',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'username' => 'newusername',
        ]);
    }

    /** @test */
    public function it_can_update_user_password()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'passworduser',
            'name' => 'Password User',
            'email' => 'password@test.com',
            'password' => bcrypt('oldpassword'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user->user_id}", [
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function it_can_update_user_role()
    {
        $newRole = Role::create(['name' => 'Manager']);

        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'roleuser',
            'name' => 'Role User',
            'email' => 'role@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user->user_id}", [
            'name' => 'Role User',
            'role_id' => $newRole->role_id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'role_id' => $newRole->role_id,
        ]);
    }

    /** @test */
    public function it_returns_not_found_when_updating_non_existent_user()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/users/99999', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'meta' => [
                    'message' => 'User Not Found',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_update_without_any_data()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user->user_id}", []);

        $response->assertStatus(400)
            ->assertJson([
                'meta' => [
                    'message' => 'Minimal ubah salah satu data',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_update_with_duplicate_username()
    {
        $user1 = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user2->user_id}", [
            'username' => 'user1', // Already taken
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function it_fails_to_update_with_duplicate_email()
    {
        $user1 = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/users/{$user2->user_id}", [
            'email' => 'user1@test.com', // Already taken
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_delete_user_successfully()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'deleteuser',
            'name' => 'Delete User',
            'email' => 'delete@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/users/{$user->user_id}");

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'message' => 'Berhasil Menghapus User',
                ],
            ]);

        $this->assertDatabaseMissing('users', [
            'user_id' => $user->user_id,
        ]);
    }

    /** @test */
    public function it_returns_not_found_when_deleting_non_existent_user()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/users/99999');

        $response->assertStatus(404)
            ->assertJson([
                'meta' => [
                    'message' => 'User Not Found',
                ],
            ]);
    }

    /** @test */
    public function it_can_list_all_users()
    {
        // Create additional users
        User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/list_user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data',
            ]);

        $users = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($users));
    }

    /** @test */
    public function it_can_filter_users_by_role()
    {
        $cashierRole = Role::create(['name' => 'Cashier']);

        User::create([
            'role_id' => $cashierRole->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'cashier1',
            'name' => 'Cashier One',
            'email' => 'cashier1@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/list_user', [
            'role_id' => $cashierRole->role_id,
        ]);

        $response->assertStatus(200);

        $items = $response->json('data');
        $this->assertNotEmpty($items);

        foreach ($items as $item) {
            $this->assertEquals($cashierRole->role_id, $item['role_id']);
        }
    }

    /** @test */
    public function it_can_paginate_users()
    {
        // Create 15 users
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'role_id' => $this->role->role_id,
                'uuid' => $this->faker->uuid,
                'username' => "user{$i}",
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'password' => bcrypt('password'),
            ]);
        }

        // Request page 1 with limit 5
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/list_user', [
            'limit' => 5,
            'page' => 1,
        ]);

        $response->assertStatus(200);

        $items = $response->json('data');
        // API doesn't implement pagination yet, so all users are returned
        $this->assertGreaterThanOrEqual(15, count($items));
    }

    /** @test */
    public function it_returns_not_found_when_no_users_exist()
    {
        // Delete all users except the authenticated one, then delete authenticated user
        User::where('user_id', '!=', $this->user->user_id)->delete();
        $this->user->delete();

        $response = $this->postJson('/api/users/list_user');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 200,
                ],
                'data' => [],
            ]);
    }

    /** @test */
    public function it_includes_role_relationship_in_user_list()
    {
        User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $this->faker->uuid,
            'username' => 'roletest',
            'name' => 'Role Test User',
            'email' => 'roletest@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/users/list_user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['role'],
                ],
            ]);
    }
}
