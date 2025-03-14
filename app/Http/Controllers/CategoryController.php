<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function addCategory(Request $request) {
        $request->validate(['name' => 'required|unique:categories']);

        Category::create([
            'id' => Str::uuid(),
            'name' => $request->name
        ]);

        return response()->json(['message' => 'Category added successfully']);
    }

    public function getCategories() {
        $categories = Category::all();
        return response()->json($categories);
    }
}
