<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function addCategory(Request $request) {
        $request->validate([
            'name' => 'required|unique:categories',
            'status' => 'nullable|in:Active,Inactive'
        ]);

        Category::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'status' => $request->status ?? 'Active'
        ]);

        return response()->json(['message' => 'Category added successfully']);
    }

    public function getCategories(Request $request) {
        $query = Category::query();
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $categories = $query->get();
        return response()->json($categories);
    }

    public function updateCategoryStatus(Request $request, $id) {
        $request->validate([
            'status' => 'required|in:Active,Inactive'
        ]);

        $category = Category::findOrFail($id);
        $category->update(['status' => $request->status]);

        return response()->json(['message' => 'Category status updated successfully']);
    }
}
