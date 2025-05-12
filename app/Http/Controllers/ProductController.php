<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function addProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price_per_kg' => 'required|numeric|min:1',
            'regular_points' => 'nullable|numeric',
            'pre_order_points' => 'nullable|numeric',
        ]);

        Product::create([
            'id' => Str::uuid(),
            'subcategory_id' => $request->subcategory_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'regular_points' => $request->regular_points,
            'pre_order_points' => $request->pre_order_points,
        ]);

        return response()->json(['message' => 'Product added successfully']);
    }

    public function getProducts($subcategory_id)
    {
        $products = Product::where('subcategory_id', $subcategory_id)->get();
        return response()->json($products);
    }
}
