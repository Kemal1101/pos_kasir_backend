<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['name', 'description'];

        $role = new Role();

        $this->assertEquals($fillable, $role->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $role = new Role();

        $this->assertEquals('roles', $role->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $role = new Role();

        $this->assertEquals('role_id', $role->getKeyName());
    }

    /** @test */
    public function it_can_create_role_with_name_only()
    {
        $role = Role::create([
            'name' => 'Admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Admin',
            'description' => null,
        ]);

        $this->assertInstanceOf(Role::class, $role);
    }

    /** @test */
    public function it_can_create_role_with_description()
    {
        $role = Role::create([
            'name' => 'Manager',
            'description' => 'Store manager with full access',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager',
            'description' => 'Store manager with full access',
        ]);
    }

    /** @test */
    public function it_can_update_role()
    {
        $role = Role::create([
            'name' => 'Old Role',
            'description' => 'Old Description',
        ]);

        $role->update([
            'name' => 'New Role',
            'description' => 'New Description',
        ]);

        $this->assertDatabaseHas('roles', [
            'role_id' => $role->role_id,
            'name' => 'New Role',
            'description' => 'New Description',
        ]);
    }

    /** @test */
    public function it_can_delete_role()
    {
        $role = Role::create([
            'name' => 'To Delete',
        ]);

        $roleId = $role->role_id;
        $role->delete();

        $this->assertDatabaseMissing('roles', [
            'role_id' => $roleId,
        ]);
    }

    /** @test */
    public function it_has_many_users()
    {
        $role = Role::create([
            'name' => 'Admin',
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $role->users()
        );
    }

    /** @test */
    public function it_can_retrieve_associated_users()
    {
        $role = Role::create([
            'name' => 'Cashier',
        ]);

        $user1 = User::create([
            'role_id' => $role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'role_id' => $role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        $role->refresh();

        $this->assertCount(2, $role->users);
        $this->assertTrue($role->users->contains($user1));
        $this->assertTrue($role->users->contains($user2));
    }

    /** @test */
    public function it_handles_special_characters_in_name()
    {
        $role = Role::create([
            'name' => "Admin & Manager (Special!)",
            'description' => 'Special @#$ characters',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => "Admin & Manager (Special!)",
            'description' => 'Special @#$ characters',
        ]);
    }

    /** @test */
    public function it_handles_unicode_characters()
    {
        $role = Role::create([
            'name' => 'Администратор',
            'description' => '管理员描述',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Администратор',
            'description' => '管理员描述',
        ]);
    }

    /** @test */
    public function it_allows_null_description()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'description' => null,
        ]);

        $this->assertNull($role->description);
    }

    /** @test */
    public function it_allows_empty_string_description()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'description' => '',
        ]);

        $this->assertEquals('', $role->description);
    }

    /** @test */
    public function it_can_handle_long_description()
    {
        $longDescription = str_repeat('a', 1000);

        $role = Role::create([
            'name' => 'Test',
            'description' => $longDescription,
        ]);

        $this->assertEquals($longDescription, $role->description);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $role = Role::create([
            'name' => 'Test',
        ]);

        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
    }

    /** @test */
    public function it_eager_loads_users()
    {
        $role = Role::create([
            'name' => 'Supervisor',
        ]);

        User::create([
            'role_id' => $role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'supervisor1',
            'name' => 'Supervisor One',
            'email' => 'supervisor@test.com',
            'password' => bcrypt('password'),
        ]);

        $roleWithUsers = Role::with('users')->find($role->role_id);

        $this->assertTrue($roleWithUsers->relationLoaded('users'));
        $this->assertCount(1, $roleWithUsers->users);
    }

    /** @test */
    public function it_can_create_multiple_roles()
    {
        $roles = ['Admin', 'Manager', 'Cashier', 'Supervisor'];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        $this->assertCount(4, Role::all());
    }

    /** @test */
    public function it_handles_role_without_users()
    {
        $role = Role::create([
            'name' => 'Empty Role',
        ]);

        $this->assertCount(0, $role->users);
        $this->assertEmpty($role->users);
    }

    /** @test */
    public function it_can_find_role_by_name()
    {
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Cashier']);

        $role = Role::where('name', 'Admin')->first();

        $this->assertNotNull($role);
        $this->assertEquals('Admin', $role->name);
    }
}
