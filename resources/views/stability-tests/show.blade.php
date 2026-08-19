<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary">Stability Test</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $stabilityTest->code }}</span>
        </div>
    </x-slot>

    @php($st = $stabilityTest)
    @php($canEdit = auth()->user()->can('stability_test.edit'))
    @php($canMutate = $canEdit && ! in_array($st->approval_status, ['Pending Protokol', 'Pending Laporan', 'Approved']))

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                @php($badge = [
                    'Draft'             => 'bg-gray-100 text-gray-600',
                    'Pending Protokol'  => 'bg-amber-100 text-amber-700',
                    'Protokol Approved' => 'bg-blue-100 text-blue-700',
                    'Pending Laporan'   => 'bg-violet-100 text-violet-700',
                    'Approved'          => 'bg-green-100 text-green-700',
                    'Rejected'          => 'bg-red-100 text-red-700',
                ][$st->approval_status] ?? 'bg-gray-100 text-gray-600')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ $st->approval_status }}</span>
                @if($st->has_oos)
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">⚠ OOS / Issue Aktif</span>
                @endif
            </div>
            <h1 class="page-title">{{ $st->product_name }}</h1>
            <p class="page-subtitle">{{ $st->code }} · Batch {{ $st->batch_number }} · Dibuat {{ $st->created_at?->isoFormat('D MMM Y') ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($canMutate)
            <a href="{{ route('stability-tests.edit', $st) }}" class="btn-outline">Edit</a>
            <form method="POST" action="{{ route('stability-tests.destroy', $st) }}" class="inline"
                  onsubmit="return confirm('Hapus Stability Test untuk {{ $st->product_name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endif

            <button type="button" onclick="window.print()" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>

            <a href="{{ route('stability-tests.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Informasi Produk --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi Produk</h2>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nama Produk</p>
                        <p class="font-semibold text-ink">{{ $st->product?->name ?? $st->product_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Deskripsi</p>
                        <p>{{ $st->product?->description ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Project Owner</p>
                        <p>{{ $st->product?->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Batch Number</p>
                        <p class="font-mono">{{ $st->batch_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kondisi Penyimpanan</p>
                        <p>{{ $st->storage_condition }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dibuat Oleh</p>
                        <p>{{ $st->creator?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Protokol & Kesimpulan --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Stability Protocol & Conclusion</h2>
                </div>
                <div class="card-body space-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Stability Protocol</p>
                        <p class="whitespace-pre-line">{{ $st->stability_protocol ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Stability Conclusion</p>
                        <p class="whitespace-pre-line">{{ $st->stability_conclusion ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Testing Schedule & Parameter Hasil Uji --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Testing Schedule & Parameter Hasil Uji</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('add-schedule').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Tambah Jadwal</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('stability-tests.schedules.store', $st) }}" id="add-schedule"
                          class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input type="text" name="timepoint" placeholder="Titik uji (contoh: Bulan 3)" class="form-input text-sm" required>
                            <input type="date" name="due_date" class="form-input text-sm" required>
                            <button type="submit" class="btn-primary btn-sm justify-center">Simpan Jadwal</button>
                        </div>
                    </form>
                    @endif

                    @if($st->schedules->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada jadwal pengujian.</p>
                    @else
                    <div class="space-y-4">
                        @foreach($st->schedules as $schedule)
                        @php($isOverdue = $schedule->status === 'Pending' && $schedule->due_date?->isPast())
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">{{ $schedule->timepoint }}</span>
                                <span class="text-xs text-gray-500">Due: {{ $schedule->due_date?->format('d M Y') ?? '—' }}</span>
                                @if($isOverdue)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600">OVERDUE</span>
                                @endif
                                @php($scheduleBadge = [
                                    'Pending'   => 'bg-gray-100 text-gray-600',
                                    'Completed' => 'bg-green-100 text-green-700',
                                    'OOS'       => 'bg-red-100 text-red-700',
                                ][$schedule->status] ?? 'bg-gray-100 text-gray-600')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $scheduleBadge }}">{{ $schedule->status }}</span>
                                @if($schedule->tested_at)
                                <span class="text-xs text-gray-400">Diuji {{ $schedule->tested_at?->isoFormat('D MMM Y, HH:mm') }}</span>
                                @endif
                                @if($canMutate)
                                <form method="POST" action="{{ route('stability-tests.schedules.destroy', [$st, $schedule]) }}" class="ml-auto"
                                      onsubmit="return confirm('Hapus jadwal {{ $schedule->timepoint }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                </form>
                                @endif
                            </div>

                            @if($schedule->parameters->isNotEmpty())
                            <div class="overflow-x-auto mb-3">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Spesifikasi</th>
                                            <th>Unit</th>
                                            <th>Hasil</th>
                                            <th>Status</th>
                                            @if($canMutate)
                                            <th class="w-24 text-center">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedule->parameters as $parameter)
                                        <tr>
                                            <td class="text-xs font-medium text-ink">{{ $parameter->parameter }}</td>
                                            <td class="text-xs text-gray-500">{{ $parameter->specification ?? '—' }}</td>
                                            <td class="text-xs text-gray-500">{{ $parameter->unit ?? '—' }}</td>
                                            <td class="text-xs text-gray-500">{{ $parameter->result ?? '—' }}</td>
                                            <td>
                                                @if($parameter->result_status === 'Sesuai')
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Sesuai</span>
                                                @elseif($parameter->result_status === 'Tidak Sesuai')
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Tidak Sesuai</span>
                                                @else
                                                <span class="text-xs text-gray-300">—</span>
                                                @endif
                                            </td>
                                            @if($canMutate)
                                            <td>
                                                <div class="flex items-center justify-center gap-2">
                                                    <button type="button" onclick="document.getElementById('edit-param-{{ $parameter->id }}').classList.toggle('hidden')"
                                                            class="text-xs text-primary hover:underline">Isi Hasil</button>
                                                    <form method="POST" action="{{ route('stability-tests.parameters.destroy', [$st, $schedule, $parameter]) }}"
                                                          onsubmit="return confirm('Hapus parameter ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                            @endif
                                        </tr>
                                        @if($canMutate)
                                        <tr id="edit-param-{{ $parameter->id }}" class="hidden">
                                            <td colspan="6" class="p-2 bg-surface">
                                                <form method="POST" action="{{ route('stability-tests.parameters.update', [$st, $schedule, $parameter]) }}"
                                                      class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="result" value="{{ $parameter->result }}" placeholder="Hasil uji"
                                                           class="form-input text-xs w-32">
                                                    <select name="result_status" class="form-input text-xs">
                                                        <option value="">— Status —</option>
                                                        <option value="Sesuai" {{ $parameter->result_status === 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                                                        <option value="Tidak Sesuai" {{ $parameter->result_status === 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                                    </select>
                                                    <button type="submit" class="btn-primary btn-sm">Simpan</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-xs text-gray-400 mb-3">Belum ada parameter hasil uji.</p>
                            @endif

                            @if($canMutate)
                            <form method="POST" action="{{ route('stability-tests.parameters.store', [$st, $schedule]) }}"
                                  class="flex flex-wrap items-center gap-2 p-2 bg-surface rounded-lg">
                                @csrf
                                <input type="text" name="parameter" placeholder="Parameter (contoh: Kadar air)" class="form-input text-xs flex-1 min-w-36" required>
                                <input type="text" name="specification" placeholder="Spesifikasi" class="form-input text-xs w-28">
                                <input type="text" name="unit" placeholder="Unit" class="form-input text-xs w-20">
                                <button type="submit" class="btn-ghost btn-sm text-primary">+ Parameter</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- OOS / Issue Tracking --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">OOS / Issue Tracking</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('add-issue').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Catat Issue</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('stability-tests.issues.store', $st) }}" id="add-issue"
                          class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <select name="issue_type" class="form-input text-sm" required>
                                <option value="OOS">OOS (Out of Specification)</option>
                                <option value="Deviasi">Deviasi</option>
                            </select>
                            <input type="text" name="description" placeholder="Deskripsi issue..." class="form-input text-sm sm:col-span-2" required>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary btn-sm">Simpan Issue</button>
                        </div>
                    </form>
                    @endif

                    @if($st->issues->isEmpty())
                    <p class="text-xs text-gray-400">Tidak ada issue/OOS tercatat.</p>
                    @else
                    <div class="space-y-2">
                        @foreach($st->issues as $issue)
                        @php($issueBadge = [
                            'Open'          => 'bg-red-100 text-red-700',
                            'Investigating' => 'bg-amber-100 text-amber-700',
                            'Closed'        => 'bg-green-100 text-green-700',
                        ][$issue->status] ?? 'bg-gray-100 text-gray-600')
                        <div class="border border-gray-200 rounded-xl p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $issue->issue_type === 'OOS' ? 'bg-red-50 text-red-600 ring-1 ring-red-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' }}">
                                    {{ $issue->issue_type }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $issueBadge }}">{{ $issue->status }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $issue->creator?->name ?? '—' }} · {{ $issue->created_at?->isoFormat('D MMM Y') }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $issue->description }}</p>
                            @if($issue->resolution)
                            <p class="text-xs text-gray-500 mt-1 bg-surface px-2 py-1 rounded-md">Resolusi: {{ $issue->resolution }}</p>
                            @endif
                            @if($canMutate)
                            <div class="flex items-center gap-2 mt-2">
                                <form method="POST" action="{{ route('stability-tests.issues.update', [$st, $issue]) }}" class="flex flex-wrap items-center gap-2 flex-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-input text-xs">
                                        @foreach(['Open', 'Investigating', 'Closed'] as $s)
                                        <option value="{{ $s }}" {{ $issue->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="resolution" value="{{ $issue->resolution }}" placeholder="Resolusi / tindak lanjut..."
                                           class="form-input text-xs flex-1 min-w-40">
                                    <button type="submit" class="btn-ghost btn-sm text-primary">Update</button>
                                </form>
                                <form method="POST" action="{{ route('stability-tests.issues.destroy', [$st, $issue]) }}"
                                      onsubmit="return confirm('Hapus issue ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Attachment --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Attachment</h2>
                    @if($canEdit && $st->approval_status !== 'Approved')
                    <button type="button" onclick="document.getElementById('add-attachment').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Unggah File</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canEdit && $st->approval_status !== 'Approved')
                    <form method="POST" action="{{ route('stability-tests.attachments.store', $st) }}" id="add-attachment"
                          enctype="multipart/form-data" class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="flex flex-wrap items-center gap-2">
                            <select name="type" class="form-input text-sm" required>
                                @foreach(\App\Models\StabilityTestAttachment::TYPES as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="file" name="file" class="form-input text-sm flex-1 min-w-40" accept=".pdf,.doc,.docx" required>
                            <button type="submit" class="btn-primary btn-sm">Unggah</button>
                        </div>
                        <p class="text-xs text-gray-400">Maks 10MB: PDF, Word (.doc/.docx)</p>
                    </form>
                    @endif

                    @if($st->attachments->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada lampiran.</p>
                    @else
                    <ul class="space-y-1.5">
                        @foreach($st->attachments as $attachment)
                        <li class="flex items-center justify-between gap-2 bg-surface rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="badge bg-white border border-gray-200 text-gray-500 shrink-0">{{ $attachment->type }}</span>
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                   class="text-xs text-primary hover:underline truncate">
                                    {{ $attachment->original_name }}
                                </a>
                                <span class="text-xs text-gray-400 shrink-0">— {{ $attachment->uploader?->name ?? '—' }}</span>
                            </div>
                            @if($canEdit && $st->approval_status !== 'Approved')
                            <form method="POST" action="{{ route('stability-tests.attachments.destroy', [$st, $attachment]) }}"
                                  onsubmit="return confirm('Hapus lampiran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR ─────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Alur Approval --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Alur Approval</h2>
                </div>
                <div class="card-body">
                    <x-approval-timeline :steps="[
                        [
                            'label'    => 'Staff R&D',
                            'sublabel' => 'Pembuatan & Pengajuan Protokol',
                            'status'   => $st->submitted_at ? 'completed' : 'current',
                            'user'     => $st->creator?->name,
                            'date'     => $st->submitted_at?->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Approval Protokol Stabilitas',
                            'status'   => $st->approved_at_om
                                ? 'completed'
                                : ($st->approval_status === 'Rejected' && ! $st->approved_at_om
                                    ? 'rejected'
                                    : ($st->approval_status === 'Pending Protokol' ? 'current' : 'pending')),
                            'user'     => $st->omApprover?->name,
                            'date'     => $st->approved_at_om?->isoFormat('D MMM Y'),
                            'notes'    => $st->approval_status === 'Rejected' && ! $st->approved_at_om ? $st->rejection_notes : null,
                        ],
                        [
                            'label'    => 'Staff R&D',
                            'sublabel' => 'Pelaksanaan Uji & Penyusunan Laporan',
                            'status'   => $st->report_submitted_at
                                ? 'completed'
                                : ($st->approval_status === 'Protokol Approved' ? 'current' : 'pending'),
                            'user'     => $st->creator?->name,
                            'date'     => $st->report_submitted_at?->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Approval Laporan Hasil Stabilitas',
                            'status'   => $st->approved_at_gm
                                ? 'completed'
                                : ($st->approval_status === 'Rejected' && $st->report_submitted_at
                                    ? 'rejected'
                                    : ($st->approval_status === 'Pending Laporan' ? 'current' : 'pending')),
                            'user'     => $st->gmApprover?->name,
                            'date'     => $st->approved_at_gm?->isoFormat('D MMM Y'),
                            'notes'    => $st->approval_status === 'Rejected' && $st->report_submitted_at ? $st->rejection_notes : null,
                        ],
                    ]" />
                </div>
            </div>

            {{-- E-Approval --}}
            @php($isOm = auth()->user()->hasRole('Operational Manager') || auth()->user()->hasRole('Superadmin'))
            @php($isGm = auth()->user()->hasRole('General Manager') || auth()->user()->hasRole('Superadmin'))
            @php($isOmTurn = $isOm && $st->approval_status === 'Pending Protokol')
            @php($isGmTurn = $isGm && $st->approval_status === 'Pending Laporan')
            @php($canSubmitProtocol = $canEdit && $st->approval_status === 'Draft')
            @php($canSubmitReport = $canEdit && $st->approval_status === 'Protokol Approved')

            @if($isOmTurn || $isGmTurn || $canSubmitProtocol || $canSubmitReport)
            <div class="card print:hidden">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">E-Approval</h2>
                </div>
                <div class="card-body space-y-3">
                    @if($canSubmitProtocol)
                    <form method="POST" action="{{ route('stability-tests.submit-protocol', $st) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Ajukan Protokol Stabilitas
                        </button>
                    </form>
                    @endif

                    @if($canSubmitReport)
                    <form method="POST" action="{{ route('stability-tests.submit-report', $st) }}" class="space-y-2">
                        @csrf
                        <textarea name="stability_conclusion" rows="3" required placeholder="Kesimpulan hasil uji stabilitas (wajib)..."
                                  class="form-input text-sm">{{ old('stability_conclusion', $st->stability_conclusion) }}</textarea>
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Ajukan Laporan Hasil Stabilitas
                        </button>
                    </form>
                    @endif

                    @if($isOmTurn)
                    <form method="POST" action="{{ route('stability-tests.approve-protocol', $st) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui Protokol (Tahap OM)
                        </button>
                    </form>
                    @endif

                    @if($isGmTurn)
                    <form method="POST" action="{{ route('stability-tests.approve-report', $st) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui Laporan (Tahap GM)
                        </button>
                    </form>
                    @endif

                    @if($isOmTurn || $isGmTurn)
                    <details class="group">
                        <summary class="w-full btn-outline btn-sm justify-center list-none cursor-pointer select-none">
                            Tolak
                        </summary>
                        <form method="POST" action="{{ route('stability-tests.reject', $st) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="rejection_notes" rows="2" required placeholder="Alasan penolakan..."
                                      class="form-input text-sm"></textarea>
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition">
                                Tolak {{ $isOmTurn ? 'Protokol' : 'Laporan' }}
                            </button>
                        </form>
                    </details>
                    @endif
                </div>
            </div>
            @endif

            {{-- Catatan Penolakan --}}
            @if($st->approval_status === 'Rejected' && $st->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $st->rejection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Ringkasan --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Ringkasan Uji</h2>
                </div>
                <div class="card-body space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Total Jadwal</span>
                        <span class="font-semibold text-ink">{{ $st->schedules->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Jadwal Selesai</span>
                        <span class="font-semibold text-ink">{{ $st->schedules->where('status', 'Completed')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Jadwal OOS</span>
                        <span class="font-semibold text-ink {{ $st->schedules->where('status', 'OOS')->count() ? 'text-red-600' : '' }}">{{ $st->schedules->where('status', 'OOS')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Issue / OOS</span>
                        <span class="font-semibold text-ink">{{ $st->issues->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Lampiran</span>
                        <span class="font-semibold text-ink">{{ $st->attachments->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>