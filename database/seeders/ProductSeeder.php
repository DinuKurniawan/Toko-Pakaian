<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            ['name' => 'Kemeja Linen Premium', 'price' => 299000, 'category' => 'Pria', 'image' => '[https://images.unsplash.com/photo-1596755094514-f87e32f85e2c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80](https://images.unsplash.com/photo-1596755094514-f87e32f85e2c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80)', 'is_featured' => true],
            ['name' => 'Dress Floral Musim Panas', 'price' => 349000, 'category' => 'Wanita', 'image' => '[https://images.unsplash.com/photo-1572804013309-82a89148802a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80](https://images.unsplash.com/photo-1572804013309-82a89148802a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80)', 'is_featured' => true],
            ['name' => 'Jaket Denim Klasik', 'price' => 459000, 'category' => 'Unisex', 'image' => '[https://images.unsplash.com/photo-1576871337622-98d48d1cf531?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80](https://images.unsplash.com/photo-1576871337622-98d48d1cf531?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80)', 'is_featured' => true],
            ['name' => 'Kaos Basic Oversize', 'price' => 149000, 'category' => 'Pria', 'image' => '[https://images.unsplash.com/photo-1581655353564-df123a1eb820?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80](https://images.unsplash.com/photo-1581655353564-df123a1eb820?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80)', 'is_featured' => true]
        ]);
    }
}
