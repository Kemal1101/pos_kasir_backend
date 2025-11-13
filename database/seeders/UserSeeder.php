<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with an admin user.
     */
    public function run(): void
    {
        $now = now();

        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('role_id');
        $kasirRoleId = DB::table('roles')->where('name', 'Kasir')->value('role_id');

        // User 1: Admin
        DB::table('users')->updateOrInsert(
            ['email' => 'valina@gmail.com'], // kunci unik
            [
                'name' => 'Nervalina',
                'username' => 'valina',
                'password' => Hash::make('password'),
                'uuid' => (string) Str::uuid(),
                'role_id' => $adminRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // User 2: Kasir
        DB::table('users')->updateOrInsert(
            ['email' => 'dewi@gmail.com'], // kunci unik
            [
                'name' => 'Dewi',
                'username' => 'dewi',
                'password' => Hash::make('password'),
                'uuid' => (string) Str::uuid(),
                'role_id' => $kasirRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

}
