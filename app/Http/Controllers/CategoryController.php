<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = Category::all();
        return view('categories', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|min:3|max:255',
        ]);

        Category::create([
            'category_name' => $request->category_name,
        ]);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|min:3|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'category_name' => $request->category_name,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
