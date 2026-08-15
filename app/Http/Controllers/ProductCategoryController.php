<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::query()
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        ProductCategory::create($validated);

        return redirect()
            ->route('product-categories.index')
            ->with('success', "Kategori {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:product_categories,name,' . $productCategory->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $productCategory->update($validated);

        return redirect()
            ->route('product-categories.index')
            ->with('success', "Kategori {$productCategory->name} berhasil diperbarui.");
    }

    public function destroy(ProductCategory $productCategory)
    {
        $name = $productCategory->name;
        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', "Kategori {$name} berhasil dihapus.");
    }
}