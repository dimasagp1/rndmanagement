<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\MaterialDocument;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with('documents')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $materials = $query->paginate(15)->withQueryString();

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
            'documents' => ['nullable', 'array'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'documents.*.type' => ['nullable', 'string', 'max:100'],
        ]);

        $material = Material::create([
            'name' => $request->name,
            'type' => $request->type,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        if ($request->has('documents')) {
            foreach ($request->documents as $docItem) {
                if (isset($docItem['file']) && $docItem['file']->isValid()) {
                    $file = $docItem['file'];
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
                    $path = $file->store('materials/documents', 'public');

                    $material->documents()->create([
                        'document_type' => $docItem['type'] ?? 'Lainnya',
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_size' => $size,
                    ]);
                }
            }
        }

        return redirect()
            ->route('materials.index')
            ->with('success', "Bahan baku {$material->name} berhasil ditambahkan.");
    }

    public function edit(Material $material)
    {
        $material->load('documents');
        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:materials,name,'.$material->id],
            'type' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'documents.*.type' => ['nullable', 'string', 'max:100'],
        ]);

        $material->update([
            'name' => $request->name,
            'type' => $request->type,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        if ($request->has('documents')) {
            foreach ($request->documents as $docItem) {
                if (isset($docItem['file']) && $docItem['file']->isValid()) {
                    $file = $docItem['file'];
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
                    $path = $file->store('materials/documents', 'public');

                    $material->documents()->create([
                        'document_type' => $docItem['type'] ?? 'Lainnya',
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_size' => $size,
                    ]);
                }
            }
        }

        return redirect()
            ->route('materials.index')
            ->with('success', "Bahan baku {$material->name} berhasil diperbarui.");
    }

    public function destroyDocument(MaterialDocument $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
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
