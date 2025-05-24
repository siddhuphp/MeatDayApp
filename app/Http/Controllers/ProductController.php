<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /** Add Product */
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

    /** View product */
    public function viewProduct($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /** List Products */
    public function listProducts(Request $request)
    {
        $products = Product::paginate($request->get('per_page', 10));
        return response()->json($products);
    }

    /**update product */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:products,name',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price_per_kg' => 'required|numeric|min:1',
            'regular_points' => 'nullable|numeric',
            'pre_order_points' => 'nullable|numeric',
        ]);

        $product->update([
            'subcategory_id' => $request->subcategory_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'regular_points' => $request->regular_points,
            'pre_order_points' => $request->pre_order_points,
        ]);

        return response()->json(['message' => 'Product updated successfully']);
    }

    /** Delete Product */
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /** Get products on selected sub category */
    public function getProductsBySubcategory($subcategory_id)
    {
        $products = Product::where('subcategory_id', $subcategory_id)->paginate(10);
        return response()->json($products);
    }
}
