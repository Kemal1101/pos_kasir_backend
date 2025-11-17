<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'categories_id' => 1,
                'name' => 'Makanan',
                'description' => 'Berbagai jenis makanan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'categories_id' => 2,
                'name' => 'Minuman',
                'description' => 'Minuman dingin & panas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'categories_id' => 3,
                'name' => 'ATK',
                'description' => 'Alat tulis kantor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
