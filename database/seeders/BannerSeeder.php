<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::insert([
            [
                'title' => 'Koleksi Musim Panas 2026',
                'subtitle' => 'Temukan gaya cerah dan nyaman untuk aktivitas harianmu. Diskon hingga 50% untuk member baru.',
                'image' => '[https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80](https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80)',
                'cta_text' => 'Belanja Sekarang',
                'cta_link' => '#'
            ],
            [
                'title' => 'Gaya Elegan & Profesional',
                'subtitle' => 'Tingkatkan rasa percaya diri Anda dengan koleksi premium kami yang dirancang khusus.',
                'image' => '[https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80](https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80)',
                'cta_text' => 'Lihat Koleksi',
                'cta_link' => '#'
            ]
        ]);
    }
}
