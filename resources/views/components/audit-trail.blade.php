@props(['activities'])

<div class="space-y-4">
    @if($activities->isEmpty())
    <p class="text-xs text-gray-400 italic">Belum ada riwayat aktivitas yang tercatat.</p>
    @else
    <div class="relative border-l border-gray-200 ml-2.5 space-y-4">
        @foreach($activities as $activity)
        <div class="relative pl-6">
            {{-- Dot indicator --}}
            <span class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full border-2 border-white
                         {{ $activity->event === 'created' ? 'bg-emerald-500' : ($activity->event === 'deleted' ? 'bg-red-500' : 'bg-blue-500') }}">
            </span>
            <div class="text-xs font-semibold text-ink uppercase tracking-wider">
                {{ $activity->description }}
            </div>
            <div class="text-[11px] text-gray-400 mt-0.5">
                oleh <span class="font-medium text-gray-500">{{ $activity->causer?->name ?? 'Sistem' }}</span>
                · {{ $activity->created_at->diffForHumans() }}
            </div>

            {{-- Detail changes (if any) --}}
            @if(isset($activity->properties['attributes']))
                @php
                    $changes = array_intersect_key(
                        $activity->properties['attributes'],
                        array_flip(['approval_status', 'decision', 'development_stage', 'version'])
                    );
                @endphp
                @if(!empty($changes))
                <div class="mt-1.5 p-2 bg-surface rounded border border-gray-150 text-[10px] font-mono text-gray-500 space-y-0.5">
                    @foreach($changes as $key => $val)
                    <div>
                        <span class="font-semibold">{{ str_replace('_', ' ', $key) }}:</span>
                        <span class="text-primary">{{ is_bool($val) ? ($val ? 'Yes' : 'No') : $val }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
