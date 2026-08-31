@props(['node', 'depth' => 0])

<div class="ml-{{ $depth > 0 ? ($depth * 4) : 0 }}">
    <a href="{{ route('commercial-productions.index', ['folder' => $node->id]) }}"
       class="flex items-center gap-2 py-1.5 px-2 rounded-lg text-sm hover:bg-gray-100 transition {{ request('folder') == $node->id ? 'bg-primary/10 text-primary font-medium' : 'text-gray-700' }}">
        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
        </svg>
        <span class="truncate">{{ $node->name }}</span>
    </a>
    @if($node->children->count())
        @foreach($node->children as $child)
            <x-commercial-productions.partials.tree-node :node="$child" :depth="$depth + 1"/>
        @endforeach
    @endif
</div>
