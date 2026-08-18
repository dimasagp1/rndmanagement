<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('creator')
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
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Product::create($validated + ['created_by' => auth()->id()]);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$name} berhasil dihapus.");
    }
}