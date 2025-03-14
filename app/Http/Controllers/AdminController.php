<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;

class AdminController extends Controller
{
    public function addCategory(Request $request) {
        $request->validate(['name' => 'required|unique:categories']);
        Category::create(['name' => $request->name]);
        return response()->json(['message' => 'Category added successfully']);
    }

    public function addSubcategory(Request $request) {
        $request->validate([
            'name' => 'required|unique:subcategories',
            'category_id' => 'required|exists:categories,id'
        ]);
        Subcategory::create(['name' => $request->name, 'category_id' => $request->category_id]);
        return response()->json(['message' => 'Subcategory added successfully']);
    }

    public function addProduct(Request $request) {
        $request->validate([
            'name' => 'required',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price_per_kg' => 'required|numeric|min:1'
        ]);
        Product::create([
            'name' => $request->name,
            'subcategory_id' => $request->subcategory_id,
            'price_per_kg' => $request->price_per_kg
        ]);
        return response()->json(['message' => 'Product added successfully']);
    }

}
