<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\HttpResponses;

class ProductController extends Controller
{
    use HttpResponses;

    /** Add Product */
    public function addProduct(Request $request)
    {
        // Check if user has permission to add products
        if (!$request->user()->canManageProducts()) {
            return $this->error(['message' => 'Unauthorized access. Admin or Content Creator role required.'], 'Unauthorized', 403);
        }

        $request->validate([
            'name' => 'required|unique:products,name',
            'category_id' => 'required|exists:categories,id',
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

        $product = Product::create([
            'id' => Str::uuid(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'regular_points' => $request->regular_points,
            'pre_order_points' => $request->pre_order_points,
            'product_discount' => $request->product_discount ?? 0,
            'product_image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product
        ]);
    }

    public function updateProduct(Request $request, $id)
    {
        // Check if user has permission to update products
        if (!$request->user()->canManageProducts()) {
            return $this->error(['message' => 'Unauthorized access. Admin or Content Creator role required.'], 'Unauthorized', 403);
        }

        $product = Product::findOrFail($id);

        try {
            $request->validate([
                'name' => 'required|unique:products,name,' . $id,
                'category_id' => 'required|exists:categories,id',
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed', 422);
        }

        $imagePath = $product->product_image;

        if ($request->hasFile('product_image')) {
            // Optionally delete old image
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('product_image')->store('product_images', 'public');
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'regular_points' => $request->regular_points,
            'pre_order_points' => $request->pre_order_points,
            'product_discount' => $request->product_discount ?? 0,
            'product_image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh()
        ]);
    }

    /** View product */
    public function viewProduct($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    /** List Products */
    public function listProducts(Request $request)
    {
        $products = Product::with('category')->paginate($request->get('per_page', 10));
        return response()->json($products);
    }

    /** Delete Product */
    public function deleteProduct(Request $request, $id)
    {
        // Check if user has permission to delete products (only admin)
        if (!$request->user()->canDeleteProducts()) {
            return $this->error(['message' => 'Unauthorized access. Admin role required to delete products.'], 'Unauthorized', 403);
        }

        $product = Product::findOrFail($id);

        // Delete the product image if it exists
        if ($product->product_image && Storage::disk('public')->exists($product->product_image)) {
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /** Get products on selected category */
    public function getProductsByCategory($category_id)
    {
        $products = Product::with('category')->where('category_id', $category_id)->paginate(10);
        return response()->json($products);
    }

    /** Get all products grouped by active categories for home page */
    public function getProductsForHomePage()
    {
        // Get all active categories with their products
        $categoriesWithProducts = \App\Models\Category::with(['products' => function ($query) {
            $query->orderBy('created_at', 'desc'); // Latest products first
        }])
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        // Transform the data to include category info and product count
        $result = $categoriesWithProducts->map(function ($category) {
            return [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'status' => $category->status,
                    'product_count' => $category->products->count()
                ],
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price_per_kg' => $product->price_per_kg,
                        'regular_points' => $product->regular_points,
                        'pre_order_points' => $product->pre_order_points,
                        'product_discount' => $product->product_discount,
                        'product_image' => $product->product_image ? asset('storage/' . $product->product_image) : null,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at
                    ];
                })
            ];
        });

        return $this->success([
            'categories_with_products' => $result,
            'total_categories' => $result->count(),
            'total_products' => $result->sum('category.product_count')
        ], 'Products retrieved successfully for home page', 200);
    }
}
