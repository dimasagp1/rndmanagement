<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('logbook-pm.index') }}" class="hover:text-primary">Log Book PM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Detail Entri</span>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="page-header mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-title">Detail Log Book PM</h1>
            <p class="text-xs text-gray-500">Dibuat oleh {{ $logbookPm->creator?->name ?? '—' }} pada {{ $logbookPm->created_at->format('d M Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            @can('update', $logbookPm)
            <a href="{{ route('logbook-pm.edit', $logbookPm) }}" class="btn-primary" id="btn-edit-logbook">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            @endcan

            @can('delete', $logbookPm)
            <form action="{{ route('logbook-pm.destroy', $logbookPm) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus entri log book ini?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline border-red-200 text-red-600 hover:bg-red-50" id="btn-hapus-logbook">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
            @endcan

            <a href="{{ route('logbook-pm.index') }}" class="btn-ghost">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ─── LEFT/MAIN: DETAILS ─── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- 1. UTAMA & IDENTITAS --}}
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-sm font-heading font-semibold text-ink">1. Identitas & Informasi Penerimaan</h2>
                    @php $stClasses = ['Lulus'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/10','Tidak Lulus'=>'bg-red-50 text-red-700 ring-red-600/10','Proses'=>'bg-blue-50 text-blue-700 ring-blue-600/10','Pending'=>'bg-amber-50 text-amber-700 ring-amber-600/10'][$logbookPm->status_pengujian] ?? ''; @endphp
                    <span class="px-2 py-0.5 rounded text-xs font-semibold ring-1 {{ $stClasses }}">{{ $logbookPm->status_pengujian }}</span>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                        <div>
                            <span class="text-gray-400 block text-xs">Tanggal Terima</span>
                            <span class="text-ink font-medium">{{ $logbookPm->tanggal_terima->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Jenis Kemasan</span>
                            <span class="text-ink font-medium">{{ $logbookPm->jenis_kemasan }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Supplier/Produsen</span>
                            <span class="text-ink font-semibold text-primary">{{ $logbookPm->nama_supplier_display }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Nama Material</span>
                            <span class="text-ink font-semibold">{{ $logbookPm->nama_material }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-400 block text-xs">Deskripsi Material</span>
                            <span class="text-ink leading-relaxed">{{ $logbookPm->deskripsi_material ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Kode Bahan</span>
                            <span class="text-ink font-mono">{{ $logbookPm->kode_bahan ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">No. Sample</span>
                            <span class="text-ink font-mono">{{ $logbookPm->no_sample ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Jumlah Diterima</span>
                            <span class="text-ink font-bold text-lg">{{ number_format($logbookPm->jumlah_diterima, 0) }} <span class="text-xs text-gray-500 font-normal">{{ $logbookPm->satuan }}</span></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Lokasi Penyimpanan</span>
                            <span class="text-ink font-medium">{{ $logbookPm->lokasi_penyimpanan ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Nama Penerima (R&D)</span>
                            <span class="text-ink font-medium">{{ $logbookPm->nama_penerima }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. KONDISI & KELENGKAPAN --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">2. Kondisi & Kelengkapan</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400 block text-xs">Kelengkapan Dokumen</span>
                            @php $kelClasses = ['Lengkap'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/10','Sebagian'=>'bg-amber-50 text-amber-700 ring-amber-600/10','Tidak Lengkap'=>'bg-red-50 text-red-700 ring-red-600/10'][$logbookPm->kelengkapan_dokumen] ?? ''; @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-semibold ring-1 {{ $kelClasses }} inline-block mt-1">{{ $logbookPm->kelengkapan_dokumen }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs">Fisik (Ringkasan)</span>
                            <span class="text-ink font-semibold inline-block mt-1">{{ $logbookPm->kondisi_fisik ?? '—' }}</span>
                        </div>
                    </div>
                    <hr class="border-gray-100">
                    <div>
                        <span class="text-gray-400 block text-xs mb-1">Kondisi Fisik Aktual</span>
                        <div class="bg-gray-50/50 rounded-lg p-3 border border-gray-100 text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                            {{ $logbookPm->kondisi_fisik_aktual }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. CATATAN TRIAL --}}
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-sm font-heading font-semibold text-ink">3. Catatan Trial</h2>
                    @if($logbookPm->trialPm)
                    <a href="{{ route('trial-pms.show', $logbookPm->trialPm) }}" class="text-primary hover:underline text-xs font-semibold">
                        Detail Trial PM ({{ $logbookPm->trialPm->code }}) →
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($logbookPm->catatan_trial)
                    <div class="bg-gray-50/50 rounded-lg p-3 border border-gray-100 font-mono text-xs text-gray-700 leading-relaxed whitespace-pre-line">
                        {{ $logbookPm->catatan_trial }}
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic">Belum ada catatan trial.</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- ─── RIGHT: APPROVAL & DOCUMENTATION ─── --}}
        <div class="space-y-5">

            {{-- OM APPROVAL --}}
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-sm font-heading font-semibold text-ink">Operational Manager Approval</h2>
                    @php $omClasses = ['Approved'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/10','Rejected'=>'bg-red-50 text-red-700 ring-red-600/10','Pending'=>'bg-amber-50 text-amber-700 ring-amber-600/10'][$logbookPm->om_approval] ?? ''; @endphp
                    <span class="px-2 py-0.5 rounded text-xs font-semibold ring-1 {{ $omClasses }}">{{ $logbookPm->om_approval }}</span>
                </div>
                <div class="card-body text-sm space-y-4">
                    @if($logbookPm->om_approval === 'Approved' || $logbookPm->om_approval === 'Rejected')
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-primary font-bold">{{ strtoupper(substr($logbookPm->omApprover?->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-ink leading-tight">{{ $logbookPm->omApprover?->name }}</p>
                                    <p class="text-[10px] text-gray-400">Approved on {{ $logbookPm->om_approved_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                            @if($logbookPm->om_notes)
                            <div class="text-xs text-gray-600 italic bg-white p-2 rounded border border-gray-100 whitespace-pre-line">
                                " {{ $logbookPm->om_notes }} "
                            </div>
                            @endif
                        </div>
                    @else
                        @can('approve', $logbookPm)
                            <form action="{{ route('logbook-pm.approve', $logbookPm) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="form-label text-xs">Catatan OM (Opsional)</label>
                                    <textarea name="om_notes" rows="3" placeholder="Masukkan catatan jika ada..." class="form-input text-xs"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="submit" name="om_approval" value="Approved" class="btn-primary py-1.5 text-xs flex justify-center items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Setujui
                                    </button>
                                    <button type="submit" name="om_approval" value="Rejected" class="btn-outline border-red-200 text-red-600 hover:bg-red-50 py-1.5 text-xs flex justify-center items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Tolak
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="bg-amber-50/50 border border-amber-200/50 rounded-lg p-3 text-center text-xs text-amber-800">
                                Menunggu approval Operational Manager.
                            </div>
                        @endcan
                    @endif
                </div>
            </div>

            {{-- DOKUMENTASI & FILE SCAN --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Dokumentasi & Scan</h2>
                </div>
                <div class="card-body text-sm space-y-4">
                    {{-- File Scan --}}
                    <div>
                        <span class="text-gray-400 block text-xs mb-1.5">File Scan (CoA / DO)</span>
                        @if($logbookPm->file_scan)
                            <a href="{{ $logbookPm->file_scan }}" target="_blank" class="flex items-center gap-2 p-2 bg-gray-50 hover:bg-primary/5 rounded border border-gray-100 transition-colors">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="font-medium text-xs text-ink truncate flex-1">{{ basename($logbookPm->file_scan) }}</span>
                            </a>
                        @else
                            <p class="text-xs text-gray-400 italic">Tidak ada file scan yang diupload.</p>
                        @endif
                    </div>
                    <hr class="border-gray-100">
                    {{-- Lampiran --}}
                    <div>
                        <span class="text-gray-400 block text-xs mb-1.5">Lampiran Dokumentasi</span>
                        @if($logbookPm->lampiran_dokumentasi && count($logbookPm->lampiran_dokumentasi) > 0)
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($logbookPm->lampiran_dokumentasi as $img)
                                    @php $ext = pathinfo($img, PATHINFO_EXTENSION); @endphp
                                    @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                                        <a href="{{ $img }}" target="_blank" class="block border border-gray-100 rounded-lg overflow-hidden hover:opacity-85 transition-opacity">
                                            <img src="{{ $img }}" class="w-full h-24 object-cover">
                                        </a>
                                    @else
                                        <a href="{{ $img }}" target="_blank" class="flex flex-col items-center justify-center p-3 border border-gray-100 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors text-center text-xs text-ink">
                                            <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span class="truncate w-full font-medium">{{ basename($img) }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Tidak ada lampiran dokumentasi.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KETERANGAN --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Keterangan Tambahan</h2>
                </div>
                <div class="card-body text-xs text-gray-600 leading-relaxed whitespace-pre-line">
                    {{ $logbookPm->keterangan ?? '—' }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
