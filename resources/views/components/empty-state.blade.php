@props([
    'title'       => 'Belum Ada Data',
    'description' => 'Belum ada data yang tersedia saat ini.',
    'icon'        => 'leaf',
])

@php
$iconSvg = match($icon) {
    'formula' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'trial'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
    'approval'=> '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
    'search'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
    default   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
};
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-16 px-4 text-center']) }}>

    {{-- Icon circle --}}
    <div class="w-20 h-20 rounded-2xl bg-primary/6 flex items-center justify-center mb-5 rotate-3">
        <svg class="w-10 h-10 text-primary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $iconSvg !!}
        </svg>
    </div>

    <h3 class="text-base font-heading font-semibold text-ink mb-2">
        {{ $title }}
    </h3>
    <p class="text-sm text-gray-400 max-w-xs leading-relaxed">
        {{ $description }}
    </p>

    @isset($action)
    <div class="mt-6">
        {{ $action }}
    </div>
    @endisset
</div>
