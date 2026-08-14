@props(['status' => 'Draft', 'size' => 'default'])

@php
    $map = [
        'draft'           => ['label' => 'Draf',              'cls' => 'bg-gray-100 text-gray-700 ring-gray-200'],
        'draf'            => ['label' => 'Draf',              'cls' => 'bg-gray-100 text-gray-700 ring-gray-200'],
        'pending tahap 1' => ['label' => 'Pending Tahap 1',   'cls' => 'bg-amber-100 text-amber-800 ring-amber-200'],
        'pending tahap 2' => ['label' => 'Pending Tahap 2',   'cls' => 'bg-orange-100 text-orange-800 ring-orange-200'],
        'pending review'  => ['label' => 'Pending Review',    'cls' => 'bg-amber-100 text-amber-800 ring-amber-200'],
        'pending'         => ['label' => 'Pending',           'cls' => 'bg-amber-100 text-amber-800 ring-amber-200'],
        'submitted'       => ['label' => 'Submitted',         'cls' => 'bg-sky-100 text-sky-800 ring-sky-200'],
        'approved'        => ['label' => 'Approved',          'cls' => 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        'completed'       => ['label' => 'Completed',         'cls' => 'bg-sky-100 text-sky-800 ring-sky-200'],
        'approval by om'  => ['label' => 'Approval by OM',    'cls' => 'bg-blue-100 text-blue-800 ring-blue-200'],
        'completed by gm' => ['label' => 'Completed by GM',   'cls' => 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        'rejected'        => ['label' => 'Ditolak',           'cls' => 'bg-red-100 text-red-700 ring-red-200'],
        'reformulasi'     => ['label' => 'Reformulasi',       'cls' => 'bg-orange-100 text-orange-700 ring-orange-200'],
        'reformulate'     => ['label' => 'Reformulasi',       'cls' => 'bg-orange-100 text-orange-700 ring-orange-200'],
        'lulus'           => ['label' => 'Lulus',             'cls' => 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        'pass'            => ['label' => 'Lulus',             'cls' => 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        'pra-trial'       => ['label' => 'Pra-Trial',         'cls' => 'bg-blue-100 text-blue-800 ring-blue-200'],
        'optimalisasi'    => ['label' => 'Optimalisasi',      'cls' => 'bg-violet-100 text-violet-800 ring-violet-200'],
        'final'           => ['label' => 'Final',             'cls' => 'bg-teal-100 text-teal-800 ring-teal-200'],
        'on track'        => ['label' => 'On Track',          'cls' => 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        'in progress'     => ['label' => 'In Progress',       'cls' => 'bg-blue-100 text-blue-800 ring-blue-200'],
        'on hold'         => ['label' => 'On Hold',           'cls' => 'bg-amber-100 text-amber-800 ring-amber-200'],
        'delayed'         => ['label' => 'Delayed',           'cls' => 'bg-red-100 text-red-700 ring-red-200'],
    ];

    $key    = strtolower(trim($status));
    $config = $map[$key] ?? ['label' => $status, 'cls' => 'bg-gray-100 text-gray-600 ring-gray-200'];

    $sizeClass = match($size) {
        'sm'  => 'px-2 py-0.5 text-[10px]',
        'lg'  => 'px-3.5 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 $sizeClass font-medium rounded-full ring-1 {$config['cls']} whitespace-nowrap"]) }}>
    {{-- Dot indicator --}}
    <span class="w-1.5 h-1.5 rounded-full
        @if(in_array($key, ['approved', 'lulus', 'pass', 'completed by gm', 'on track'])) bg-emerald-500
        @elseif(in_array($key, ['rejected', 'delayed'])) bg-red-500
        @elseif(in_array($key, ['pending tahap 1', 'pending tahap 2', 'pending review', 'pending', 'approval by om', 'on hold'])) bg-amber-500
        @elseif(in_array($key, ['reformulasi', 'reformulate'])) bg-orange-500
        @elseif(in_array($key, ['draft', 'draf'])) bg-gray-400
        @elseif(in_array($key, ['submitted'])) bg-sky-500
        @else bg-current opacity-50
        @endif
    "></span>
    {{ $config['label'] }}
</span>
