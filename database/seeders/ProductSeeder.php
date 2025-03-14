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
            ['subcategory' => 'Boiler Kodi', 'name' => 'Whole Chicken', 'price_per_kg' => 220],
            ['subcategory' => 'Natu Kodi', 'name' => 'Country Chicken', 'price_per_kg' => 350],
            ['subcategory' => 'Eggs', 'name' => 'Farm Fresh Eggs (12)', 'price_per_kg' => 120],
            ['subcategory' => 'Mutton', 'name' => 'Goat Meat (With Bone)', 'price_per_kg' => 700],
            ['subcategory' => 'Mutton', 'name' => 'Boneless Mutton', 'price_per_kg' => 850],
            ['subcategory' => 'Bochu', 'name' => 'Live Bochu Fish', 'price_per_kg' => 400],
            ['subcategory' => 'Korameena', 'name' => 'Fresh Korameena Fish', 'price_per_kg' => 550],
            ['subcategory' => 'Vanjram', 'name' => 'Seer Fish (Steaks)', 'price_per_kg' => 950],
            ['subcategory' => 'Tiger Prawns', 'name' => 'Jumbo Tiger Prawns', 'price_per_kg' => 1200],
            ['subcategory' => 'Endu Royyalu', 'name' => 'Dry Prawns', 'price_per_kg' => 850],
            ['subcategory' => 'Chicken Pickle', 'name' => 'Spicy Chicken Pickle', 'price_per_kg' => 500],
            ['subcategory' => 'Chicken Masala', 'name' => 'Special Chicken Masala', 'price_per_kg' => 200],
        ];

        foreach ($products as $product) {
            $subcategory = DB::table('subcategories')->where('name', $product['subcategory'])->first();
            if ($subcategory) {
                DB::table('products')->insert([
                    'id' => Str::uuid(),
                    'subcategory_id' => $subcategory->id,
                    'name' => $product['name'],
                    'price_per_kg' => $product['price_per_kg'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
