<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function addSubcategory(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subcategories',
            'category_id' => 'required|exists:categories,id'
        ]);

        Subcategory::create([
            'id' => Str::uuid(),
            'category_id' => $request->category_id,
            'name' => $request->name
        ]);

        return response()->json(['message' => 'Subcategory added successfully']);
    }

    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }

    public function getAllSubcategories()
    {
        $subcategories = Subcategory::get();
        return response()->json($subcategories);
    }
}
