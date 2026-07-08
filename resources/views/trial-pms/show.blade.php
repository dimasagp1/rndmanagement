<x-app-layout>
    <div x-data="{ showPrintModal: false }">
    {{-- Google Font for Signatures --}}
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        .font-signature {
            font-family: 'Dancing Script', cursive;
        }
    </style>

    @php
        // Helper: resolve signature URL (handles both /storage/ prefix and relative paths)
        $resolveSigUrl = function($path) {
            if (!$path) return null;
            $path = str_starts_with($path, '/storage/') ? substr($path, 9) : $path;
            return asset('storage/' . $path);
        };
    @endphp

    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-pms.index') }}" class="hover:text-primary">Catatan Trial PM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $trialPm->code }}</span>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="alert-danger mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    {{-- Header Action Bar --}}
    <div class="page-header flex-wrap gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $trialPm->code }}</code>
                <x-status-badge :status="$trialPm->approval_status" />
            </div>
            <h1 class="page-title">Trial PM: {{ $trialPm->packaging_material }}</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($trialPm->approval_status === 'Draft')
                @can('edit', $trialPm)
                <a href="{{ route('trial-pms.edit', $trialPm) }}" class="btn-outline" id="btn-edit-trial-pm">Edit</a>
                @endcan

                @can('submit', $trialPm)
                <form method="POST" action="{{ route('trial-pms.submit', $trialPm) }}" id="form-submit-trial-pm">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Ajukan trial PM ini untuk review 4 departemen? Status akan terkunci.')"
                            class="btn-primary" id="btn-submit-trial-pm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ajukan Review Kolektif
                    </button>
                </form>
                @endcan
            @endif

            <button type="button" x-on:click="showPrintModal = true; document.getElementById('printPreviewFrame').src = '{{ route('trial-pms.print', $trialPm) }}'" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Form
            </button>

            <a href="{{ route('trial-pms.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ activeReviewDept: null }">

        {{-- ─── LEFT COLUMN: PAPER FORM LAYOUT ────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6 print:w-full">
            
            {{-- THE OFFICIAL HERBATECH PAPER DOCUMENT CARD --}}
            <div class="bg-white border border-gray-300 shadow-sm rounded-xl overflow-hidden p-6 sm:p-8 space-y-6 print:border-none print:shadow-none print:p-0">
                
                {{-- Document Header --}}
                <div class="flex items-center justify-between border-b-2 border-gray-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-emerald-700 text-white font-extrabold flex items-center justify-center rounded-xl text-lg tracking-wider">
                            HT
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none">PT HERBATECH</p>
                            <h2 class="text-xs font-bold text-ink uppercase tracking-wider leading-tight">Innopharma Industry</h2>
                        </div>
                    </div>
                    <div class="text-right">
                        <h1 class="text-sm sm:text-base font-bold text-ink tracking-wide uppercase leading-tight">FORM CATATAN TRIAL BAHAN PENGEMAS</h1>
                        <p class="text-[10px] font-mono text-gray-500">No. Form: CM-06/RD/002-03.00</p>
                    </div>
                </div>

                {{-- No. Usulan (diisi oleh Sub-Bagian R&D) --}}
                <div class="flex items-center text-sm border-b border-gray-200 pb-3">
                    <span class="font-bold text-ink w-28">No. Usulan</span>
                    <span class="text-gray-400 mr-2">:</span>
                    <span class="text-ink font-semibold bg-gray-50 px-2 py-0.5 rounded border border-gray-150 font-mono">{{ $trialPm->proposal_number ?? '_______________________' }}</span>
                    <span class="text-xs text-gray-400 italic ml-4">(diisi oleh Sub-Bagian R&D)</span>
                </div>

                {{-- A. DATA BAHAN KEMAS --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-1 mb-3">A. Data Bahan Kemas</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                        <div class="flex border-b border-gray-100 py-1">
                            <span class="text-gray-400 w-44 flex-shrink-0 font-medium">Bahan Pengemas</span>
                            <span class="text-ink font-semibold">{{ $trialPm->packaging_material }}</span>
                        </div>
                        <div class="flex border-b border-gray-100 py-1">
                            <span class="text-gray-400 w-44 flex-shrink-0 font-medium">Pemasok (Supplier)</span>
                            <span class="text-ink font-semibold">{{ $trialPm->supplier }}</span>
                        </div>
                        <div class="flex border-b border-gray-100 py-1">
                            <span class="text-gray-400 w-44 flex-shrink-0 font-medium">Digunakan Untuk Produk</span>
                            <span class="text-ink">{{ is_array($trialPm->product_use) ? implode(', ', $trialPm->product_use) : $trialPm->product_use }}</span>
                        </div>
                        <div class="flex border-b border-gray-100 py-1">
                            <span class="text-gray-400 w-44 flex-shrink-0 font-medium">Ditrial Pada Produk</span>
                            <span class="text-ink">{{ is_array($trialPm->product_trial) ? implode(', ', $trialPm->product_trial) : $trialPm->product_trial }}</span>
                        </div>
                        <div class="flex border-b border-gray-100 py-1">
                            <span class="text-gray-400 w-44 flex-shrink-0 font-medium">Jumlah Sampel Ditrial</span>
                            <span class="text-ink">{{ $trialPm->trial_sample_quantity }}</span>
                        </div>
                        <div class="flex border-b border-gray-100 py-1">
                            <span class="text-gray-400 w-44 flex-shrink-0 font-medium">Pemasok Lama</span>
                            <span class="text-ink">{{ $trialPm->old_supplier ?? '—' }}</span>
                        </div>
                        <div class="sm:col-span-2 flex flex-col gap-1 py-1">
                            <span class="text-gray-400 font-medium">Perbedaan dengan Eksisting</span>
                            <p class="text-ink bg-gray-50 p-2.5 rounded-lg border border-gray-150 text-xs whitespace-pre-line leading-relaxed">
                                {{ $trialPm->difference_with_existing ?? 'Tidak ada perbedaan yang dicatatkan.' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- B. SPESIFIKASI BAHAN KEMAS --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-1 mb-3">B. Spesifikasi Bahan Kemas</h3>
                    <div class="bg-gray-50 border border-gray-150 rounded-xl p-4">
                        <ul class="space-y-2 text-sm text-gray-700">
                            @if(is_array($trialPm->specifications) && count($trialPm->specifications) > 0)
                                @foreach($trialPm->specifications as $index => $spec)
                                <li class="flex items-start gap-2">
                                    <span class="font-bold text-gray-400 w-5 text-right">{{ $index + 1 }}.</span>
                                    <span class="flex-1">{{ $spec }}</span>
                                </li>
                                @endforeach
                            @else
                            <li class="text-xs text-gray-400 italic">Tidak ada deskripsi spesifikasi fisik kemasan.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- C. PELAKSANAAN TRIAL DAN HASIL TRIAL --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-1 mb-3">C. Pelaksanaan Trial dan Hasil Trial</h3>
                    <div class="border border-gray-200 rounded-xl overflow-hidden overflow-x-auto bg-white">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500 w-8">No</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500">Mesin Pengemas</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500">Parameter Setting</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500">Parameter Aktual</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-500 w-16">Waktu Mulai</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-500 w-16">Waktu Selesai</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-500 w-14">Reject</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-500 w-14">Baik</th>
                                    <th class="px-2 py-2 text-center font-bold text-gray-500 w-24">Paraf Prod</th>
                                    <th class="px-2 py-2 text-center font-bold text-gray-500 w-24">Paraf Eng</th>
                                    <th class="px-2 py-2 text-center font-bold text-gray-500 w-24">Paraf QC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @if(is_array($trialPm->executions) && count($trialPm->executions) > 0)
                                    @foreach($trialPm->executions as $index => $exe)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-3 py-2.5 text-center font-medium text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2.5 font-semibold text-ink">{{ $exe['machine'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-gray-600">{{ $exe['setting'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-gray-600">{{ $exe['actual'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-center text-gray-600">{{ $exe['start_time'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-center text-gray-600">{{ $exe['end_time'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-center text-red-600 font-semibold">{{ $exe['reject'] ?? '0' }}</td>
                                        <td class="px-3 py-2.5 text-center text-emerald-600 font-semibold">{{ $exe['good'] ?? '0' }}</td>
                                        <td class="px-2 py-2.5 text-center">
                                            @if(!empty($exe['paraf_prod']))
                                                @if(!empty($exe['paraf_prod_signature']))
                                                <img src="{{ $resolveSigUrl($exe['paraf_prod_signature']) }}" alt="Paraf Prod"
                                                     title="Diparaf oleh {{ $exe['paraf_prod_signed_name'] ?? '—' }} · {{ $exe['paraf_prod_signed_at'] ?? '—' }}"
                                                     class="h-7 max-w-[72px] object-contain inline-block border border-emerald-100 rounded bg-white select-none">
                                                @elseif(!empty($exe['paraf_prod_signed_name']))
                                                <span class="font-signature text-sm text-emerald-700 select-none"
                                                      title="Diparaf oleh {{ $exe['paraf_prod_signed_name'] }} · {{ $exe['paraf_prod_signed_at'] ?? '—' }}">{{ $exe['paraf_prod_signed_name'] }}</span>
                                                @else
                                                <span class="text-emerald-600 font-bold text-sm">✓</span>
                                                @endif
                                            @else
                                            <span class="text-gray-300 font-semibold">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2.5 text-center">
                                            @if(!empty($exe['paraf_eng']))
                                                @if(!empty($exe['paraf_eng_signature']))
                                                <img src="{{ $resolveSigUrl($exe['paraf_eng_signature']) }}" alt="Paraf Eng"
                                                     title="Diparaf oleh {{ $exe['paraf_eng_signed_name'] ?? '—' }} · {{ $exe['paraf_eng_signed_at'] ?? '—' }}"
                                                     class="h-7 max-w-[72px] object-contain inline-block border border-emerald-100 rounded bg-white select-none">
                                                @elseif(!empty($exe['paraf_eng_signed_name']))
                                                <span class="font-signature text-sm text-emerald-700 select-none"
                                                      title="Diparaf oleh {{ $exe['paraf_eng_signed_name'] }} · {{ $exe['paraf_eng_signed_at'] ?? '—' }}">{{ $exe['paraf_eng_signed_name'] }}</span>
                                                @else
                                                <span class="text-emerald-600 font-bold text-sm">✓</span>
                                                @endif
                                            @else
                                            <span class="text-gray-300 font-semibold">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2.5 text-center">
                                            @if(!empty($exe['paraf_qc']))
                                                @if(!empty($exe['paraf_qc_signature']))
                                                <img src="{{ $resolveSigUrl($exe['paraf_qc_signature']) }}" alt="Paraf QC"
                                                     title="Diparaf oleh {{ $exe['paraf_qc_signed_name'] ?? '—' }} · {{ $exe['paraf_qc_signed_at'] ?? '—' }}"
                                                     class="h-7 max-w-[72px] object-contain inline-block border border-emerald-100 rounded bg-white select-none">
                                                @elseif(!empty($exe['paraf_qc_signed_name']))
                                                <span class="font-signature text-sm text-emerald-700 select-none"
                                                      title="Diparaf oleh {{ $exe['paraf_qc_signed_name'] }} · {{ $exe['paraf_qc_signed_at'] ?? '—' }}">{{ $exe['paraf_qc_signed_name'] }}</span>
                                                @else
                                                <span class="text-emerald-600 font-bold text-sm">✓</span>
                                                @endif
                                            @else
                                            <span class="text-gray-300 font-semibold">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="11" class="px-3 py-4 text-center text-xs text-gray-400 italic">Belum ada rincian pelaksanaan trial mesin.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- D. PEMBAHASAN HASIL TRIAL --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-1 mb-3">D. Pembahasan Hasil Trial</h3>
                    <div class="border border-gray-200 rounded-xl overflow-hidden overflow-x-auto bg-white">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500 w-8">No</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500 min-w-[150px]">Evaluasi</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500 min-w-[150px]">Analisis Risiko</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500 min-w-[150px]">Rekomendasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @if(is_array($trialPm->discussion_rows) && count($trialPm->discussion_rows) > 0)
                                    @foreach($trialPm->discussion_rows as $index => $disc)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-3 py-3 text-center font-medium text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-3 py-3 text-gray-700 whitespace-pre-line leading-relaxed">{{ $disc['evaluation'] ?? '—' }}</td>
                                        <td class="px-3 py-3 text-gray-700 whitespace-pre-line leading-relaxed">{{ $disc['risk_analysis'] ?? '—' }}</td>
                                        <td class="px-3 py-3 text-gray-700 whitespace-pre-line leading-relaxed font-medium text-emerald-800">{{ $disc['recommendation'] ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-xs text-gray-400 italic">Belum ada pembahasan dan evaluasi R&D.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ATTACHED PHOTOS --}}
                @if($trialPm->photos && count($trialPm->photos) > 0)
                <div>
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-1 mb-3">Foto Proses dan Hasil Trial</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 print:grid-cols-2">
                        @foreach($trialPm->photos as $photo)
                        <div class="border border-gray-200 rounded-xl overflow-hidden aspect-[4/3] bg-surface">
                            <img src="{{ $photo }}" class="w-full h-full object-cover">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- E. KESIMPULAN --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-1 mb-3">E. Kesimpulan Kelaikan (Review 4 Departemen)</h3>
                    <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500 w-24">Departemen</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-500 w-28">Kesimpulan Kelaikan</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-500">Informasi Lain (Catatan Tinjauan)</th>
                                    <th class="px-3 py-2 text-right font-bold text-gray-500 w-24 print:hidden">Tinjau</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($trialPm->departmentApprovals as $app)
                                @if($trialPm->hasParafChecked($app->department))
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-3 py-3 font-bold text-ink">{{ $app->department_label }}</td>
                                    <td class="px-3 py-3 text-center">
                                        @if($app->approved_by)
                                            @if($app->is_approved)
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10 rounded text-[10px] font-semibold">
                                                Bisa Digunakan ✓
                                            </span>
                                            @else
                                            <span class="px-2 py-0.5 bg-red-50 text-red-700 ring-1 ring-red-600/10 rounded text-[10px] font-semibold">
                                                Tidak Bisa Digunakan ✗
                                            </span>
                                            @endif
                                        @else
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 ring-1 ring-amber-600/10 rounded text-[10px] font-semibold animate-pulse">
                                            Belum Dinilai
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-gray-600 leading-relaxed">
                                        @if($app->notes)
                                            {{ $app->notes }}
                                            <p class="text-[10px] text-gray-400 mt-1 flex flex-wrap items-center gap-1.5">
                                                <span>Ditinjau oleh {{ $app->approver?->name }} · {{ $app->approved_at?->isoFormat('D MMM Y, HH:mm') }}</span>
                                                @if($app->approver?->signature_path)
                                                    <span class="inline-flex items-center border border-emerald-100 rounded bg-white px-1.5 py-0.5" style="max-height: 20px !important;">
                                                        <img src="{{ $resolveSigUrl($app->approver->signature_path) }}" alt="Sig" class="select-none" style="height: 16px !important; max-height: 16px !important; width: auto !important; object-fit: contain !important; display: inline-block;">
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[9px] font-mono leading-none">
                                                        <span class="font-signature font-bold text-xs select-none lowercase">{{ str_replace(' ', '', $app->approver?->name) }}</span>
                                                        <span class="text-[8px] text-emerald-600">✓ TTE</span>
                                                    </span>
                                                @endif
                                            </p>
                                        @else
                                        <span class="inline-flex items-center gap-1.5 text-amber-600/90 font-medium italic text-xs">
                                            <svg class="w-3.5 h-3.5 text-amber-500 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Menunggu tinjauan...
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right print:hidden">
                                        @can('approve', $trialPm)
                                            @if(!$app->approved_by)
                                                @if($trialPm->hasParafChecked($app->department))
                                                <button type="button"
                                                        @click="activeReviewDept = activeReviewDept === '{{ $app->department }}' ? null : '{{ $app->department }}'"
                                                        class="btn-outline btn-sm text-[10px] py-0.5 px-2">
                                                    Input Penilaian
                                                </button>
                                                @else
                                                <button type="button"
                                                        disabled
                                                        title="Paraf untuk departemen ini belum dicentang pada pelaksanaan trial (Bagian C)"
                                                        class="btn-outline btn-sm text-[10px] py-0.5 px-2 opacity-55 cursor-not-allowed bg-gray-50 border-gray-200 text-gray-400">
                                                    Input Penilaian
                                                </button>
                                                @endif
                                            @endif
                                        @endcan
                                    </td>
                                </tr>

                                {{-- Inline Review Input Form --}}
                                @can('approve', $trialPm)
                                    @if(!$app->approved_by && $trialPm->hasParafChecked($app->department))
                                    <tr x-show="activeReviewDept === '{{ $app->department }}'" 
                                        x-collapse 
                                        class="bg-gray-50/40 print:hidden"
                                        style="display: none;">
                                        <td colspan="4" class="px-4 py-4">
                                            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-4 max-w-xl mx-auto">
                                                <h4 class="text-xs font-bold text-ink uppercase tracking-wider">Form Tinjauan: {{ $app->department_label }}</h4>
                                                <form method="POST" action="{{ route('trial-pms.approve', $trialPm) }}" class="space-y-4" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan penilaian departemen ini?')">
                                                    @csrf
                                                    <input type="hidden" name="department" value="{{ $app->department }}">

                                                    <div>
                                                        <label class="form-label text-[10px] font-bold uppercase text-gray-500">Kesimpulan Kelaikan *</label>
                                                        <div class="flex items-center gap-6 mt-1.5">
                                                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-750 cursor-pointer">
                                                                <input type="radio" name="is_approved" value="1" required checked class="text-primary focus:ring-primary h-4 w-4">
                                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10 rounded text-[10px] font-semibold">Bisa Digunakan</span>
                                                            </label>
                                                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-750 cursor-pointer">
                                                                <input type="radio" name="is_approved" value="0" required class="text-red-600 focus:ring-red-600 h-4 w-4">
                                                                <span class="px-2 py-0.5 bg-red-50 text-red-700 ring-1 ring-red-600/10 rounded text-[10px] font-semibold">Tidak Bisa Digunakan</span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="form-label text-[10px] font-bold uppercase text-gray-500" for="notes_{{ $app->department }}">Informasi Lain (Catatan Detail) *</label>
                                                        <textarea id="notes_{{ $app->department }}" name="notes" rows="3" required
                                                                  placeholder="Berikan catatan detail teknis uji kelayakan..."
                                                                  class="form-input text-xs py-1.5"></textarea>
                                                    </div>

                                                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                                        <button type="button" @click="activeReviewDept = null" class="btn-ghost btn-sm text-[10px]">Batal</button>
                                                        <button type="submit" class="btn-primary btn-sm text-[10px]">Simpan Penilaian</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @endcan
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Signatures Section --}}
                <div class="pt-6 border-t border-gray-150 grid grid-cols-2 gap-8 text-center text-xs">
                    <div>
                        <p class="text-gray-400 font-medium mb-1">Disusun oleh,<br>Staff Packaging Development</p>
                        <div class="h-20 flex items-center justify-center relative my-1">
                            @if($trialPm->creator?->signature_path)
                                <img src="{{ $resolveSigUrl($trialPm->creator->signature_path) }}" alt="Signature" class="select-none" style="height: 72px !important; max-height: 72px !important; width: auto !important; object-fit: contain !important; display: block; margin: 0 auto;">
                            @else
                                <div class="inline-flex flex-col items-center justify-center border border-emerald-200 bg-emerald-50/20 rounded-lg py-1 px-3 max-w-[180px] mx-auto text-emerald-800">
                                    <span class="text-[8px] uppercase tracking-wider font-semibold text-emerald-600 leading-none">Tanda Tangan Digital</span>
                                    <span class="font-signature text-xl my-0.5 text-emerald-700 select-none">{{ $trialPm->creator?->name ?? 'Staff R&D' }}</span>
                                    <span class="text-[7px] font-mono text-gray-400 leading-none">ID: HT-TTE-{{ strtoupper(substr(md5($trialPm->id . 'creator'), 0, 6)) }}</span>
                                </div>
                            @endif
                        </div>
                        <p class="font-bold text-ink">{{ $trialPm->creator?->name ?? '—' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $trialPm->created_at->isoFormat('D MMM Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 font-medium mb-1">Diperiksa oleh,<br>Operasional Manager</p>
                        @if($trialPm->operationalManager)
                            <div class="h-20 flex items-center justify-center relative my-1">
                                @if($trialPm->operationalManager->signature_path)
                                    <img src="{{ $resolveSigUrl($trialPm->operationalManager->signature_path) }}" alt="Signature" class="select-none" style="height: 72px !important; max-height: 72px !important; width: auto !important; object-fit: contain !important; display: block; margin: 0 auto;">
                                @else
                                    <div class="inline-flex flex-col items-center justify-center border border-emerald-200 bg-emerald-50/20 rounded-lg py-1 px-3 max-w-[180px] mx-auto text-emerald-800">
                                        <span class="text-[8px] uppercase tracking-wider font-semibold text-emerald-600 leading-none">Tanda Tangan Digital</span>
                                        <span class="font-signature text-xl my-0.5 text-emerald-700 select-none">{{ $trialPm->operationalManager->name }}</span>
                                        <span class="text-[7px] font-mono text-gray-400 leading-none">ID: HT-TTE-{{ strtoupper(substr(md5($trialPm->id . 'om'), 0, 6)) }}</span>
                                    </div>
                                @endif
                            </div>
                            <p class="font-bold text-ink">{{ $trialPm->operationalManager->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ $trialPm->approved_at?->isoFormat('D MMM Y') ?? '—' }}</p>
                        @else
                            <div class="h-20 flex items-center justify-center my-1">
                                <span class="text-xs text-gray-300 italic">Belum diperiksa</span>
                            </div>
                            <p class="font-bold text-ink">—</p>
                            <p class="text-[10px] text-gray-400">—</p>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        {{-- ─── RIGHT COLUMN: DEPT INPUTS & AUDIT TRAIL (print:hidden) ─────── --}}
        <div class="space-y-6 print:hidden">


            {{-- Audit Trail (Rejected notes) --}}
            @if($trialPm->approval_status === 'Rejected' && $trialPm->rejection_notes)
            <div class="card border-l-4 border-red-500 bg-red-50/10">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-700">Keputusan Ditolak</p>
                    <p class="text-xs text-red-600 mt-1 leading-relaxed">{{ $trialPm->rejection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Riwayat Aktivitas --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Riwayat Aktivitas (Audit Trail)</h2>
                </div>
                <div class="card-body">
                    <x-audit-trail :activities="$trialPm->activities" />
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         PRINT PREVIEW MODAL (inline popup on same page)
    ════════════════════════════════════════════════════════ --}}
    <style>
        .print-modal-backdrop {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
        }
        .print-modal-container {
            width: 95vw;
            max-width: 1200px;
            height: 92vh;
        }
        .print-iframe-wrapper {
            background: #64748b;
            overflow: auto;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .print-iframe-wrapper iframe {
            width: 794px;
            min-height: 1123px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            border-radius: 4px;
            background: #fff;
            flex-shrink: 0;
        }
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.15s;
            border: none;
            cursor: pointer;
        }
        .btn-toolbar svg { width: 15px; height: 15px; }
        .btn-print-action { background: #16a34a; color: #fff; }
        .btn-print-action:hover { background: #15803d; }
        .btn-download-action { background: #f59e0b; color: #fff; }
        .btn-download-action:hover { background: #d97706; }
        .btn-close-action { background: #ef4444; color: #fff; }
        .btn-close-action:hover { background: #dc2626; }
    </style>

    {{-- Backdrop + Modal --}}
        <div x-show="showPrintModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="print-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;"
             @keydown.escape.window="showPrintModal = false">

            {{-- Modal Container --}}
            <div x-show="showPrintModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="print-modal-container bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">

                {{-- Toolbar --}}
                <div class="flex items-center justify-between px-5 py-3 bg-slate-800 text-white rounded-t-xl flex-shrink-0">
                    <span class="font-semibold text-sm tracking-wide">Preview Cetak — {{ $trialPm->code }}</span>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                onclick="document.getElementById('printPreviewFrame').contentWindow.print()"
                                class="btn-toolbar btn-print-action">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print
                        </button>
                        <button type="button"
                                onclick="document.getElementById('printPreviewFrame').contentWindow.print()"
                                class="btn-toolbar btn-download-action">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download PDF
                        </button>
                        <button type="button"
                                x-on:click="showPrintModal = false"
                                class="btn-toolbar btn-close-action">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tutup
                        </button>
                    </div>
                </div>

                {{-- Iframe Preview (A4 page view) --}}
                <div class="print-iframe-wrapper flex-1">
                    <iframe id="printPreviewFrame"
                            src=""
                            frameborder="0"
                            loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

    </div>
    </div>
</x-app-layout>
