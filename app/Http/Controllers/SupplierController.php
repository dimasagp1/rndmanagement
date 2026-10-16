<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogbookPm;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:suppliers,name'],
            'contact' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'contact' => $request->contact,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Pemasok {$supplier->name} berhasil ditambahkan.");
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:suppliers,name,'.$supplier->id],
            'contact' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
        ]);

        $supplier->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Pemasok {$supplier->name} berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier)
    {
        $formulaCount = $supplier->formulaMaterials()->distinct()->count('formula_id');
        $logbookCount = LogbookPm::where('supplier_id', $supplier->id)->count();
        if ($formulaCount > 0 || $logbookCount > 0) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', "Pemasok {$supplier->name} tidak dapat dihapus karena masih dipakai di {$formulaCount} formula dan {$logbookCount} Log Book PM.");
        }

        $name = $supplier->name;
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Pemasok {$name} berhasil dihapus.");
    }

    /**
     * Tarik dan sinkronkan daftar rekanan pemasok resmi dari API Procurement.
     */
    public function syncFromProcurement(Request $request)
    {
        $apiUrl = setting('procurement_api_url', config('services.procurement.url'));
        $apiKey = setting('procurement_api_token', config('services.procurement.key'));

        if (empty($apiUrl)) {
            return redirect()->route('suppliers.index')
                ->with('error', 'URL Endpoint API Procurement belum disetel di Pengaturan Sistem. Silakan atur terlebih dahulu di menu Pengaturan Sistem.');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-API-KEY'     => $apiKey ?: '',
                    'Authorization' => 'Bearer ' . ($apiKey ?: ''),
                    'Accept'        => 'application/json',
                ])
                ->timeout(15)
                ->get($apiUrl);

            if (! $response->successful()) {
                return redirect()->route('suppliers.index')
                    ->with('error', "Gagal menghubungi API Procurement: Status HTTP {$response->status()}");
            }

            $json = $response->json();
            $items = $json['data'] ?? $json;
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')
                ->with('error', "Koneksi ke API Procurement gagal: {$e->getMessage()}");
        }

        if (! is_array($items) || empty($items)) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Data respons dari API Procurement kosong atau format tidak sesuai.');
        }

        $newCount = 0;
        $existingCount = 0;

        foreach ($items as $item) {
            $venName = trim($item['vendor_name'] ?? '');
            if (empty($venName) || in_array(strtolower($venName), ['vendor odoo terdaftar', 'vendor'])) {
                continue;
            }

            $supplier = Supplier::where('name', $venName)->first();

            if (! $supplier) {
                Supplier::create([
                    'name'    => $venName,
                    'contact' => !empty($item['vendor_code']) ? 'Kode AVL: ' . $item['vendor_code'] : null,
                    'phone'   => null,
                    'email'   => null,
                    'address' => 'Sinkronisasi dari Master AVL Sistem Procurement / Odoo ERP',
                ]);
                $newCount++;
            } else {
                $existingCount++;
            }
        }

        return redirect()->route('suppliers.index')
            ->with('success', "Sinkronisasi pemasok berhasil: {$newCount} pemasok baru ditambahkan ({$existingCount} pemasok sudah terdaftar di R&D).");
    }
}

