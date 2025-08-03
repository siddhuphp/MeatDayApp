<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $products = [
            ['category' => 'Meat & Poultry', 'name' => 'Whole Chicken', 'price_per_kg' => 220],
            ['category' => 'Meat & Poultry', 'name' => 'Country Chicken', 'price_per_kg' => 350],
            ['category' => 'Meat & Poultry', 'name' => 'Farm Fresh Eggs (12)', 'price_per_kg' => 120],
            ['category' => 'Meat & Poultry', 'name' => 'Goat Meat (With Bone)', 'price_per_kg' => 700],
            ['category' => 'Meat & Poultry', 'name' => 'Boneless Mutton', 'price_per_kg' => 850],
            ['category' => 'Fresh Water Fish (LIVE & ICED)', 'name' => 'Live Bochu Fish', 'price_per_kg' => 400],
            ['category' => 'Fresh Water Fish (LIVE & ICED)', 'name' => 'Fresh Korameena Fish', 'price_per_kg' => 550],
            ['category' => 'Sea Fish', 'name' => 'Seer Fish (Steaks)', 'price_per_kg' => 950],
            ['category' => 'Sea Fish', 'name' => 'Jumbo Tiger Prawns', 'price_per_kg' => 1200],
            ['category' => 'Dry Fish', 'name' => 'Dry Prawns', 'price_per_kg' => 850],
            ['category' => 'Non-Veg Pickles', 'name' => 'Spicy Chicken Pickle', 'price_per_kg' => 500],
            ['category' => 'Masalas', 'name' => 'Special Chicken Masala', 'price_per_kg' => 200],
        ];

        foreach ($products as $product) {
            $category = DB::table('categories')->where('name', $product['category'])->first();
            if ($category) {
                DB::table('products')->insert([
                    'id' => Str::uuid(),
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'price_per_kg' => $product['price_per_kg'],
                    'regular_points' => 100,
                    'pre_order_points' => 150,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
