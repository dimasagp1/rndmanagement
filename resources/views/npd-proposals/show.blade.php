<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('npd-proposals.index') }}" class="hover:text-primary">NPD Proposal</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $proposal->code }}</span>
        </div>
    </x-slot>

    {{-- Flash messages --}}
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

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $proposal->code }}</code>
                <x-status-badge :status="$proposal->project_status" />
            </div>
            <h1 class="page-title">{{ $proposal->product_name }}</h1>
            <p class="page-subtitle">
                Berdasarkan PRF <a href="{{ route('prfs.show', $proposal->prf_id) }}" class="text-primary hover:underline font-mono">{{ $proposal->prf?->code ?? '—' }}</a>
                · PIC {{ $proposal->pic ?? '—' }} · Dibuat oleh {{ $proposal->creator?->name ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('edit', $proposal)
            <a href="{{ route('npd-proposals.edit', $proposal) }}" class="btn-outline">Edit</a>
            @endcan

            @can('delete', $proposal)
            <form method="POST" action="{{ route('npd-proposals.destroy', $proposal) }}" class="inline"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus NPD Proposal {{ $proposal->code }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endcan

            <a href="{{ route('npd-proposals.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Info Proyek --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi Proyek</h2>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">PRF</p>
                        <a href="{{ route('prfs.show', $proposal->prf_id) }}" class="font-mono text-primary hover:underline">{{ $proposal->prf?->code ?? '—' }}</a>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">PIC / Project Owner</p>
                        <p class="font-semibold text-ink">{{ $proposal->pic ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Target COGS / HPP</p>
                        <p class="font-semibold text-ink">{{ $proposal->formatted_cogs }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Target Selling Price</p>
                        <p class="font-semibold text-ink">{{ $proposal->formatted_selling_price }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Development Mulai</p>
                        <p>{{ $proposal->development_start?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Development Selesai</p>
                        <p>{{ $proposal->development_end?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Project Status</p>
                        <x-status-badge :status="$proposal->project_status" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dibuat Oleh</p>
                        <p>{{ $proposal->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Dibuat</p>
                        <p>{{ $proposal->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
                    </div>
                </div>
            </div>

            {{-- Product Concept --}}
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Product Concept</h2></div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $proposal->product_concept }}</p>
                </div>
            </div>

            {{-- Project Team --}}
            @if($proposal->project_team)
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Project Team</h2></div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $proposal->project_team }}</p>
                </div>
            </div>
            @endif

            {{-- Attachment --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Attachment</h2>
                    @if($proposal->documents->count() > 0)
                    <span class="badge bg-emerald-100 text-emerald-700 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $proposal->documents->count() }} file terunggah
                    </span>
                    @else
                    <span class="badge bg-gray-100 text-gray-500">Belum ada file</span>
                    @endif
                </div>
                <div class="card-body space-y-2">
                    @forelse($proposal->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-emerald-50/40 border border-emerald-200 rounded-lg">
                        <div class="flex items-center gap-3 min-w-0 pr-2">
                            <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-semibold text-gray-800 truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</p>
                                    <span class="badge bg-emerald-100 text-emerald-700 flex items-center gap-0.5 flex-shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Terunggah
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-400">{{ $doc->formatted_size }} • {{ $doc->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener"
                               class="btn-ghost btn-sm text-xs text-gray-600 hover:bg-gray-100 flex items-center gap-1" title="Unduh File">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh
                            </a>
                            @can('edit', $proposal)
                            <form method="POST" action="{{ route('npd-proposals.documents.destroy', $doc) }}"
                                  onsubmit="return confirm('Hapus dokumen {{ $doc->file_name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus Dokumen">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                        <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm font-semibold text-gray-500">Belum ada dokumen terunggah</p>
                        @can('edit', $proposal)
                        <p class="text-xs text-gray-400 mt-1">Klik tombol <span class="font-semibold text-emerald-600">Edit</span> untuk menambahkan file pendukung.</p>
                        @else
                        <p class="text-xs text-gray-400 mt-1">Pembuat proposal belum melampirkan dokumen pendukung.</p>
                        @endcan
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR ─────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Update Project Status --}}
            @can('updateProjectStatus', $proposal)
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Perbarui Status Proyek</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('npd-proposals.project-status', $proposal) }}" class="space-y-3">
                        @csrf
                        <select name="project_status" class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            @foreach(\App\Services\NpdProposalService::PROJECT_STAGES as $stage)
                            <option value="{{ $stage }}" {{ $proposal->project_status === $stage ? 'selected' : '' }}>{{ $stage }}</option>
                            @endforeach
                        </select>
                        @error('project_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>