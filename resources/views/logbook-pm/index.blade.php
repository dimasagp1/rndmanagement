<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Log Book PM</span>
        </div>
    </x-slot>

    <div x-data="{ showPrintPreview: false, printUrl: '', showPreview: false, previewUrl: '', previewType: 'image' }">

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total Entri</p>
                <p class="text-xl font-bold text-ink font-heading">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Pending</p>
                <p class="text-xl font-bold text-amber-600 font-heading">{{ $stats['pending'] }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Lulus</p>
                <p class="text-xl font-bold text-emerald-600 font-heading">{{ $stats['lulus'] }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Tidak Lulus</p>
                <p class="text-xl font-bold text-red-600 font-heading">{{ $stats['tidak_lulus'] }}</p>
            </div>
        </div>
    </div>

    {{-- Header Bar --}}
    <div class="page-header flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title">Log Book Bahan Pengemas (PM)</h1>
            <p class="text-sm text-gray-500">Rekap penerimaan dan pengujian sampel bahan pengemas</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('create', App\Models\LogbookPm::class)
            <a href="{{ route('logbook-pm.create') }}" class="btn-primary" id="btn-tambah-logbook">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Entri
            </a>
            @endcan
            <button @click="printUrl = '{{ route('logbook-pm.print-all', request()->query()) }}'; showPrintPreview = true;"
                    class="btn-outline" id="btn-cetak-logbook">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Log Book
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('logbook-pm.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="form-label text-xs">Cari Material / Kode / Supplier</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari..." class="form-input text-sm py-1.5">
                </div>
                <div class="min-w-[140px]">
                    <label class="form-label text-xs">Status Pengujian</label>
                    <select name="status_pengujian" class="form-input text-sm py-1.5">
                        <option value="">Semua Status</option>
                        @foreach(['Pending','Proses','Lulus','Tidak Lulus'] as $s)
                        <option value="{{ $s }}" {{ request('status_pengujian') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[130px]">
                    <label class="form-label text-xs">OM Approval</label>
                    <select name="om_approval" class="form-input text-sm py-1.5">
                        <option value="">Semua</option>
                        @foreach(['Pending','Approved','Rejected'] as $s)
                        <option value="{{ $s }}" {{ request('om_approval') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[120px]">
                    <label class="form-label text-xs">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm py-1.5">
                </div>
                <div class="min-w-[120px]">
                    <label class="form-label text-xs">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm py-1.5">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm text-sm">Filter</button>
                    <a href="{{ route('logbook-pm.index') }}" class="btn-ghost btn-sm text-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 w-8">No.</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 w-24">Tgl Terima</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[130px]">Supplier/Produsen</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[100px]">Jenis Kemasan</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[140px]">Nama/Deskripsi Material</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 w-24">Kode Bahan</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 w-24">No. Sample</th>
                        <th class="px-3 py-2.5 text-right font-bold text-gray-500 w-24">Jml Diterima</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 w-16">Satuan</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-24">Kelengkapan Dok.</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[120px]">Kondisi Fisik Aktual</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[120px]">Catatan Trial</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-24">Status Pengujian</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-20">Fisik</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-20">Lampiran</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-24">OM Approval</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-16">File Scan</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[100px]">Lokasi Simpan</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[100px]">Penerima (R&D)</th>
                        <th class="px-3 py-2.5 text-left font-bold text-gray-500 min-w-[100px]">Keterangan</th>
                        <th class="px-3 py-2.5 text-center font-bold text-gray-500 w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($entries as $i => $entry)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-3 py-2.5 text-center text-gray-400 font-medium">{{ $entries->firstItem() + $i }}</td>
                        <td class="px-3 py-2.5 text-gray-600 whitespace-nowrap">{{ $entry->tanggal_terima->format('d M Y') }}</td>
                        <td class="px-3 py-2.5 font-semibold text-ink">{{ $entry->nama_supplier_display }}</td>
                        <td class="px-3 py-2.5 text-gray-700">{{ $entry->jenis_kemasan }}</td>
                        <td class="px-3 py-2.5">
                            <p class="font-semibold text-ink leading-tight">{{ $entry->nama_material }}</p>
                            @if($entry->deskripsi_material)
                            <p class="text-gray-400 text-[10px] mt-0.5 leading-tight">{{ Str::limit($entry->deskripsi_material, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 font-mono text-[10px] text-gray-600">{{ $entry->kode_bahan ?? '—' }}</td>
                        <td class="px-3 py-2.5 font-mono text-[10px] text-primary">{{ $entry->no_sample ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-right font-semibold text-ink">{{ number_format($entry->jumlah_diterima, 0) }}</td>
                        <td class="px-3 py-2.5 text-gray-600">{{ $entry->satuan }}</td>
                        <td class="px-3 py-2.5 text-center">
                            @php $kelClasses = ['Lengkap'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/10','Sebagian'=>'bg-amber-50 text-amber-700 ring-amber-600/10','Tidak Lengkap'=>'bg-red-50 text-red-700 ring-red-600/10'][$entry->kelengkapan_dokumen] ?? ''; @endphp
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ring-1 {{ $kelClasses }}">{{ $entry->kelengkapan_dokumen }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-gray-600 leading-relaxed">{{ Str::limit($entry->kondisi_fisik_aktual, 60) }}</td>
                        <td class="px-3 py-2.5">
                            @if($entry->trialPm)
                                <a href="{{ route('trial-pms.show', $entry->trialPm) }}" class="text-primary hover:underline text-[10px] font-semibold">
                                    {{ $entry->trialPm->code }}
                                </a>
                                @if($entry->catatan_trial)
                                <p class="text-gray-400 text-[10px] mt-0.5 leading-tight">{{ Str::limit($entry->catatan_trial, 50) }}</p>
                                @endif
                            @else
                                <span class="text-gray-400 text-[10px] leading-tight">{{ Str::limit($entry->catatan_trial, 60) }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @php $stClasses = ['Lulus'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/10','Tidak Lulus'=>'bg-red-50 text-red-700 ring-red-600/10','Proses'=>'bg-blue-50 text-blue-700 ring-blue-600/10','Pending'=>'bg-amber-50 text-amber-700 ring-amber-600/10'][$entry->status_pengujian] ?? ''; @endphp
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ring-1 {{ $stClasses }}">{{ $entry->status_pengujian }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-center text-gray-600">{{ $entry->kondisi_fisik ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center">
                            @if($entry->lampiran_dokumentasi && count($entry->lampiran_dokumentasi) > 0)
                            <span class="text-primary font-semibold">{{ count($entry->lampiran_dokumentasi) }} file</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @php $omClasses = ['Approved'=>'bg-emerald-50 text-emerald-700 ring-emerald-600/10','Rejected'=>'bg-red-50 text-red-700 ring-red-600/10','Pending'=>'bg-amber-50 text-amber-700 ring-amber-600/10'][$entry->om_approval] ?? ''; @endphp
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ring-1 {{ $omClasses }}">{{ $entry->om_approval }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($entry->file_scan)
                            @php $ext = pathinfo($entry->file_scan, PATHINFO_EXTENSION); @endphp
                            <a href="{{ $entry->file_scan }}" 
                               @click.prevent="previewUrl = '{{ $entry->file_scan }}'; previewType = '{{ strtolower($ext) === 'pdf' ? 'pdf' : 'image' }}'; showPreview = true;"
                               class="text-primary hover:underline cursor-pointer">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-gray-600">{{ $entry->lokasi_penyimpanan ?? '—' }}</td>
                        <td class="px-3 py-2.5 font-medium text-ink">{{ $entry->nama_penerima }}</td>
                        <td class="px-3 py-2.5 text-gray-400 leading-relaxed">{{ Str::limit($entry->keterangan, 50) }}</td>
                        <td class="px-3 py-2.5 text-center">
                            <a href="{{ route('logbook-pm.show', $entry) }}" class="text-primary hover:underline text-[10px] font-semibold">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="21" class="px-4 py-10 text-center text-gray-400 italic text-sm">
                            Belum ada entri Log Book PM.
                            @can('create', App\Models\LogbookPm::class)
                            <a href="{{ route('logbook-pm.create') }}" class="text-primary hover:underline ml-1">Tambah sekarang →</a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $entries->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Pratinjau Cetak -->
    <div x-show="showPrintPreview" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div @click="showPrintPreview = false" 
             class="absolute inset-0 bg-ink/40 backdrop-blur-sm"></div>

        <!-- Modal Box -->
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-150 w-full flex flex-col z-10 overflow-hidden transform transition-all duration-300"
             style="height: 85vh; width: 95vw; max-width: 1200px;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-surface">
                <div>
                    <h3 class="text-sm font-bold text-ink leading-tight font-heading">Pratinjau Cetak Log Book PM</h3>
                    <p class="text-xs text-gray-500">Tampilan cetak lembar dokumen A4 Landscape</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="document.getElementById('preview-iframe').contentWindow.print()" 
                            class="btn-primary py-1.5 px-3 text-xs flex items-center gap-1.5"
                            id="btn-trigger-cetak-modal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak / Download
                    </button>
                    <button @click="showPrintPreview = false" class="btn-ghost py-1.5 px-3 text-xs">
                        Tutup
                    </button>
                </div>
            </div>

            <!-- Preview Area -->
            <div class="flex-1 bg-gray-150 p-4 overflow-hidden relative flex items-center justify-center"
                 x-data="{ scale: 0.6 }"
                 x-init="
                    const updateScale = () => {
                        const containerWidth = $el.clientWidth - 32;
                        const containerHeight = $el.clientHeight - 32;
                        const scaleW = containerWidth / 1122;
                        const scaleH = containerHeight / 794;
                        scale = Math.min(scaleW, scaleH, 1);
                    };
                    $watch('showPrintPreview', value => {
                        if (value) {
                            setTimeout(updateScale, 150);
                        }
                    });
                    window.addEventListener('resize', updateScale);
                 ">
                <template x-if="showPrintPreview">
                    <iframe id="preview-iframe" 
                            :src="printUrl" 
                            :style="'width: 1122px; height: 794px; transform: scale(' + scale + '); transform-origin: center center; position: absolute; border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);'" 
                            class="bg-white rounded-lg">
                    </iframe>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal Pratinjau Dokumen & Scan -->
    <div x-show="showPreview" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div @click="showPreview = false" 
             class="absolute inset-0 bg-ink/50 backdrop-blur-sm"></div>

        <!-- Modal Box -->
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-150 w-full flex flex-col z-10 overflow-hidden transform transition-all duration-300"
             style="height: 80vh; width: 95vw; max-width: 1000px;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-surface">
                <div>
                    <h3 class="text-sm font-bold text-ink leading-tight font-heading">Pratinjau Dokumen</h3>
                    <p class="text-[11px] text-gray-500 truncate max-w-md mt-0.5" x-text="previewUrl.split('/').pop()"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" target="_blank" class="btn-outline py-1.5 px-3 text-xs flex items-center gap-1.5" id="btn-trigger-tab-baru">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka Tab Baru
                    </a>
                    <button @click="showPreview = false" class="btn-ghost py-1.5 px-3 text-xs" id="btn-close-preview">
                        Tutup
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 bg-gray-150 p-4 overflow-auto flex items-center justify-center relative">
                <template x-if="previewType === 'image'">
                    <img :src="previewUrl" class="max-w-full max-h-full object-contain rounded-lg shadow-md bg-white">
                </template>
                <template x-if="previewType === 'pdf'">
                    <iframe :src="previewUrl" class="w-full h-full rounded-lg bg-white border-0 shadow-inner"></iframe>
                </template>
            </div>
        </div>
    </div>

    </div>

</x-app-layout>
