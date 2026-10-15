<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['creator', 'category'])
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = \App\Models\ProductCategory::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255|unique:products,name',
            'description'         => 'nullable|string|max:1000',
            'product_category_id' => 'nullable|exists:product_categories,id',
        ]);

        Product::create($validated + ['created_by' => auth()->id()]);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        $categories = \App\Models\ProductCategory::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255|unique:products,name,' . $product->id,
            'description'         => 'nullable|string|max:1000',
            'product_category_id' => 'nullable|exists:product_categories,id',
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        // Produk yang sudah terhubung ke data modul tidak boleh dihapus agar relasinya tidak hilang
        $relations = ['formulas', 'trialRms', 'trialPms', 'prfs', 'npdProposals', 'preformulationStudies',
            'sampleEvaluations', 'qbds', 'nieApprovals', 'stabilityTests', 'technologyTransfers', 'approvalForms'];
        $usageCount = collect($relations)->sum(fn ($relation) => $product->{$relation}()->count());

        if ($usageCount > 0) {
            return redirect()
                ->route('products.index')
                ->with('error', "Produk {$product->name} tidak dapat dihapus karena masih terhubung ke {$usageCount} data di modul lain.");
        }

        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$name} berhasil dihapus.");
    }
}