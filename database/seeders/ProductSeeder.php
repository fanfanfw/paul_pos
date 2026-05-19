<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@kasirku.com')->first();
        $categories = Category::query()->pluck('id', 'name');

        $products = [
            ['code' => 'MKN001', 'name' => 'Nasi Goreng Instan', 'category' => 'Makanan', 'price' => 18000, 'cost_price' => 12000, 'stock' => 30, 'min_stock' => 5],
            ['code' => 'MKN002', 'name' => 'Mie Goreng Cup', 'category' => 'Makanan', 'price' => 7500, 'cost_price' => 5000, 'stock' => 48, 'min_stock' => 10],
            ['code' => 'MKN003', 'name' => 'Roti Cokelat', 'category' => 'Makanan', 'price' => 6500, 'cost_price' => 4000, 'stock' => 25, 'min_stock' => 8],
            ['code' => 'MKN004', 'name' => 'Sarden Kaleng', 'category' => 'Makanan', 'price' => 15500, 'cost_price' => 11000, 'stock' => 20, 'min_stock' => 5],
            ['code' => 'MNM001', 'name' => 'Air Mineral 600ml', 'category' => 'Minuman', 'price' => 4000, 'cost_price' => 2500, 'stock' => 96, 'min_stock' => 24],
            ['code' => 'MNM002', 'name' => 'Teh Botol', 'category' => 'Minuman', 'price' => 6000, 'cost_price' => 4000, 'stock' => 60, 'min_stock' => 12],
            ['code' => 'MNM003', 'name' => 'Kopi Susu Kaleng', 'category' => 'Minuman', 'price' => 8500, 'cost_price' => 6000, 'stock' => 42, 'min_stock' => 10],
            ['code' => 'MNM004', 'name' => 'Susu UHT Cokelat', 'category' => 'Minuman', 'price' => 7000, 'cost_price' => 4800, 'stock' => 36, 'min_stock' => 10],
            ['code' => 'SNK001', 'name' => 'Keripik Singkong', 'category' => 'Snack', 'price' => 9000, 'cost_price' => 6000, 'stock' => 40, 'min_stock' => 8],
            ['code' => 'SNK002', 'name' => 'Biskuit Kelapa', 'category' => 'Snack', 'price' => 12000, 'cost_price' => 8500, 'stock' => 32, 'min_stock' => 8],
            ['code' => 'SNK003', 'name' => 'Cokelat Bar', 'category' => 'Snack', 'price' => 10000, 'cost_price' => 7000, 'stock' => 28, 'min_stock' => 8],
            ['code' => 'SNK004', 'name' => 'Kacang Panggang', 'category' => 'Snack', 'price' => 11000, 'cost_price' => 7800, 'stock' => 24, 'min_stock' => 6],
            ['code' => 'ATK001', 'name' => 'Pulpen Hitam', 'category' => 'Alat Tulis', 'price' => 3500, 'cost_price' => 2000, 'stock' => 80, 'min_stock' => 15],
            ['code' => 'ATK002', 'name' => 'Pensil 2B', 'category' => 'Alat Tulis', 'price' => 3000, 'cost_price' => 1800, 'stock' => 70, 'min_stock' => 15],
            ['code' => 'ATK003', 'name' => 'Buku Tulis 38 Lembar', 'category' => 'Alat Tulis', 'price' => 5500, 'cost_price' => 3500, 'stock' => 55, 'min_stock' => 12],
            ['code' => 'ATK004', 'name' => 'Penghapus Putih', 'category' => 'Alat Tulis', 'price' => 2500, 'cost_price' => 1200, 'stock' => 45, 'min_stock' => 10],
            ['code' => 'LNY001', 'name' => 'Tisu Wajah', 'category' => 'Lainnya', 'price' => 13500, 'cost_price' => 9500, 'stock' => 26, 'min_stock' => 6],
            ['code' => 'LNY002', 'name' => 'Sabun Cuci Piring', 'category' => 'Lainnya', 'price' => 14000, 'cost_price' => 9800, 'stock' => 22, 'min_stock' => 5],
            ['code' => 'LNY003', 'name' => 'Masker Kain', 'category' => 'Lainnya', 'price' => 8000, 'cost_price' => 4500, 'stock' => 35, 'min_stock' => 8],
            ['code' => 'LNY004', 'name' => 'Korek Api', 'category' => 'Lainnya', 'price' => 2500, 'cost_price' => 1500, 'stock' => 50, 'min_stock' => 10],
        ];

        foreach ($products as $item) {
            $product = Product::query()->updateOrCreate(
                ['code' => strtoupper($item['code'])],
                [
                    'category_id' => $categories[$item['category']] ?? null,
                    'name' => $item['name'],
                    'description' => null,
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'image' => null,
                    'is_active' => true,
                ]
            );

            $product->stock()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => $item['stock'],
                    'min_quantity' => $item['min_stock'],
                ]
            );

            StockMovement::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'reference' => 'SEED-INITIAL',
                ],
                [
                    'user_id' => $admin?->id,
                    'quantity' => $item['stock'],
                    'before_quantity' => 0,
                    'after_quantity' => $item['stock'],
                    'notes' => 'Stok awal dari seeder.',
                ]
            );
        }
    }
}
