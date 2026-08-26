<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Packaging Development</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Packaging Development</h1>
            <p class="page-subtitle">Pengelolaan pengembangan kemasan: spesifikasi, primary & secondary packaging, material, supplier, trial, compatibility, dokumen, dan approval online.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('packaging-developments.index') }}" class="relative flex items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari produk / tipe..."
                       class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                <a href="{{ route('packaging-developments.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan Pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
            </form>
            @can('packaging_development.edit')
            <a href="{{ route('packaging-developments.create') }}" class="btn-primary flex-shrink-0" title="Buat Packaging Development Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah
            </a>
            @endcan
        </div>
    </div>

    {{-- Status Filter --}}
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <a href="{{ route('packaging-developments.index', array_merge(request()->except('status'), ['status' => null])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold {{ ! request('status') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            Semua
        </a>
        @foreach(\App\Models\PackagingDevelopment::APPROVAL_STATUSES as $status)
        <a href="{{ route('packaging-developments.index', array_merge(request()->except('status'), ['status' => $status])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold {{ request('status') === $status ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            {{ $status }}
        </a>
        @endforeach
    </div>
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('packaging-developments.index', array_merge(request()->except('stage'), ['stage' => null])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold {{ ! request('stage') ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            Stage: Semua
        </a>
        @foreach(\App\Models\PackagingDevelopment::DEVELOPMENT_STAGES as $stage)
        <a href="{{ route('packaging-developments.index', array_merge(request()->except('stage'), ['stage' => $stage])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold {{ request('stage') === $stage ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            {{ $stage }}
        </a>
        @endforeach
    </div>

    <div class="card">
        @if($developments->isEmpty())
        <x-empty-state
            icon="approval"
            title="{{ request('search') || request('status') || request('stage') ? 'Tidak Ada Hasil' : 'Belum Ada Packaging Development' }}"
            description="{{ request('search') || request('status') || request('stage') ? 'Coba kata kunci lain atau ubah filter.' : 'Buat Packaging Development pertama untuk mengelola pengembangan kemasan.' }}"
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>No. Packaging Development</th>
                        <th>Product Name</th>
                        <th>Packaging Type</th>
                        <th>Supplier</th>
                        <th>Stage</th>
                        <th>Status</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($developments as $index => $dev)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($developments->currentPage() - 1) * $developments->perPage() }}</td>
                        <td class="text-xs font-mono text-primary whitespace-nowrap">{{ $dev->code }} <span class="ml-1 text-[10px] text-gray-400">{{ $dev->revision_label }}</span></td>
                        <td>
                            <div class="font-semibold text-ink">{{ $dev->product_name }}</div>
                            <div class="text-xs text-gray-400">{{ $dev->product_category }} · {{ $dev->development_purpose }}</div>
                        </td>
                        <td class="text-xs text-gray-500">{{ $dev->packaging_type }}</td>
                        <td class="text-xs text-gray-500">{{ $dev->suppliers->first()?->supplier_name ?? '—' }}</td>
                        <td>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">{{ $dev->development_stage }}</span>
                        </td>
                        <td>
                            @php($badge = [
                                'Draft'      => 'bg-gray-100 text-gray-600',
                                'Pending OM' => 'bg-amber-100 text-amber-700',
                                'Pending GM' => 'bg-violet-100 text-violet-700',
                                'Approved'   => 'bg-green-100 text-green-700',
                                'Rejected'   => 'bg-red-100 text-red-700',
                            ][$dev->approval_status] ?? 'bg-gray-100 text-gray-600')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                {{ $dev->approval_status }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('packaging-developments.show', $dev) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('packaging_development.edit')
                                @if(in_array($dev->approval_status, ['Draft', 'Rejected']))
                                <a href="{{ route('packaging-developments.edit', $dev) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400 text-sm">Belum ada Packaging Development.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $developments->links() }}
        </div>
        @endif
    </div>
</x-app-layout>