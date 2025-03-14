<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            'Meat & Poultry',
            'Fresh Water Fish (LIVE & ICED)',
            'Sea Fish',
            'Dry Fish',
            'Non-Veg Pickles',
            'Masalas',
            'Order Based Meat Supply'
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'id' => Str::uuid(),
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
