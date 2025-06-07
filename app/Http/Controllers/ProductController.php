<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
            'product_discount' => 'nullable|numeric|min:0|max:100',
            'product_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048', // 2MB max
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        [$width, $height] = getimagesize($value->getRealPath());
                        if ($width != 800 || $height != 580) {
                            $fail("The {$attribute} must be exactly 800x580 pixels.");
                        }
                    }
                }
            ],
        ]);

        $imagePath = $request->file('product_image')->store('product_images', 'public');

        Product::create([
            'id' => Str::uuid(),
            'subcategory_id' => $request->subcategory_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'regular_points' => $request->regular_points,
            'pre_order_points' => $request->pre_order_points,
            'product_discount' => $request->product_discount ?? 0,
            'product_image' => $imagePath,
        ]);

        return response()->json(['message' => 'Product added successfully']);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price_per_kg' => 'required|numeric|min:1',
            'regular_points' => 'nullable|numeric',
            'pre_order_points' => 'nullable|numeric',
            'product_discount' => 'nullable|numeric|min:0|max:100',
            'product_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        [$width, $height] = getimagesize($value->getRealPath());
                        if ($width != 800 || $height != 580) {
                            $fail("The {$attribute} must be exactly 800x580 pixels.");
                        }
                    }
                }
            ],
        ]);

        $imagePath = $product->product_image;

        if ($request->hasFile('product_image')) {
            // Optionally delete old image
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('product_image')->store('product_images', 'public');
        }

        $product->update([
            'subcategory_id' => $request->subcategory_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'regular_points' => $request->regular_points,
            'pre_order_points' => $request->pre_order_points,
            'product_discount' => $request->product_discount ?? 0,
            'product_image' => $imagePath,
        ]);

        return response()->json(['message' => 'Product updated successfully']);
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
