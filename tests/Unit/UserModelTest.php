<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name',
            'username',
            'email',
            'password',
            'uuid',
            'role_id',
        ];

        $user = new User();

        $this->assertEquals($fillable, $user->getFillable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $user = new User();

        $this->assertEquals('user_id', $user->getKeyName());
    }

    /** @test */
    public function it_hides_password_and_remember_token()
    {
        $user = new User();

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }

    /** @test */
    public function it_casts_password_to_hashed()
    {
        $user = new User();

        $this->assertArrayHasKey('password', $user->getCasts());
        $this->assertEquals('hashed', $user->getCasts()['password']);
    }

    /** @test */
    public function it_can_create_user_with_all_fields()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function it_hashes_password_when_creating_user()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('plainpassword'),
        ]);

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plainpassword', bcrypt('plainpassword')));
    }

    /** @test */
    public function it_belongs_to_role()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $user->role()
        );

        $this->assertEquals($this->role->role_id, $user->role->role_id);
        $this->assertEquals('Admin', $user->role->name);
    }

    /** @test */
    public function it_has_many_sales()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'cashier',
            'name' => 'Cashier User',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->sales()
        );
    }

    /** @test */
    public function it_can_retrieve_associated_sales()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'cashier',
            'name' => 'Cashier User',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
        ]);

        $sale1 = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $sale2 = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 200000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 200000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $user->refresh();

        $this->assertCount(2, $user->sales);
        $this->assertTrue($user->sales->contains($sale1));
        $this->assertTrue($user->sales->contains($sale2));
    }

    /** @test */
    public function it_returns_jwt_identifier()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals($user->user_id, $user->getJWTIdentifier());
    }

    /** @test */
    public function it_returns_jwt_custom_claims()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'customuser',
            'name' => 'Custom User',
            'email' => 'custom@test.com',
            'password' => bcrypt('password'),
        ]);

        $claims = $user->getJWTCustomClaims();

        $this->assertIsArray($claims);
        $this->assertArrayHasKey('username', $claims);
        $this->assertArrayHasKey('role_id', $claims);
        $this->assertEquals('customuser', $claims['username']);
        $this->assertEquals($this->role->role_id, $claims['role_id']);
    }

    /** @test */
    public function it_can_update_user()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'olduser',
            'name' => 'Old Name',
            'email' => 'old@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->update([
            'name' => 'New Name',
            'email' => 'new@test.com',
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'name' => 'New Name',
            'email' => 'new@test.com',
        ]);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'deleteuser',
            'name' => 'Delete User',
            'email' => 'delete@test.com',
            'password' => bcrypt('password'),
        ]);

        $userId = $user->user_id;
        $user->delete();

        $this->assertDatabaseMissing('users', [
            'user_id' => $userId,
        ]);
    }

    /** @test */
    public function it_handles_unique_uuid()
    {
        $uuid1 = \Illuminate\Support\Str::uuid();
        $uuid2 = \Illuminate\Support\Str::uuid();

        $user1 = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $uuid1,
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => $uuid2,
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNotEquals($user1->uuid, $user2->uuid);
    }

    /** @test */
    public function it_handles_special_characters_in_name()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'specialuser',
            'name' => "John O'Brien (Jr.)",
            'email' => 'special@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals("John O'Brien (Jr.)", $user->name);
    }

    /** @test */
    public function it_handles_unicode_in_name()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'unicodeuser',
            'name' => '张三 Иван',
            'email' => 'unicode@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals('张三 Иван', $user->name);
    }

    /** @test */
    public function it_eager_loads_role()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'eageruser',
            'name' => 'Eager User',
            'email' => 'eager@test.com',
            'password' => bcrypt('password'),
        ]);

        $userWithRole = User::with('role')->find($user->user_id);

        $this->assertTrue($userWithRole->relationLoaded('role'));
        $this->assertEquals('Admin', $userWithRole->role->name);
    }

    /** @test */
    public function it_eager_loads_sales()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'salesuser',
            'name' => 'Sales User',
            'email' => 'sales@test.com',
            'password' => bcrypt('password'),
        ]);

        Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $userWithSales = User::with('sales')->find($user->user_id);

        $this->assertTrue($userWithSales->relationLoaded('sales'));
        $this->assertCount(1, $userWithSales->sales);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'timestampuser',
            'name' => 'Timestamp User',
            'email' => 'timestamp@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    /** @test */
    public function it_doesnt_expose_password_in_array()
    {
        $user = User::create([
            'role_id' => $this->role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'secureuser',
            'name' => 'Secure User',
            'email' => 'secure@test.com',
            'password' => bcrypt('password'),
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    /** @test */
    public function it_can_create_user_without_role()
    {
        $user = User::create([
            'role_id' => null,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'norole',
            'name' => 'No Role User',
            'email' => 'norole@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNull($user->role_id);
    }

    /** @test */
    public function it_handles_email_format()
    {
        $emails = [
            'simple@test.com',
            'user+tag@example.com',
            'user.name@domain.co.id',
            'user_name@sub.domain.com',
        ];

        foreach ($emails as $index => $email) {
            $user = User::create([
                'role_id' => $this->role->role_id,
                'uuid' => \Illuminate\Support\Str::uuid(),
                'username' => 'emailuser' . $index,
                'name' => 'Email User ' . $index,
                'email' => $email,
                'password' => bcrypt('password'),
            ]);

            $this->assertEquals($email, $user->email);
        }
    }
}
