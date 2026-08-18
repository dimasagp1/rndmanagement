<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('sample-evaluations.index') }}" class="hover:text-primary transition shrink-0">Sample Evaluation</a>
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium truncate">{{ $sampleEvaluation->sample_id }}</span>
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

    @if($errors->any())
    <div class="alert-danger mb-4" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-medium">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside text-sm mt-1">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="page-header">
        <div class="min-w-0">
            <div class="flex items-center gap-3">
                <h1 class="page-title">{{ $sampleEvaluation->sample_id }}</h1>
                @if($sampleEvaluation->status === 'Approved')
                <span class="badge bg-emerald-100 text-emerald-700">✅ Approved</span>
                @elseif($sampleEvaluation->status === 'Reform')
                <span class="badge bg-amber-100 text-amber-700">↻ Reform</span>
                @else
                <span class="badge bg-gray-100 text-gray-500">In Progress</span>
                @endif
            </div>
            <p class="page-subtitle truncate">{{ $sampleEvaluation->product_name }}</p>
        </div>
        @can('edit', $sampleEvaluation)
        <a href="{{ route('sample-evaluations.edit', $sampleEvaluation) }}" class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </a>
        @endcan
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        {{-- ─── Main: Evaluation History ─────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">
            @can('edit', $sampleEvaluation)
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-ink">Tambah Sesi Evaluasi</h2>
                    <p class="text-xs text-gray-500">Riwayat baru untuk batch percobaan berikutnya</p>
                </div>
                <form method="POST" action="{{ route('sample-evaluations.sessions.store', $sampleEvaluation) }}" class="p-6 space-y-5">
                    @csrf
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trial Number <span class="text-red-500">*</span></label>
                            <input type="number" name="trial_batch" min="1" value="{{ old('trial_batch') }}" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Panelis <span class="text-red-500">*</span></label>
                            <select name="evaluator_type" class="form-input" required>
                                <option value="Internal" {{ old('evaluator_type') === 'External' ? '' : 'selected' }}>Internal</option>
                                <option value="External" {{ old('evaluator_type') === 'External' ? 'selected' : '' }}>External</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                            <select name="decision" class="form-input">
                                <option value="">— Belum diputuskan —</option>
                                <option value="Approved" {{ old('decision') === 'Approved' ? 'selected' : '' }}>Approved (Lulus)</option>
                                <option value="Reform" {{ old('decision') === 'Reform' ? 'selected' : '' }}>Reform (Reformulasi)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Evaluation Parameters <span class="text-red-500">*</span></label>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Hasil</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(App\Models\SampleEvaluation::PARAMETERS as $parameter)
                                    <tr>
                                        <td class="font-medium text-ink">{{ $parameter }}</td>
                                        <td>
                                            <input type="text" name="parameters[{{ $parameter }}][score]"
                                                   value="{{ old('parameters.'.$parameter.'.score') }}"
                                                   placeholder="Hasil manual..." class="form-input" required>
                                        </td>
                                        <td>
                                            <input type="text" name="parameters[{{ $parameter }}][note]"
                                                   value="{{ old('parameters.'.$parameter.'.note') }}"
                                                   placeholder="Catatan opsional" class="form-input">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Saran</label>
                            <textarea name="evaluation_result" rows="3" class="form-input">{{ old('evaluation_result') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kesimpulan</label>
                            <textarea name="sensory_result" rows="3" class="form-input">{{ old('sensory_result') }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="submit" class="btn-primary">Simpan Sesi Evaluasi</button>
                    </div>
                </form>
            </div>
            @endcan

            {{-- Riwayat Sesi --}}
            @forelse($sampleEvaluation->sessions as $session)
            <div class="card">
                <div class="card-header flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="badge bg-primary/10 text-primary">Sesi {{ $session->session_no }}</span>
                        <span class="text-xs text-gray-500 shrink-0">Batch #{{ $session->trial_batch }}</span>
                        @if($session->decision === 'Approved')
                        <span class="badge bg-emerald-100 text-emerald-700">✅ Approved</span>
                        @elseif($session->decision === 'Reform')
                        <span class="badge bg-amber-100 text-amber-700">↻ Reform</span>
                        @endif
                    </div>
                    @can('edit', $sampleEvaluation)
                    <form method="POST" action="{{ route('sample-evaluations.sessions.destroy', [$sampleEvaluation, $session]) }}"
                          onsubmit="return confirm('Hapus sesi evaluasi ini beserta parameternya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-ghost btn-sm text-red-600 hover:text-red-700">Hapus</button>
                    </form>
                    @endcan
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400">Evaluator</p>
                            <p class="font-medium text-ink">{{ $session->evaluator_type }} ({{ $session->evaluator?->name ?? '—' }})</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Tanggal Evaluasi</p>
                            <p class="font-medium text-ink">{{ $session->evaluated_at?->format('d M Y H:i') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Hasil Parameter</p>
                            <p class="font-medium text-ink text-xs">
                                @foreach($session->parameters as $p)
                                <span class="mr-2">{{ $p->parameter }}: <span class="text-gray-600">{{ $p->score }}</span></span>
                                @endforeach
                            </p>
                        </div>
                    </div>

                    @if($session->parameters->isNotEmpty())
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Hasil</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($session->parameters as $param)
                                <tr>
                                    <td class="font-medium text-ink">{{ $param->parameter }}</td>
                                    <td class="text-sm text-ink">{{ $param->score }}</td>
                                    <td class="text-xs text-gray-500">{{ $param->note ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($session->evaluation_result || $session->sensory_result)
                    <div class="grid md:grid-cols-2 gap-4">
                        @if($session->evaluation_result)
                        <div class="bg-surface rounded-lg p-3">
                            <p class="text-xs text-gray-400 font-medium mb-1">Evaluation Result</p>
                            <p class="text-sm text-ink whitespace-pre-wrap">{{ $session->evaluation_result }}</p>
                        </div>
                        @endif
                        @if($session->sensory_result)
                        <div class="bg-surface rounded-lg p-3">
                            <p class="text-xs text-gray-400 font-medium mb-1">Sensory Result</p>
                            <p class="text-sm text-ink whitespace-pre-wrap">{{ $session->sensory_result }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Attachment --}}
                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-ink">Attachment</p>
                            @can('edit', $sampleEvaluation)
                            <button type="button" onclick="document.getElementById('upload-{{ $session->id }}').classList.toggle('hidden')"
                                    class="btn-ghost btn-sm text-primary">+ Unggah Lampiran</button>
                            @endcan
                        </div>

                        <form method="POST" action="{{ route('sample-evaluations.sessions.attachments.store', [$sampleEvaluation, $session]) }}"
                              enctype="multipart/form-data" id="upload-{{ $session->id }}" class="hidden mb-3 p-3 bg-surface rounded-lg space-y-2">
                            @csrf
                            <div class="flex flex-wrap items-center gap-2">
                                <select name="type" class="form-input text-sm" required>
                                    @foreach(['Form Panel', 'Blind Code', 'Report Panel Test', 'Data Panelis', 'Result'] as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                                <input type="file" name="file" class="form-input text-sm flex-1 min-w-40" required>
                                <button type="submit" class="btn-primary btn-sm">Unggah</button>
                            </div>
                            <p class="text-xs text-gray-400">Maks 10MB: PDF, gambar, doc, xls, zip</p>
                        </form>

                        @if($session->attachments->isEmpty())
                        <p class="text-xs text-gray-400">Belum ada lampiran.</p>
                        @else
                        <ul class="space-y-1.5">
                            @foreach($session->attachments as $attachment)
                            <li class="flex items-center justify-between gap-2 bg-surface rounded-lg px-3 py-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="badge bg-white border border-gray-200 text-gray-500 shrink-0">{{ $attachment->type }}</span>
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                       class="text-xs text-primary hover:underline truncate">
                                        {{ $attachment->original_name }}
                                    </a>
                                    <span class="text-xs text-gray-400 shrink-0">— {{ $attachment->uploader?->name ?? '—' }}</span>
                                </div>
                                @can('edit', $sampleEvaluation)
                                <form method="POST" action="{{ route('sample-evaluations.attachments.destroy', [$sampleEvaluation, $attachment]) }}"
                                      onsubmit="return confirm('Hapus lampiran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <x-empty-state
                icon="trial"
                title="Belum Ada Sesi Evaluasi"
                description="Tambahkan sesi evaluasi pertama untuk batch percobaan sampel ini."
            />
            @endforelse
        </div>

        {{-- ─── Sidebar ─────────────────────────────────────────────── --}}
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-ink">Informasi Sample</h2>
                </div>
                <dl class="p-6 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400">Sample ID</dt>
                        <dd class="font-medium text-ink font-mono">{{ $sampleEvaluation->sample_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Product Name</dt>
                        <dd class="font-medium text-ink">
                            @if($sampleEvaluation->npdProposal)
                            <a href="{{ route('npd-proposals.show', $sampleEvaluation->npdProposal) }}" class="hover:text-primary hover:underline">
                                {{ $sampleEvaluation->product_name }}
                            </a>
                            <span class="text-xs text-gray-400 font-mono ml-1">({{ $sampleEvaluation->npdProposal->code }})</span>
                            @else
                            {{ $sampleEvaluation->product_name }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Project Owner</dt>
                        <dd class="font-medium text-ink">{{ $sampleEvaluation->projectOwner?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Status</dt>
                        <dd>
                            @if($sampleEvaluation->status === 'Approved')
                            <span class="badge bg-emerald-100 text-emerald-700">✅ Approved</span>
                            @elseif($sampleEvaluation->status === 'Reform')
                            <span class="badge bg-amber-100 text-amber-700">↻ Reform</span>
                            @else
                            <span class="badge bg-gray-100 text-gray-500">In Progress</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Tanggal Dibuat</dt>
                        <dd class="font-medium text-ink">{{ $sampleEvaluation->created_at->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Dibuat Oleh</dt>
                        <dd class="font-medium text-ink">{{ $sampleEvaluation->creator?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Jumlah Sesi Evaluasi</dt>
                        <dd class="font-medium text-ink">{{ $sampleEvaluation->sessions->count() }} sesi</dd>
                    </div>
                </dl>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-ink">Aktivitas Terakhir</h2>
                </div>
                <ul class="p-6 space-y-3">
                    @forelse($sampleEvaluation->activities->take(5) as $activity)
                    <li class="text-xs text-gray-500">
                        <span class="font-medium text-ink">{{ $activity->causer?->name ?? 'Sistem' }}</span>
                        {{ $activity->description }} — {{ $activity->created_at->diffForHumans() }}
                    </li>
                    @empty
                    <li class="text-xs text-gray-400">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>