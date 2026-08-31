<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Approval Formula & Design</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="page-title">Approval Formula & Design</h1>
                <p class="page-subtitle">Persetujuan final formula & artwork sebelum registrasi & produksi. Revision, approver & approval date terekam otomatis (approval online).</p>
            </div>
            @php($currentType = request('type') === 'Design' ? 'Design' : 'Formula')
            @can('formula.edit')
            <a href="{{ route('approval-formula-designs.create', ['type' => $currentType]) }}" class="btn-primary flex-shrink-0 self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Approval {{ $currentType }}
            </a>
            @endcan
        </div>
        <div class="flex w-full justify-end">
            <form method="GET" action="{{ route('approval-formula-designs.index') }}" class="flex items-center gap-2 justify-end ml-auto">
                <div class="relative flex items-center">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari produk / artwork..."
                           class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if(request('search'))
                    <a href="{{ route('approval-formula-designs.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php($showTracker = request('type') !== 'Formula')
    @php($currentApprovalInternal = request('approval_internal'))

    @if($typeFilter === 'Design')
    <div class="flex gap-1 mb-4 bg-gray-100 rounded-lg p-1 w-fit">
        <a href="{{ route('approval-formula-designs.index', ['type' => 'Design']) }}"
           class="px-4 py-1.5 rounded-md text-xs font-medium transition {{ !$currentApprovalInternal ? 'bg-white shadow-sm text-ink font-semibold' : 'text-gray-500 hover:text-gray-700' }}">
            Semua
        </a>
        <a href="{{ route('approval-formula-designs.index', ['type' => 'Design', 'approval_internal' => 'Maklon']) }}"
           class="px-4 py-1.5 rounded-md text-xs font-medium transition {{ $currentApprovalInternal === 'Maklon' ? 'bg-white shadow-sm text-ink font-semibold' : 'text-gray-500 hover:text-gray-700' }}">
            Maklon
        </a>
        <a href="{{ route('approval-formula-designs.index', ['type' => 'Design', 'approval_internal' => 'Vitabrand']) }}"
           class="px-4 py-1.5 rounded-md text-xs font-medium transition {{ $currentApprovalInternal === 'Vitabrand' ? 'bg-white shadow-sm text-ink font-semibold' : 'text-gray-500 hover:text-gray-700' }}">
            Vitabrand
        </a>
    </div>
    @endif

    <div class="card">
        @if($forms->isEmpty())
        <x-empty-state
            icon="approval"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Final Approval' }}"
            description="{{ request('search') ? 'Coba kata kunci lain.' : 'Final approval formula & artwork akan tampil di sini (Staff dapat membuat, GM approve online).' }}"
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">Code</th>
                        <th>Product / Artwork</th>
                        @if($showTracker)<th>Tracker Status</th>@endif
                        <th>Final Doc</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                    <tr>
                        <td class="text-xs font-mono text-gray-500">
                            <div>{{ $form->code }}</div>
                            <div class="text-[10px] text-gray-400">{{ $form->created_at?->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="font-semibold text-ink text-sm">{{ $form->product_name }}</div>
                            <div class="text-xs text-gray-500">
                                @if($form->artwork_title)
                                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg> {{ $form->artwork_title }} @if($form->artwork_version)<span class="text-gray-400">· {{ $form->artwork_version }}</span>@endif</span>
                                @else
                                    <span class="text-gray-400">— Tanpa artwork</span>
                                @endif
                            </div>
                            @if($form->formula)
                            <div class="text-[11px] text-primary">Formula: {{ $form->formula->code }}</div>
                            @endif
                        </td>
                        @if($showTracker)
                        <td class="text-xs">
                            @php(
                                $stepIdx = $form->tracker_status
                                    ? array_search($form->tracker_status, \App\Models\FormulaApprovalForm::TRACKER_STATUSES)
                                    : -1
                            )
                            @php($stepColors = ['bg-emerald-500', 'bg-blue-500', 'bg-violet-500', 'bg-gray-500'])
                                <details class="relative group/trk tracker-dropdown">
                                    <summary class="inline-flex items-center gap-1.5 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">
                                        <div class="flex items-center gap-0.5">
                                            @foreach(\App\Models\FormulaApprovalForm::TRACKER_STATUSES as $ti => $tst)
                                            <span class="w-2 h-2 rounded-full transition-all {{ ($stepIdx !== false && $ti <= $stepIdx) ? ($stepColors[$ti] ?? 'bg-primary') : 'bg-gray-200' }}"></span>
                                            @endforeach
                                        </div>
                                        <span class="text-[11px] font-semibold text-ink group-hover/trk:text-primary transition">{{ \Illuminate\Support\Str::limit($form->tracker_status ?? 'Set Tracker', 18) }}</span>
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </summary>
                                    <div class="absolute right-0 top-full mt-1 z-30 bg-white border border-gray-200 rounded-xl shadow-lg py-1 w-52">
                                        @can('formula.edit')
                                        @foreach(\App\Models\FormulaApprovalForm::TRACKER_STATUSES as $tst)
                                        <button type="button"
                                                class="tracker-pick w-full flex items-center gap-2 px-3 py-1.5 text-xs hover:bg-surface transition text-left {{ $form->tracker_status === $tst ? 'font-semibold bg-primary/5' : '' }}"
                                                data-id="{{ $form->id }}" data-status="{{ $tst }}">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ in_array($form->tracker_status ?? '', array_slice(\App\Models\FormulaApprovalForm::TRACKER_STATUSES, 0, $loop->index + 1)) ? 'bg-primary' : 'bg-gray-200' }}"></span>
                                            {{ $tst }}
                                            @if($form->tracker_status === $tst)<span class="ml-auto text-primary">✓</span>@endif
                                        </button>
                                        @endforeach
                                        @else
                                        <p class="px-3 py-1.5 text-xs text-gray-400">{{ $form->tracker_status ?? '—' }}</p>
                                        @endcan
                                        <div class="border-t border-gray-100 mt-1 pt-1">
                                            <button type="button" class="tracker-history-btn w-full text-left px-3 py-1.5 text-[11px] text-primary hover:bg-surface flex items-center gap-1.5"
                                                    data-history='@json($form->tracker_history ?? [])' data-code="{{ $form->code }}" data-current="{{ $form->tracker_status ?? '-' }}">
                                                🕐 Riwayat ({{ count($form->tracker_history ?? []) }})
                                            </button>
                                        </div>
                                    </div>
                                </details>
                        </td>
                        @endif

                        <td class="text-center">
                            @if($form->final_document_path)
                            <a href="{{ Storage::url($form->final_document_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-green-600 hover:underline">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Final
                            </a>
                            @elseif($form->artwork_file_path)
                            <span class="text-xs text-gray-400">Artwork ready</span>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('approval-formula-designs.show', ['formApproval' => $form, 'type' => $form->type]) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('formula.edit')
                                    @if(!in_array($form->approval_status, ['Pending','Approved']) || ($form->type === 'Design' && $form->approval_status === 'Approved'))
                                        <a href="{{ route('approval-formula-designs.edit', ['formApproval' => $form, 'type' => $form->type]) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-400 text-sm">Belum ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $forms->links() }}
        </div>
        @endif
    </div>

    {{-- Tracker History Modal --}}
    <div id="trackerHistoryModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" onclick="document.getElementById('trackerHistoryModal').classList.add('hidden')"></div>
        <div class="relative mx-auto mt-24 max-w-md bg-white rounded-2xl shadow-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-ink">Tracker History — <span id="trackerHistoryCode" class="font-mono text-primary"></span></h3>
                <button onclick="document.getElementById('trackerHistoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div id="trackerHistoryList" class="space-y-2 max-h-80 overflow-y-auto text-xs"></div>
            <div class="mt-4 flex justify-end">
                <button onclick="document.getElementById('trackerHistoryModal').classList.add('hidden')" class="btn-ghost btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    <script>
    // Tutup semua tracker dropdown saat klik di luar
    document.addEventListener('click', function(e){
        if(!e.target.closest('.tracker-dropdown')){
            document.querySelectorAll('details.tracker-dropdown[open]').forEach(d => d.removeAttribute('open'));
        }
    });

    // Tutup dropdown lain saat satu dibuka
    document.querySelectorAll('details.tracker-dropdown').forEach(d => {
        d.addEventListener('toggle', function(){
            if(this.open){
                document.querySelectorAll('details.tracker-dropdown').forEach(other => {
                    if(other !== this) other.removeAttribute('open');
                });
            }
        });
    });

    // Event delegation: klik status di dropdown tracker → langsung PATCH tanpa reload/alert
    document.addEventListener('click', function(e){
        const pick = e.target.closest('.tracker-pick');
        if(pick){
            const id = pick.dataset.id;
            const status = pick.dataset.status;
            const details = pick.closest('details');
            pick.style.opacity = '0.5';
            fetch('{{ url("approval-formula-designs") }}/' + id + '/tracker', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({tracker_status: status})
            })
            .then(r => r.json().then(j => ({ok: r.ok, j})))
            .then(({ok, j}) => {
                if(ok){
                    details.open = false;
                    toastMsg('Tracker diperbarui ke "' + status + '"', true);
                    setTimeout(() => window.location.reload(), 700);
                } else {
                    toastMsg(j.message || 'Gagal update tracker', false);
                    pick.style.opacity = '1';
                }
            })
            .catch(() => { toastMsg('Error jaringan', false); pick.style.opacity = '1'; });
        }

        const histBtn = e.target.closest('.tracker-history-btn');
        if(histBtn){
            const history = JSON.parse(histBtn.dataset.history || '[]');
            document.getElementById('trackerHistoryCode').textContent = histBtn.dataset.code;
            const list = document.getElementById('trackerHistoryList');
            list.innerHTML = history.length
                ? history.map((h,i)=>`<div class="flex justify-between items-center p-2 rounded border ${i===history.length-1?'bg-primary/5 border-primary/20':'bg-gray-50 border-gray-100'}"><div><p class="font-semibold text-ink">${h.status}</p><p class="text-gray-400">${h.updated_name||'—'} · ${new Date(h.updated_at).toLocaleString('id-ID')}</p></div><span class="text-xs font-mono text-gray-400">#${i+1}</span></div>`).join('')
                : '<p class="text-gray-400 text-center py-4">Belum ada history tracker.</p>';
            document.getElementById('trackerHistoryModal').classList.remove('hidden');
        }
    });

    function toastMsg(msg, ok){
        const el = document.createElement('div');
        el.className = 'fixed top-4 right-4 z-[9999] px-4 py-2.5 rounded-xl text-sm shadow-lg flex items-center gap-2 transition-opacity duration-300 ' + (ok ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(()=>{ el.style.opacity='0'; setTimeout(()=>el.remove(),300); }, 2500);
    }
    </script>
</x-app-layout>
