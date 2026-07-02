<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::paginate(15);
        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        return view('materials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:materials,name'],
            'type' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $material = Material::create([
            'name' => $request->name,
            'type' => $request->type,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('materials.index')
            ->with('success', "Bahan baku {$material->name} berhasil ditambahkan.");
    }

    public function edit(Material $material)
    {
        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:materials,name,'.$material->id],
            'type' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $material->update([
            'name' => $request->name,
            'type' => $request->type,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('materials.index')
            ->with('success', "Bahan baku {$material->name} berhasil diperbarui.");
    }

    public function destroy(Material $material)
    {
        $name = $material->name;
        $material->delete();

        return redirect()
            ->route('materials.index')
            ->with('success', "Bahan baku {$name} berhasil dihapus.");
    }
}
