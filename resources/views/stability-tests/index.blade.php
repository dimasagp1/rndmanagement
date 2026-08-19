<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Stability Test</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Stability Test</h1>
            <p class="page-subtitle">Monitoring uji stabilitas produk: protokol, jadwal pengujian, parameter hasil uji, due date, dan OOS/issue tracking.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('stability-tests.index') }}" class="relative flex items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari produk / batch..."
                       class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                <a href="{{ route('stability-tests.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan Pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
            </form>
            @can('stability_test.edit')
            <a href="{{ route('stability-tests.create') }}" class="btn-primary flex-shrink-0" title="Buat Stability Test Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah
            </a>
            @endcan
        </div>
    </div>

    {{-- Status Filter --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('stability-tests.index', array_merge(request()->except('status'), ['status' => null])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold {{ ! request('status') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            Semua
        </a>
        @foreach(\App\Models\StabilityTest::STATUSES as $status)
        <a href="{{ route('stability-tests.index', array_merge(request()->except('status'), ['status' => $status])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold {{ request('status') === $status ? 'bg-primary text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            {{ $status }}
        </a>
        @endforeach
    </div>

    <div class="card">
        @if($tests->isEmpty())
        <x-empty-state
            icon="approval"
            title="{{ request('search') || request('status') ? 'Tidak Ada Hasil' : 'Belum Ada Stability Test' }}"
            description="{{ request('search') || request('status') ? 'Coba kata kunci lain atau ubah filter.' : 'Buat Stability Test pertama untuk memantau stabilitas produk Anda.' }}"
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Code</th>
                        <th>Product Name</th>
                        <th>Batch</th>
                        <th>Kondisi Penyimpanan</th>
                        <th>Status</th>
                        <th>Due Date Terdekat</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tests as $index => $test)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($tests->currentPage() - 1) * $tests->perPage() }}</td>
                        <td class="text-xs font-mono text-primary">{{ $test->code }}</td>
                        <td>
                            <div class="font-semibold text-ink">{{ $test->product_name }}</div>
                            <div class="text-xs text-gray-400">{{ $test->product?->name ?? '—' }}</div>
                        </td>
                        <td class="text-xs text-gray-500 font-mono">{{ $test->batch_number }}</td>
                        <td class="text-xs text-gray-500">{{ $test->storage_condition }}</td>
                        <td>
                            @php($badge = [
                                'Draft'             => 'bg-gray-100 text-gray-600',
                                'Pending Protokol'  => 'bg-amber-100 text-amber-700',
                                'Protokol Approved' => 'bg-blue-100 text-blue-700',
                                'Pending Laporan'   => 'bg-violet-100 text-violet-700',
                                'Approved'          => 'bg-green-100 text-green-700',
                                'Rejected'          => 'bg-red-100 text-red-700',
                            ][$test->approval_status] ?? 'bg-gray-100 text-gray-600')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                {{ $test->approval_status }}
                            </span>
                        </td>
                        <td class="text-xs text-gray-500 whitespace-nowrap">
                            @php($nextDue = $test->schedules->where('status', 'Pending')->sortBy('due_date')->first())
                            @if($nextDue)
                                {{ $nextDue->due_date?->format('d M Y') }}
                                @if($nextDue->due_date?->isPast())
                                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600">OVERDUE</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('stability-tests.show', $test) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('stability_test.edit')
                                @if(in_array($test->approval_status, ['Draft', 'Protokol Approved', 'Rejected']))
                                <a href="{{ route('stability-tests.edit', $test) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400 text-sm">Belum ada Stability Test.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $tests->links() }}
        </div>
        @endif
    </div>
</x-app-layout>