<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan', 'description' => 'Produk makanan siap jual.'],
            ['name' => 'Minuman', 'description' => 'Minuman kemasan dan siap saji.'],
            ['name' => 'Snack', 'description' => 'Camilan ringan untuk pelanggan.'],
            ['name' => 'Alat Tulis', 'description' => 'Kebutuhan tulis kantor dan sekolah.'],
            ['name' => 'Lainnya', 'description' => 'Produk umum di luar kategori utama.'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
