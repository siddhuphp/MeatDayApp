<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $subcategories = [
            'Meat & Poultry' => ['Boiler Kodi', 'Natu Kodi', 'Eggs', 'Mutton', 'Dukudu Mamsam', 'Talakaya', 'Boti'],
            'Fresh Water Fish (LIVE & ICED)' => ['Bochu', 'Sheelavathi', 'Sandhuva', 'Korameena', 'Mittalu', 'Prawns'],
            'Sea Fish' => ['Vanjram', 'Konam', 'Gulivindhalu', 'Kanaganthalu', 'Indian Mackerel', 'Tuna', 'Pachi Netthallu', 'Prawns', 'Tiger Prawns', 'Pithalu', 'Big Crabs'],
            'Dry Fish' => ['Natthallu', 'Endu Royyalu', 'Vanjram', 'Kanaganthalu', 'Gulivindhalu'],
            'Non-Veg Pickles' => ['Chicken Pickle', 'Chicken Boneless Pickle', 'Prawns Pickle', 'Natukodi Pickle', 'Mutton Boneless Pickle', 'Korameena Pickle', 'Vanjram Pickle', 'Konam Pickle', 'Pandugappa Pickle', 'Tiger Prawn Pickle'],
            'Masalas' => ['Chicken Masala', 'Mutton Masala', 'Fish Masala', 'Fish Fry Masala', 'Kabab Masala', 'Biriyani Masala', 'Allam Vellulli Paste'],
            'Order Based Meat Supply' => ['Appollo', 'Pork', 'Kounsu Pitta']
        ];

        foreach ($subcategories as $categoryName => $subcats) {
            $category = DB::table('categories')->where('name', $categoryName)->first();
            if ($category) {
                foreach ($subcats as $subcat) {
                    DB::table('subcategories')->insert([
                        'id' => Str::uuid(),
                        'category_id' => $category->id,
                        'name' => $subcat,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
