<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Preformulation Study</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Daftar Study</span>
        </div>
    </x-slot>

    <div class="min-h-screen">
        <header class="flex flex-wrap justify-between items-start gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-heading font-bold text-ink mb-1">Preformulation Study</h1>
                <p class="text-sm text-gray-500 max-w-2xl">
                    Penyusunan study preformulation untuk menentukan mutu produk di tahap awal pengembangan.
                </p>
            </div>
            @can('npd_proposal.create')
            <a href="{{ route('preformulation-studies.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Study Baru
            </a>
            @endcan
        </header>

        <div class="card card-body mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <a href="{{ route('preformulation-studies.index') }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ !request('status') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua ({{ $counts['all'] }})
                </a>
                <a href="{{ route('preformulation-studies.index', ['status' => 'Draft']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Draft' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Draft ({{ $counts['draft'] }})
                </a>
                <a href="{{ route('preformulation-studies.index', ['status' => 'In Progress']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'In Progress' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    In Progress ({{ $counts['in_progress'] }})
                </a>
                <a href="{{ route('preformulation-studies.index', ['status' => 'Completed']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Completed' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Completed ({{ $counts['completed'] }})
                </a>
                <a href="{{ route('preformulation-studies.index', ['status' => 'On Hold']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'On Hold' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    On Hold ({{ $counts['on_hold'] }})
                </a>
                <div class="flex-1"></div>
                <form method="GET" class="w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari kode, nama produk, PIC..."
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-primary focus:ring-primary">
                </form>
            </div>
        </div>

        <div class="card shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-gray-500 font-bold uppercase tracking-wider bg-white border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4" scope="col">Kode</th>
                            <th class="px-6 py-4" scope="col">Product Name</th>
                            <th class="px-6 py-4" scope="col">NPD Proposal</th>
                            <th class="px-6 py-4" scope="col">Study Type</th>
                            <th class="px-6 py-4" scope="col">Project Owner</th>
                            <th class="px-6 py-4" scope="col">Status</th>
                            <th class="px-6 py-4" scope="col">Approval</th>
                            <th class="px-6 py-4" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($studies as $study)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $study->code }}</td>
                            <td class="px-6 py-4 font-semibold text-ink">{{ $study->product_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $study->npdProposal?->code ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $study->study_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $study->project_owner ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'Draft' => 'bg-gray-100 text-gray-700',
                                        'In Progress' => 'bg-blue-100 text-blue-700',
                                        'Completed' => 'bg-emerald-100 text-emerald-700',
                                        'On Hold' => 'bg-amber-100 text-amber-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$study->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $study->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $approvalColors = [
                                        'Draft' => 'bg-gray-100 text-gray-600',
                                        'Pending Tahap 1' => 'bg-yellow-100 text-yellow-700',
                                        'Pending Tahap 2' => 'bg-orange-100 text-orange-700',
                                        'Approved' => 'bg-emerald-100 text-emerald-700',
                                        'Rejected' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $approvalColors[$study->approval_status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $study->approval_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('preformulation-studies.show', $study) }}" class="text-primary hover:underline text-sm font-medium">Lihat</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                Belum ada preformulation study.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-100">
                {{ $studies->links() }}
            </div>
        </div>
    </div>
</x-app-layout>