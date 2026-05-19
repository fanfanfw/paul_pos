<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@kasirku.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kasir@kasirku.com'],
            [
                'name' => 'Kasir Demo',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'is_active' => true,
            ]
        );
    }
}
