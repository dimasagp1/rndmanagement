@props([
    'steps'      => [],
    'orientation'=> 'vertical',   {{-- 'vertical' | 'horizontal' --}}
])

{{--
    Usage:
    <x-approval-timeline :steps="[
        ['label' => 'Staff R&D', 'sublabel' => 'Dibuat', 'status' => 'completed', 'user' => 'Ahmad', 'date' => '2024-01-15 10:30'],
        ['label' => 'Operational Manager', 'sublabel' => 'Approval Tahap 1', 'status' => 'current', 'user' => null, 'date' => null],
        ['label' => 'General Manager', 'sublabel' => 'Approval Tahap 2', 'status' => 'pending', 'user' => null, 'date' => null],
    ]" />
--}}

<div {{ $attributes->merge(['class' => 'space-y-0']) }}>
    @foreach($steps as $index => $step)
    @php
        $isCompleted = $step['status'] === 'completed';
        $isCurrent   = $step['status'] === 'current';
        $isLast      = $loop->last;
    @endphp
    <div class="flex items-start gap-4">

        {{-- Step Column (icon + line) --}}
        <div class="flex flex-col items-center flex-shrink-0" style="width: 2rem;">
            {{-- Icon --}}
            @if($isCompleted)
            <div class="stepper-icon completed shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            @elseif($isCurrent)
            <div class="stepper-icon current">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse-dot"></span>
            </div>
            @else
            <div class="stepper-icon pending">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
            </div>
            @endif

            {{-- Connector Line --}}
            @unless($isLast)
            <div class="w-0.5 flex-1 min-h-[2.5rem] mt-1
                {{ $isCompleted ? 'bg-primary/40' : 'bg-gray-200' }}">
            </div>
            @endunless
        </div>

        {{-- Content --}}
        <div class="flex-1 pb-6">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold {{ $isCompleted ? 'text-ink' : ($isCurrent ? 'text-accent' : 'text-gray-400') }}">
                        {{ $step['label'] }}
                    </p>
                    @if(isset($step['sublabel']))
                    <p class="text-xs {{ $isCompleted ? 'text-gray-500' : ($isCurrent ? 'text-accent/70' : 'text-gray-300') }} mt-0.5">
                        {{ $step['sublabel'] }}
                    </p>
                    @endif
                </div>

                {{-- Current badge --}}
                @if($isCurrent)
                <span class="text-[10px] font-semibold bg-accent/10 text-accent px-2 py-0.5 rounded-full ring-1 ring-accent/20 whitespace-nowrap flex-shrink-0">
                    Menunggu
                </span>
                @endif
            </div>

            {{-- User & date (only if completed) --}}
            @if($isCompleted && (isset($step['user']) || isset($step['date'])))
            <div class="mt-1 flex items-center gap-2 flex-wrap">
                @if(isset($step['user']) && $step['user'])
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $step['user'] }}
                </div>
                @endif
                @if(isset($step['date']) && $step['date'])
                <div class="flex items-center gap-1 text-xs text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $step['date'] }}
                </div>
                @endif

                @if(isset($step['notes']) && $step['notes'])
                <div class="w-full mt-1 text-xs text-gray-500 bg-surface px-2 py-1 rounded-md italic">
                    "{{ $step['notes'] }}"
                </div>
                @endif
            </div>
            @endif

            {{-- Pending message --}}
            @if(!$isCompleted && !$isCurrent)
            <p class="text-xs text-gray-300 mt-0.5">Menunggu tahap sebelumnya...</p>
            @endif
        </div>
    </div>
    @endforeach
</div>
