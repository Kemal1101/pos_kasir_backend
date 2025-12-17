<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
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
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567890',
        ]);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'token',
                    'user' => [
                        'user_id',
                        'name',
                        'email',
                        'username',
                    ],
                    'expires_in',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function it_fails_to_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah',
            ]);
    }

    /** @test */
    public function it_fails_to_login_with_invalid_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah',
            ]);
    }

    /** @test */
    public function it_fails_to_login_without_credentials()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah',
            ]);
    }

    /** @test */
    public function it_fails_to_login_with_empty_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah',
            ]);
    }

    /** @test */
    public function it_can_get_authenticated_user_profile()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'user_id',
                    'name',
                    'email',
                    'username',
                    'role_id',
                ],
            ])
            ->assertJson([
                'data' => [
                    'email' => 'test@example.com',
                    'name' => 'Test User',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_get_profile_without_token()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_fails_to_get_profile_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-here',
        ])->getJson('/api/me');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_logout_successfully()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'message' => 'Logout berhasil',
                ],
            ]);

        // Verify token is invalidated
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me')
            ->assertStatus(401);
    }

    /** @test */
    public function it_fails_to_logout_without_token()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_refresh_token_successfully()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['status', 'message'],
                'data' => [
                    'token',
                    'user',
                    'expires_in',
                ],
            ]);

        $newToken = $response->json('data.token');
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($token, $newToken);
    }

    /** @test */
    public function it_fails_to_refresh_without_token()
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_fails_to_refresh_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->postJson('/api/refresh');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_correct_token_expiration_time()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $expiresIn = $response->json('data.expires_in');
        $expectedTTL = JWTAuth::factory()->getTTL() * 60;

        $this->assertEquals($expectedTTL, $expiresIn);
    }

    /** @test */
    public function it_can_use_refreshed_token_for_authentication()
    {
        $token = JWTAuth::fromUser($this->user);

        $refreshResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/refresh');

        $newToken = $refreshResponse->json('data.token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newToken,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'email' => 'test@example.com',
                ],
            ]);
    }

    /** @test */
    public function it_includes_user_relationships_in_profile()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'role_id',
                    'uuid',
                    'username',
                    'name',
                    'email',
                ],
            ]);
    }

    /** @test */
    public function it_does_not_expose_password_in_profile()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200);

        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    /** @test */
    public function it_can_login_multiple_times_and_generate_different_tokens()
    {
        $response1 = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token1 = $response1->json('data.token');

        $response2 = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token2 = $response2->json('data.token');

        $this->assertNotEquals($token1, $token2);
    }

    /** @test */
    public function it_handles_case_sensitive_email_correctly()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'password123',
        ]);

        // Laravel's default auth is case-insensitive for email
        // So uppercase email should work
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);
    }

    /** @test */
    public function it_trims_whitespace_from_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => '  test@example.com  ',
            'password' => 'password123',
        ]);

        // Laravel TrimStrings middleware automatically trims input
        // So whitespace should be handled and login should succeed
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);
    }

    /** @test */
    public function it_prevents_sql_injection_in_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => "' OR '1'='1",
            'password' => "' OR '1'='1",
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Email atau password salah',
            ]);
    }
}
