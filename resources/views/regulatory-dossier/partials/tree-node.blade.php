@php
    $isActive = $currentId == $folder->id;
    $isAncestor = $currentId && in_array($currentId, $folder->allDescendantIds());
    $hasChildren = $folder->children_tree && $folder->children_tree->count() > 0;
    $isOpen = $isActive || $isAncestor || $depth < 1;
@endphp
<li x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }">
    <div class="group flex items-center gap-1">
        @if($hasChildren)
            <button @click="open = !open" class="w-5 h-5 rounded-md flex items-center justify-center flex-shrink-0 {{ $isActive ? 'text-white/60 hover:text-white hover:bg-white/10' : 'text-gray-300 hover:text-gray-600 hover:bg-gray-50' }} transition">
                <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        @else
            <span class="w-5 flex-shrink-0"></span>
        @endif
        <a href="{{ route('regulatory-dossier.index', ['folder' => $folder->id]) }}"
           class="flex-1 flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-sm transition min-w-0 {{ $isActive ? 'bg-ink text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-ink' }}">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition {{ $isActive ? 'bg-white/15 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-white group-hover:text-primary group-[.bg-ink]:bg-white/15' }}">
                @if($hasChildren)
                    <svg x-show="!open" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <svg x-show="open" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19a2 2 0 002 2h10a2 2 0 002-2v-3.5a1 1 0 00-1-1H9a1 1 0 00-1 1V19z"/></svg>
                @else
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/></svg>
                @endif
            </span>
            <span class="truncate flex-1 font-medium text-[13px] leading-tight">{{ $folder->name }}</span>
            @if($hasChildren)
                <span class="text-xs px-1.5 py-0.5 rounded-full font-medium flex-shrink-0 {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $folder->children_tree->count() }}</span>
            @endif
        </a>
    </div>
    @if($hasChildren)
        <ul x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="ml-[18px] mt-1.5 space-y-0.5 border-l border-gray-100 pl-3">
            @foreach($folder->children_tree as $child)
                @include('regulatory-dossier.partials.tree-node', ['folder' => $child, 'currentId' => $currentId, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
