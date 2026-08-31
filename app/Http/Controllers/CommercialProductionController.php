<?php

namespace App\Http\Controllers;

use App\Models\CommercialProductionAudit;
use App\Models\CommercialProductionDocument;
use App\Models\CommercialProductionDocumentVersion;
use App\Models\CommercialProductionFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommercialProductionController extends Controller
{
    // ── Index: folder view + tree + search ──
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $search   = $request->get('search');

        $currentFolder = $folderId ? CommercialProductionFolder::with('parent')->find($folderId) : null;

        if ($folderId && ! $currentFolder) {
            abort(404, 'Folder tidak ditemukan.');
        }

        $breadcrumbs = $currentFolder ? $currentFolder->breadcrumbs() : [];

        // Tree for sidebar
        $tree = CommercialProductionFolder::tree();

        // Folders in current level
        $foldersQuery = CommercialProductionFolder::where('parent_id', $folderId)->with('creator')->orderBy('name');

        // Documents in current folder
        $documentsQuery = CommercialProductionDocument::where('folder_id', $folderId)->with('uploader', 'folder')->orderByDesc('updated_at');

        if ($search) {
            $searchLower = strtolower($search);
            // If search, search across all folders/documents
            $foldersQuery = CommercialProductionFolder::where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->with('creator')->orderBy('name');

            $documentsQuery = CommercialProductionDocument::where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('extension', 'like', "%{$search}%");
            })->with('uploader', 'folder')->orderByDesc('updated_at');

            // Also filter by type, date, uploader if needed
            if ($request->get('type')) {
                $documentsQuery->where('extension', $request->get('type'));
            }
            if ($request->get('uploader')) {
                $documentsQuery->where('uploaded_by', $request->get('uploader'));
            }
        }

        // Type filter
        if ($request->get('type') && ! $search) {
            $documentsQuery->where('extension', $request->get('type'));
        }

        $folders   = $foldersQuery->get();
        $documents = $documentsQuery->paginate(20)->withQueryString();

        // Stats
        $totalFolders   = CommercialProductionFolder::count();
        $totalDocuments = CommercialProductionDocument::count();
        $totalSize      = CommercialProductionDocument::sum('file_size');

        // Audits recent
        $recentAudits = CommercialProductionAudit::with('user')->latest()->take(10)->get();

        // For move dropdown: all folders flat
        $allFolders = CommercialProductionFolder::orderBy('name')->get();

        return view('commercial-productions.index', compact(
            'currentFolder',
            'breadcrumbs',
            'tree',
            'folders',
            'documents',
            'totalFolders',
            'totalDocuments',
            'totalSize',
            'recentAudits',
            'allFolders'
        ));
    }

    // ── Store Folder ──
    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id'   => 'nullable|exists:commercial_production_folders,id',
        ]);

        $folder = CommercialProductionFolder::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        CommercialProductionAudit::log('Folder dibuat', $folder, "Folder \"{$folder->name}\" dibuat" . ($folder->parent_id ? " di dalam \"{$folder->parent->name}\"" : " di root"));

        return back()->with('success', "Folder \"{$folder->name}\" berhasil dibuat.");
    }

    // ── Update Folder (rename) ──
    public function updateFolder(Request $request, CommercialProductionFolder $folder)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id'   => 'nullable|exists:commercial_production_folders,id',
        ]);

        // Prevent moving into own descendant
        if (! empty($validated['parent_id'])) {
            $descendantIds = $folder->allDescendantIds();
            if (in_array($validated['parent_id'], $descendantIds)) {
                return back()->with('error', 'Tidak dapat memindahkan folder ke dalam sub-foldernya sendiri.');
            }
            if ((int) $validated['parent_id'] === (int) $folder->id) {
                return back()->with('error', 'Tidak dapat memindahkan folder ke dirinya sendiri.');
            }
        }

        $oldName = $folder->name;
        $folder->update($validated);

        CommercialProductionAudit::log('Folder diubah', $folder, "Folder \"{$oldName}\" diubah menjadi \"{$folder->name}\"");

        return back()->with('success', "Folder \"{$oldName}\" berhasil diperbarui.");
    }

    // ── Destroy Folder ──
    public function destroyFolder(CommercialProductionFolder $folder)
    {
        $hasChildren = $folder->children()->exists();
        $hasDocuments = $folder->documents()->exists();

        if ($hasChildren || $hasDocuments) {
            return back()->with('error', 'Folder tidak dapat dihapus karena masih berisi sub-folder atau dokumen. Pindahkan atau hapus isinya terlebih dahulu.');
        }

        $name = $folder->name;
        $folder->delete();

        CommercialProductionAudit::log('Folder dihapus', null, "Folder \"{$name}\" dihapus");

        return back()->with('success', "Folder \"{$name}\" berhasil dihapus.");
    }

    // ── Store Document (upload) ──
    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'folder_id'   => 'nullable|exists:commercial_production_folders,id',
            'files'       => 'required|array|min:1',
            'files.*'     => 'file|max:102400|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,zip,rar,txt',
            'description' => 'nullable|string|max:1000',
        ]);

        $uploaded = 0;
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension    = strtolower($file->getClientOriginalExtension());
            $mimeType     = $file->getMimeType();
            $fileSize     = $file->getSize();
            $fileName     = Str::uuid() . '.' . $extension;
            $filePath     = $file->storeAs('commercial-productions/' . ($validated['folder_id'] ?? 'root'), $fileName, 'public');

            // Check duplicate name in same folder -> create version instead of duplicate entry
            $existing = CommercialProductionDocument::where('folder_id', $validated['folder_id'] ?? null)
                ->where('original_name', $originalName)
                ->first();

            if ($existing) {
                // Save current as version
                CommercialProductionDocumentVersion::create([
                    'commercial_production_document_id' => $existing->id,
                    'version'                => $existing->version,
                    'file_path'              => $existing->file_path,
                    'file_name'              => $existing->file_name,
                    'file_size'              => $existing->file_size,
                    'mime_type'              => $existing->mime_type,
                    'uploaded_by'            => $existing->uploaded_by,
                ]);

                $existing->update([
                    'file_name'   => $fileName,
                    'file_path'   => $filePath,
                    'mime_type'   => $mimeType,
                    'file_size'   => $fileSize,
                    'extension'   => $extension,
                    'version'     => $existing->version + 1,
                    'uploaded_by' => auth()->id(),
                    'description' => $validated['description'] ?? $existing->description,
                ]);

                CommercialProductionAudit::log('Dokumen versi baru', $existing, "Versi {$existing->version} dari \"{$originalName}\" diunggah");
            } else {
                $doc = CommercialProductionDocument::create([
                    'folder_id'     => $validated['folder_id'] ?? null,
                    'original_name' => $originalName,
                    'file_name'     => $fileName,
                    'file_path'     => $filePath,
                    'mime_type'     => $mimeType,
                    'file_size'     => $fileSize,
                    'extension'     => $extension,
                    'version'       => 1,
                    'description'   => $validated['description'] ?? null,
                    'uploaded_by'   => auth()->id(),
                ]);

                CommercialProductionAudit::log('File di-upload', $doc, "File \"{$originalName}\" diunggah" . ($validated['folder_id'] ? " ke folder \"{$doc->folder->name}\"" : " di root"));
            }

            $uploaded++;
        }

        return back()->with('success', "{$uploaded} file berhasil diunggah.");
    }

    // ── Show Document Detail ──
    public function showDocument(CommercialProductionDocument $document)
    {
        $document->load(['folder', 'uploader', 'versions.uploader']);

        $breadcrumbs = $document->folder ? $document->folder->breadcrumbs() : [];

        return view('commercial-productions.show', compact('document', 'breadcrumbs'));
    }

    // ── Update Document (rename / description) ──
    public function updateDocument(Request $request, CommercialProductionDocument $document)
    {
        $validated = $request->validate([
            'original_name' => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
        ]);

        $oldName = $document->original_name;
        $document->update($validated);

        CommercialProductionAudit::log('File diubah', $document, "File \"{$oldName}\" diubah menjadi \"{$document->original_name}\"");

        return back()->with('success', "File \"{$oldName}\" berhasil diperbarui.");
    }

    // ── Move Document ──
    public function moveDocument(Request $request, CommercialProductionDocument $document)
    {
        $validated = $request->validate([
            'folder_id' => 'nullable|exists:commercial_production_folders,id',
        ]);

        $oldFolder = $document->folder?->name ?? 'Root';
        $document->update(['folder_id' => $validated['folder_id']]);

        $newFolder = $document->fresh()->folder?->name ?? 'Root';

        CommercialProductionAudit::log('File dipindahkan', $document, "File \"{$document->original_name}\" dipindahkan dari \"{$oldFolder}\" ke \"{$newFolder}\"");

        return back()->with('success', "File \"{$document->original_name}\" dipindahkan ke \"{$newFolder}\".");
    }

    // ── Destroy Document ──
    public function destroyDocument(CommercialProductionDocument $document)
    {
        $name = $document->original_name;
        $folderId = $document->folder_id;

        // Delete file and versions
        Storage::disk('public')->delete($document->file_path);
        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }

        $document->delete();

        CommercialProductionAudit::log('File dihapus', null, "File \"{$name}\" dihapus");

        // Redirect to folder index instead of back() to avoid 404 when deleted from show page
        return redirect()->route('commercial-productions.index', ['folder' => $folderId])
            ->with('success', "File \"{$name}\" berhasil dihapus.");
    }

    // ── Download ──
    public function downloadDocument(CommercialProductionDocument $document)
    {
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        CommercialProductionAudit::log('File di-download', $document, "File \"{$document->original_name}\" di-download");

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    // ── Download Version ──
    public function downloadVersion(CommercialProductionDocumentVersion $version)
    {
        if (! Storage::disk('public')->exists($version->file_path)) {
            abort(404, 'File versi tidak ditemukan.');
        }

        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }

    // ── Preview (for pdf/image) ──
    public function previewDocument(CommercialProductionDocument $document)
    {
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $path = Storage::disk('public')->path($document->file_path);

        return response()->file($path);
    }
}