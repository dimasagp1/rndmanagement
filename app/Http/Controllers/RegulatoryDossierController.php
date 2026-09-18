<?php

namespace App\Http\Controllers;

use App\Exports\RegulatoryDocumentsExport;
use App\Models\RegulatoryAudit;
use App\Models\RegulatoryDocument;
use App\Models\RegulatoryDocumentVersion;
use App\Models\RegulatoryFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class RegulatoryDossierController extends Controller
{
    // ── Index: folder view + tree + search ──
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $search   = $request->get('search');

        $currentFolder = $folderId ? RegulatoryFolder::with('parent')->find($folderId) : null;

        if ($folderId && ! $currentFolder) {
            abort(404, 'Folder tidak ditemukan.');
        }

        $breadcrumbs = $currentFolder ? $currentFolder->breadcrumbs() : [];

        // Tree for sidebar
        $tree = RegulatoryFolder::tree();

        // Folders in current level
        $foldersQuery = RegulatoryFolder::where('parent_id', $folderId)->with('creator')->orderBy('name');

        // Documents in current folder
        $documentsQuery = RegulatoryDocument::where('folder_id', $folderId)->with('uploader', 'folder')->orderByDesc('updated_at');

        if ($search) {
            $searchLower = strtolower($search);
            // If search, search across all folders/documents
            $foldersQuery = RegulatoryFolder::where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->with('creator')->orderBy('name');

            $documentsQuery = RegulatoryDocument::where(function ($q) use ($search) {
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
        $totalFolders   = RegulatoryFolder::count();
        $totalDocuments = RegulatoryDocument::count();
        $totalSize      = RegulatoryDocument::sum('file_size');

        // Audits recent
        $recentAudits = RegulatoryAudit::with('user')->latest()->take(10)->get();

        // For move dropdown: all folders flat
        $allFolders = RegulatoryFolder::orderBy('name')->get();

        return view('regulatory-dossier.index', compact(
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
            'parent_id'   => 'nullable|exists:regulatory_folders,id',
        ]);

        $folder = RegulatoryFolder::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        RegulatoryAudit::log('Folder dibuat', $folder, "Folder \"{$folder->name}\" dibuat" . ($folder->parent_id ? " di dalam \"{$folder->parent->name}\"" : " di root"));

        return back()->with('success', "Folder \"{$folder->name}\" berhasil dibuat.");
    }

    // ── Update Folder (rename) ──
    public function updateFolder(Request $request, RegulatoryFolder $folder)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id'   => 'nullable|exists:regulatory_folders,id',
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

        RegulatoryAudit::log('Folder diubah', $folder, "Folder \"{$oldName}\" diubah menjadi \"{$folder->name}\"");

        return back()->with('success', "Folder \"{$oldName}\" berhasil diperbarui.");
    }

    // ── Destroy Folder ──
    public function destroyFolder(RegulatoryFolder $folder)
    {
        $name = $folder->name;
        $parentId = $folder->parent_id;

        $this->deleteFolderRecursively($folder);

        RegulatoryAudit::log('Folder dihapus', null, "Folder \"{$name}\" beserta seluruh isinya dihapus");

        return redirect()->route('regulatory-dossier.index', $parentId ? ['folder' => $parentId] : [])
            ->with('success', "Folder \"{$name}\" berhasil dihapus.");
    }

    private function deleteFolderRecursively(RegulatoryFolder $folder): void
    {
        $folder->loadMissing(['children', 'documents.versions']);

        foreach ($folder->children as $child) {
            $this->deleteFolderRecursively($child);
        }

        foreach ($folder->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
            foreach ($doc->versions as $version) {
                Storage::disk('public')->delete($version->file_path);
            }
            $doc->delete();
        }

        $folder->delete();
    }

    // ── Store Document (upload) ──
    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'folder_id'   => 'nullable|exists:regulatory_folders,id',
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
            $filePath     = $file->storeAs('regulatory-dossier/' . ($validated['folder_id'] ?? 'root'), $fileName, 'public');

            // Check duplicate name in same folder -> create version instead of duplicate entry
            $existing = RegulatoryDocument::where('folder_id', $validated['folder_id'] ?? null)
                ->where('original_name', $originalName)
                ->first();

            if ($existing) {
                // Save current as version
                RegulatoryDocumentVersion::create([
                    'regulatory_document_id' => $existing->id,
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

                RegulatoryAudit::log('Dokumen versi baru', $existing, "Versi {$existing->version} dari \"{$originalName}\" diunggah");
            } else {
                $doc = RegulatoryDocument::create([
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

                RegulatoryAudit::log('File di-upload', $doc, "File \"{$originalName}\" diunggah" . ($validated['folder_id'] ? " ke folder \"{$doc->folder->name}\"" : " di root"));
            }

            $uploaded++;
        }

        return back()->with('success', "{$uploaded} file berhasil diunggah.");
    }

    // ── Show Document Detail ──
    public function showDocument(RegulatoryDocument $document)
    {
        $document->load(['folder', 'uploader', 'versions.uploader']);

        $breadcrumbs = $document->folder ? $document->folder->breadcrumbs() : [];
        $allFolders  = RegulatoryFolder::orderBy('name')->get();

        return view('regulatory-dossier.show', compact('document', 'breadcrumbs', 'allFolders'));
    }

    // ── Update Document (rename / description) ──
    public function updateDocument(Request $request, RegulatoryDocument $document)
    {
        $validated = $request->validate([
            'original_name' => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
        ]);

        $oldName = $document->original_name;
        $document->update($validated);

        RegulatoryAudit::log('File diubah', $document, "File \"{$oldName}\" diubah menjadi \"{$document->original_name}\"");

        return back()->with('success', "File \"{$oldName}\" berhasil diperbarui.");
    }

    // ── Move Document ──
    public function moveDocument(Request $request, RegulatoryDocument $document)
    {
        $validated = $request->validate([
            'folder_id' => 'nullable|exists:regulatory_folders,id',
        ]);

        $oldFolder = $document->folder?->name ?? 'Root';
        $document->update(['folder_id' => $validated['folder_id']]);

        $newFolder = $document->fresh()->folder?->name ?? 'Root';

        RegulatoryAudit::log('File dipindahkan', $document, "File \"{$document->original_name}\" dipindahkan dari \"{$oldFolder}\" ke \"{$newFolder}\"");

        return back()->with('success', "File \"{$document->original_name}\" dipindahkan ke \"{$newFolder}\".");
    }

    // ── Destroy Document ──
    public function destroyDocument(RegulatoryDocument $document)
    {
        $name = $document->original_name;
        $folderId = $document->folder_id;

        // Delete file and versions
        Storage::disk('public')->delete($document->file_path);
        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }

        $document->delete();

        RegulatoryAudit::log('File dihapus', null, "File \"{$name}\" dihapus");

        // Redirect to folder index instead of back() to avoid 404 when deleted from show page
        return redirect()->route('regulatory-dossier.index', ['folder' => $folderId])
            ->with('success', "File \"{$name}\" berhasil dihapus.");
    }

    // ── Download ──
    public function downloadDocument(RegulatoryDocument $document)
    {
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        RegulatoryAudit::log('File di-download', $document, "File \"{$document->original_name}\" di-download");

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    // ── Download Version ──
    public function downloadVersion(RegulatoryDocumentVersion $version)
    {
        if (! Storage::disk('public')->exists($version->file_path)) {
            abort(404, 'File versi tidak ditemukan.');
        }

        return Storage::disk('public')->download($version->file_path, $version->file_name);
    }

    // ── Preview (for pdf/image) ──
    public function previewDocument(RegulatoryDocument $document)
    {
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $path = Storage::disk('public')->path($document->file_path);

        return response()->file($path);
    }

    // ── Export ZIP (download all original files in folder/subfolder/system) ──
    public function exportZip(Request $request)
    {
        $folderId = $request->get('folder');
        $currentFolder = $folderId ? RegulatoryFolder::find($folderId) : null;

        $query = RegulatoryDocument::with(['folder']);

        if ($currentFolder) {
            $folderIds = $currentFolder->allDescendantIds();
            $query->whereIn('folder_id', $folderIds);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('extension', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('extension', $type);
        }

        $documents = $query->get();

        if ($documents->isEmpty()) {
            return back()->with('error', 'Tidak ada dokumen yang dapat diunduh.');
        }

        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'dossier_zip_');

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat arsip ZIP.');
        }

        $disk = Storage::disk('public');
        $usedNames = [];
        $addedFiles = 0;

        foreach ($documents as $doc) {
            if (! $disk->exists($doc->file_path)) {
                continue;
            }

            $folderPrefix = '';
            if ($doc->folder) {
                $folderPrefix = $this->getRelativeZipPath($doc->folder, $currentFolder);
            } elseif (! $currentFolder) {
                $folderPrefix = 'Root_Files/';
            }

            $rawName = $doc->original_name;
            $zipEntryPath = $folderPrefix . $rawName;

            if (isset($usedNames[$zipEntryPath])) {
                $usedNames[$zipEntryPath]++;
                $ext = pathinfo($rawName, PATHINFO_EXTENSION);
                $base = pathinfo($rawName, PATHINFO_FILENAME);
                $suffix = $ext ? ".{$ext}" : '';
                $zipEntryPath = $folderPrefix . "{$base} (v{$doc->version}-{$usedNames[$zipEntryPath]}){$suffix}";
            } else {
                $usedNames[$zipEntryPath] = 1;
            }

            $zip->addFile($disk->path($doc->file_path), $zipEntryPath);
            $addedFiles++;
        }

        $zip->close();

        if ($addedFiles === 0) {
            @unlink($tempFile);
            return back()->with('error', 'File fisik dokumen tidak ditemukan di penyimpanan server.');
        }

        $folderSlug = $currentFolder ? Str::slug($currentFolder->name) : 'semua-dossier';
        $fileName = "Regulatory_Dossier_{$folderSlug}_" . now()->format('Ymd_His') . ".zip";

        RegulatoryAudit::log('Export ZIP', null, "Download ZIP {$addedFiles} file dokumen dossier ({$fileName})");

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function getRelativeZipPath(RegulatoryFolder $folder, ?RegulatoryFolder $baseFolder = null): string
    {
        $crumbs = $folder->breadcrumbs();

        if ($baseFolder) {
            $segments = [];
            $foundBase = false;
            foreach ($crumbs as $crumb) {
                if ($crumb->id === $baseFolder->id) {
                    $foundBase = true;
                    continue;
                }
                if ($foundBase) {
                    $segments[] = Str::slug($crumb->name, '_');
                }
            }
            return empty($segments) ? '' : implode('/', $segments) . '/';
        }

        $segments = array_map(fn($f) => Str::slug($f->name, '_'), $crumbs);
        return empty($segments) ? '' : implode('/', $segments) . '/';
    }

    // ── Export Excel (metadata of all documents in folder/system) ──
    public function exportExcel(Request $request)
    {
        $folderId = $request->get('folder');
        $currentFolder = $folderId ? RegulatoryFolder::find($folderId) : null;

        $query = RegulatoryDocument::with(['folder', 'uploader']);

        if ($currentFolder) {
            $folderIds = $currentFolder->allDescendantIds();
            $query->whereIn('folder_id', $folderIds);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('extension', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('extension', $type);
        }

        $documents = $query->orderBy('original_name')->get();

        if ($documents->isEmpty()) {
            return back()->with('error', 'Tidak ada dokumen yang dapat diexport.');
        }

        $folderSlug = $currentFolder ? Str::slug($currentFolder->name) : 'semua-dossier';
        $fileName = "Rekap_Dokumen_Dossier_{$folderSlug}_" . now()->format('Ymd_His') . ".xlsx";

        RegulatoryAudit::log('Export Excel', null, "Export rekap {$documents->count()} dokumen dossier ({$fileName})");

        return Excel::download(new RegulatoryDocumentsExport($documents), $fileName);
    }
}
