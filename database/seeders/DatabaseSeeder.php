<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Membuat satu akun khusus Admin
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@lumiere.com',
            'password' => Hash::make('password123'), // Password default
            'role' => 'admin',
        ]);

        // 2. Membuat satu akun khusus User (Pelanggan)
        User::factory()->create([
            'name' => 'Pelanggan Setia',
            'email' => 'user@lumiere.com',
            'password' => Hash::make('password123'), // Password default
            'role' => 'user',
        ]);

        // 3. (Opsional) Membuat 10 akun user acak untuk testing UI
        User::factory(10)->create([
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Nanti Anda bisa memanggil seeder lain di sini, contoh:
        // $this->call([
        //     ProductSeeder::class,
        //     CategorySeeder::class,
        // ]);

        // Panggil seeder-seeder yang telah dipisah
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            BannerSeeder::class,
        ]);
    }

}
