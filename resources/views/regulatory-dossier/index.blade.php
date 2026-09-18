<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('timeline.index') }}" class="text-gray-400 hover:text-ink">Dashboard</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-semibold text-ink">Regulatory Dossier</span>
        </div>
    </x-slot>

    @php($activeTab = request('tab', 'files'))
    @php($viewMode = request('view', 'grid'))

    <div class="max-w-[1200px] mx-auto">
        <!-- Top Action Bar: Search | Add File | More -->
        <div class="flex items-center gap-3 mb-6">
            <!-- Search -->
            <div class="flex-1 max-w-md" x-data="{ open: false }">
                <button x-show="!open" @click="open = true; $nextTick(() => $refs.searchInput.focus())" class="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
                <form x-show="open" x-transition method="GET" action="{{ route('regulatory-dossier.index') }}" class="flex items-center gap-2 w-full">
                    @if($currentFolder)<input type="hidden" name="folder" value="{{ $currentFolder->id }}">@endif
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <div class="relative flex-1">
                        <input x-ref="searchInput" type="text" name="search" value="{{ request('search') }}" placeholder="Search documents and folders..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:border-primary focus:ring-2 focus:ring-primary/10 transition">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-ink text-white text-sm font-medium hover:bg-ink/90 transition">Search</button>
                    <button type="button" @click="open = false" class="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center hover:bg-gray-50">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>

            <!-- Right side buttons -->
            <div class="flex items-center gap-2 ml-auto">
                <!-- Export Menu -->
                <div class="relative" x-data="{ openExport: false }">
                    <button @click="openExport = !openExport" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 text-sm font-medium shadow-sm transition" title="Export Semua Dokumen">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openExport" @click.outside="openExport = false" x-transition class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-xl py-1.5 z-50 text-xs">
                        <a href="{{ route('regulatory-dossier.export.zip', array_merge(request()->only('search','type'), $currentFolder ? ['folder' => $currentFolder->id] : [])) }}" class="px-4 py-2.5 text-gray-700 hover:bg-gray-50 flex items-start gap-2.5 transition">
                            <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-ink">Download Berkas (.zip)</p>
                                <p class="text-[11px] text-gray-400">Unduh seluruh berkas asli {{ $currentFolder ? 'dalam folder ini & sub-foldernya' : 'semua berkas dossier' }}</p>
                            </div>
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="{{ route('regulatory-dossier.export.excel', array_merge(request()->only('search','type'), $currentFolder ? ['folder' => $currentFolder->id] : [])) }}" class="px-4 py-2.5 text-gray-700 hover:bg-gray-50 flex items-start gap-2.5 transition">
                            <div class="w-7 h-7 rounded-lg bg-green-50 text-green-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-ink">Export Rekap Dokumen (.xlsx)</p>
                                <p class="text-[11px] text-gray-400">Daftar & metadata seluruh dokumen format Excel</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Add File -->
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 w-48 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm transition" style="background-color: #2F7D46;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add File
                </button>

                <!-- More -->
                <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center hover:bg-gray-50 transition" title="Menu Opsi">⋯</button>
                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-30 text-xs">
                    <button onclick="document.getElementById('createFolderModal').classList.remove('hidden'); open = false" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ $currentFolder ? 'Buat Sub-folder' : 'Buat Folder Baru' }}
                    </button>
                    @if($currentFolder)
                        @can('regulatory_dossier.edit')
                        <button onclick="openRenameFolderModal({{ $currentFolder->id }}, '{{ addslashes($currentFolder->name) }}', '{{ addslashes($currentFolder->description ?? '') }}'); open = false" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Ubah Nama Folder Ini
                        </button>
                        @endcan
                        @can('regulatory_dossier.delete')
                        <form method="POST" action="{{ route('regulatory-dossier.folders.destroy', $currentFolder) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus folder ini beserta seluruh isinya? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-red-600 hover:bg-red-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus Folder Ini
                            </button>
                        </form>
                        @endcan
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="{{ route('regulatory-dossier.export.zip', ['folder' => $currentFolder->id]) }}" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Folder Ini (.zip)
                        </a>
                        <a href="{{ route('regulatory-dossier.export.excel', ['folder' => $currentFolder->id]) }}" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export Rekap Excel (.xlsx)
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                    @endif
                    <button onclick="location.reload()" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </button>
                </div>
            </div>
            </div>
        </div>

        <!-- Page Title -->
        <div class="mb-2">
            <h1 class="text-[32px] font-bold" style="color: #1F2A22;">Regulatory Dossier</h1>
            @if($currentFolder)
                <nav class="flex items-center gap-1.5 text-sm mt-2 flex-wrap">
                    <a href="{{ route('regulatory-dossier.index') }}" class="text-gray-400 hover:text-ink">Regulatory Dossier</a>
                    @foreach($breadcrumbs as $crumb)
                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <a href="{{ route('regulatory-dossier.index', ['folder' => $crumb->id]) }}" class="{{ $loop->last ? 'font-semibold text-ink' : 'text-gray-500 hover:text-ink' }}">{{ $crumb->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-6 border-b border-gray-100 mb-6">
            <a href="{{ route('regulatory-dossier.index', array_merge(request()->only('folder','search'), ['tab' => 'files'])) }}" class="pb-3 pt-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'files' ? 'border-ink text-ink' : 'border-transparent text-gray-400 hover:text-ink' }}">Files</a>
            <a href="{{ route('regulatory-dossier.index', array_merge(request()->only('folder','search'), ['tab' => 'activities'])) }}" class="pb-3 pt-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'activities' ? 'border-ink text-ink' : 'border-transparent text-gray-400 hover:text-ink' }}">Activities</a>
            <a href="{{ route('regulatory-dossier.index', array_merge(request()->only('folder','search'), ['tab' => 'overviews'])) }}" class="pb-3 pt-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'overviews' ? 'border-ink text-ink' : 'border-transparent text-gray-400 hover:text-ink' }}">Overviews</a>
        </div>

        @if($activeTab === 'activities')
            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden max-w-3xl">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-ink">Activities</h2>
                    <p class="text-xs text-gray-400 mt-1">Histori aktivitas folder dan dokumen</p>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentAudits as $audit)
                        <div class="px-6 py-4 flex gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold text-white" style="background-color: #2F7D46;">{{ strtoupper(substr($audit->user?->name ?? 'S',0,1)) }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-ink">{{ $audit->action }} <span class="font-normal text-gray-500">— {{ $audit->description }}</span></p>
                                <p class="text-xs text-gray-400 mt-1">{{ $audit->user?->name ?? 'System' }} · {{ $audit->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-sm text-gray-400">Belum ada aktivitas</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @elseif($activeTab === 'overviews')
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 max-w-3xl">
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <p class="text-xs text-gray-400">Total Folders</p>
                    <p class="text-3xl font-bold mt-1" style="color: #1F2A22;">{{ $totalFolders }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <p class="text-xs text-gray-400">Total Documents</p>
                    <p class="text-3xl font-bold mt-1" style="color: #1F2A22;">{{ $totalDocuments }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <p class="text-xs text-gray-400">Total Storage</p>
                    <p class="text-2xl font-bold mt-1" style="color: #1F2A22;">
                        @if($totalSize >= 1048576) {{ number_format($totalSize/1048576,2) }} MB
                        @elseif($totalSize >= 1024) {{ number_format($totalSize/1024,2) }} KB
                        @else {{ $totalSize }} B @endif
                    </p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <p class="text-xs text-gray-400">Last Updated</p>
                    <p class="text-sm font-semibold mt-1" style="color: #1F2A22;">{{ $recentAudits->first()?->created_at?->format('d M Y') ?? '—' }}</p>
                </div>
            </div>
        @else
            <!-- Files Tab -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold" style="color: #1F2A22;">Folders</h2>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('regulatory-dossier.index') }}" class="flex items-center gap-2">
                        @if($currentFolder)<input type="hidden" name="folder" value="{{ $currentFolder->id }}">@endif
                        <select name="sort" onchange="this.form.submit()" class="text-sm border-0 bg-transparent text-gray-500 focus:ring-0 cursor-pointer">
                            <option value="">Sort by: Name</option>
                            <option value="newest" {{ request('sort')==='newest'?'selected':'' }}>Newest</option>
                            <option value="oldest" {{ request('sort')==='oldest'?'selected':'' }}>Oldest</option>
                            <option value="size" {{ request('sort')==='size'?'selected':'' }}>Size</option>
                        </select>
                    </form>
                    <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden">
                        <a href="{{ route('regulatory-dossier.index', array_merge(request()->all(), ['view' => 'grid'])) }}" class="p-2 {{ $viewMode==='grid' ? 'text-white' : 'bg-white text-gray-400 hover:text-ink' }} transition" style="{{ $viewMode==='grid' ? 'background-color: #1F2A22;' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </a>
                        <a href="{{ route('regulatory-dossier.index', array_merge(request()->all(), ['view' => 'list'])) }}" class="p-2 {{ $viewMode==='list' ? 'text-white' : 'bg-white text-gray-400 hover:text-ink' }} transition" style="{{ $viewMode==='list' ? 'background-color: #1F2A22;' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            @if($folders->isEmpty() && $documents->isEmpty() && !request('search'))
                <div class="bg-white border border-gray-100 rounded-2xl py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-medium" style="color: #1F2A22;">This folder is empty</p>
                    <p class="text-sm text-gray-400 mt-1">Create a folder or upload a document to get started.</p>
                    <div class="flex items-center justify-center gap-2 mt-4">
                        <button onclick="document.getElementById('createFolderModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm hover:bg-gray-50">+ New Folder</button>
                        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl text-white text-sm" style="background-color: #2F7D46;">+ Add File</button>
                    </div>
                            @else
                <!-- Folder Grid -->
                <div class="grid gap-4 mb-8" style="grid-template-columns: repeat(auto-fill, minmax(190px, 220px));">
                    @foreach($folders as $index => $folder)
                        <div x-data="{ open: false }"
                             class="group relative bg-white border border-gray-100 rounded-2xl p-4 hover:shadow-sm hover:border-gray-200 transition flex flex-col justify-between"
                             :class="{ 'z-30': open, 'z-0': !open }"
                             style="min-height: 170px;">
                            <div class="flex items-start justify-between">
                                <a href="{{ route('regulatory-dossier.index', ['folder' => $folder->id]) }}" class="w-10 h-10 rounded-xl flex items-center justify-center transition hover:scale-105" style="background-color: #F8F8F6;">
                                    <svg class="w-6 h-6" style="color: #1F2A22;" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                                </a>

                                <!-- Folder Dropdown Menu -->
                                <div class="relative">
                                    <button type="button" @click.stop="open = !open" class="w-7 h-7 rounded-lg text-gray-400 hover:text-ink hover:bg-gray-100 flex items-center justify-center transition" title="Menu Folder">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="4" r="2"/><circle cx="10" cy="10" r="2"/><circle cx="10" cy="16" r="2"/></svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-xl py-1 z-50 text-xs">
                                        <a href="{{ route('regulatory-dossier.index', ['folder' => $folder->id]) }}" class="px-3 py-2 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Buka Folder
                                        </a>
                                        <a href="{{ route('regulatory-dossier.export.zip', ['folder' => $folder->id]) }}" class="px-3 py-2 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download ZIP
                                        </a>
                                        @can('regulatory_dossier.edit')
                                        <button type="button" @click="open = false; openRenameFolderModal({{ $folder->id }}, '{{ addslashes($folder->name) }}', '{{ addslashes($folder->description ?? '') }}')" class="w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Ubah Nama
                                        </button>
                                        @endcan
                                        @can('regulatory_dossier.delete')
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <form method="POST" action="{{ route('regulatory-dossier.folders.destroy', $folder) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus folder \'{{ addslashes($folder->name) }}\' beserta seluruh sub-folder dan file di dalamnya? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus Folder
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('regulatory-dossier.index', ['folder' => $folder->id]) }}" class="mt-4 block flex-1 flex flex-col justify-end">
                                <p class="text-[15px] font-medium text-ink truncate group-hover:text-primary transition" title="{{ $folder->name }}">{{ $folder->name }}</p>
                                <div class="flex items-center justify-between text-xs text-gray-400 mt-2">
                                    <span>{{ $folder->children()->count() }} sub-folder</span>
                                    <span>{{ $folder->documentCountRecursive() }} file</span>
                                </div>
                            </a>
                        </div>
                    @endforeach

                    <!-- Add New Folder Card -->
                    <button onclick="document.getElementById('createFolderModal').classList.remove('hidden')" class="border-2 border-dashed border-gray-200 rounded-2xl p-5 flex flex-col items-center justify-center hover:border-gray-300 hover:bg-gray-50/50 transition" style="min-height: 170px;">
                        <div class="w-10 h-10 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-500">{{ $currentFolder ? '+ Add Sub-folder' : '+ Add New Folder' }}</span>
                    </button>
                </div>

                <!-- Files -->
                <h2 class="text-lg font-semibold mb-3" style="color: #1F2A22;">Files</h2>
                @if($documents->isEmpty())
                    <div class="bg-white border border-gray-100 rounded-2xl py-12 text-center">
                        <p class="text-sm text-gray-400">No files in this folder</p>
                    </div>
                @else
                    <div class="bg-white border border-gray-100 rounded-2xl">
                        @if($viewMode === 'grid')
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4">
                                @foreach($documents as $doc)
                                    <div x-data="{ open: false }"
                                         class="group relative border border-gray-100 rounded-2xl p-3 hover:shadow-sm hover:border-gray-200 transition min-w-0 flex flex-col justify-between"
                                         :class="{ 'z-30': open, 'z-0': !open }"
                                         style="min-height: 130px;">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                                @if($doc->extension==='pdf') bg-red-50 text-red-600
                                                @elseif(in_array($doc->extension,['doc','docx'])) bg-blue-50 text-blue-600
                                                @else bg-gray-100 text-gray-500 @endif">
                                                @if($doc->extension==='pdf')<span class="text-xs font-bold">PDF</span>@elseif(in_array($doc->extension,['doc','docx']))<span class="text-xs font-bold">DOC</span>@else{{ $doc->icon }}@endif
                                            </div>

                                            <!-- File Dropdown Menu -->
                                            <div class="relative">
                                                <button type="button" @click.stop="open = !open" class="w-7 h-7 rounded-lg text-gray-400 hover:text-ink hover:bg-gray-100 flex items-center justify-center transition" title="Opsi File">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="4" r="2"/><circle cx="10" cy="10" r="2"/><circle cx="10" cy="16" r="2"/></svg>
                                                </button>
                                                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-xl py-1 z-50 text-xs">
                                                    <a href="{{ route('regulatory-dossier.documents.show', $doc) }}" class="px-3 py-2 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        Detail File
                                                    </a>
                                                    <a href="{{ route('regulatory-dossier.documents.download', $doc) }}" class="px-3 py-2 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                        Download
                                                    </a>
                                                    @can('regulatory_dossier.edit')
                                                    <button type="button" @click="open = false; openMoveModal({{ $doc->id }}, '{{ addslashes($doc->original_name) }}', {{ $doc->folder_id ?? 'null' }})" class="w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                        Pindah File
                                                    </button>
                                                    @endcan
                                                    @can('regulatory_dossier.delete')
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <form method="POST" action="{{ route('regulatory-dossier.documents.destroy', $doc) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file \'{{ addslashes($doc->original_name) }}\'?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            Hapus File
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ route('regulatory-dossier.documents.show', $doc) }}" class="text-sm font-medium hover:text-primary line-clamp-2 leading-tight block" style="color: #1F2A22;" title="{{ $doc->original_name }}">{{ $doc->original_name }}</a>
                                        <p class="text-xs text-gray-400 mt-2">{{ $doc->formatted_size }} · v{{ $doc->version }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="divide-y divide-gray-50">
                                @foreach($documents as $doc)
                                    <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition group">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm flex-shrink-0">{{ $doc->icon }}</div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('regulatory-dossier.documents.show', $doc) }}" class="text-sm font-medium hover:text-primary truncate block" style="color: #1F2A22;" title="{{ $doc->original_name }}">{{ $doc->original_name }}</a>
                                            <p class="text-xs text-gray-400">{{ $doc->formatted_size }} · v{{ $doc->version }}</p>
                                        </div>
                                        <span class="hidden sm:block text-xs text-gray-400">{{ $doc->updated_at->format('d M Y') }}</span>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('regulatory-dossier.documents.download', $doc) }}" class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-500 hover:text-ink transition" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            @can('regulatory_dossier.edit')
                                            <button type="button" @click="openMoveModal({{ $doc->id }}, '{{ addslashes($doc->original_name) }}', {{ $doc->folder_id ?? 'null' }})" class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-500 hover:text-ink transition" title="Pindah File">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                            @endcan
                                            @can('regulatory_dossier.delete')
                                            <form method="POST" action="{{ route('regulatory-dossier.documents.destroy', $doc) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file \'{{ addslashes($doc->original_name) }}\'?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-red-50 text-gray-500 hover:text-red-600 transition" title="Hapus File">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="px-4 py-3 border-t bg-gray-50/30 flex items-center justify-between rounded-b-2xl">
                            <span class="text-xs text-gray-400">{{ $documents->total() }} file</span>
                            {{ $documents->links() }}
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>

    <!-- Modals -->
    <!-- Create Folder Modal -->
    <div id="createFolderModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold text-base" style="color: #1F2A22;">{{ $currentFolder ? 'Buat Sub-folder' : 'Buat Folder Baru' }}</h3>
            @if($currentFolder)
                <p class="text-xs text-gray-500 mt-1">Akan dibuat di dalam: <span class="font-semibold text-ink">📁 {{ $currentFolder->name }}</span></p>
            @else
                <p class="text-xs text-gray-500 mt-1">Akan dibuat di: <span class="font-semibold text-ink">📁 Root (Folder Utama)</span></p>
            @endif
            <form method="POST" action="{{ route('regulatory-dossier.folders.store') }}" class="mt-4">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                <label class="text-xs font-semibold text-gray-700" style="color: #1F2A22;">Nama Folder</label>
                <input type="text" name="name" required placeholder="Nama folder..." class="form-input w-full mt-1 rounded-xl border-gray-200 text-sm">
                <label class="text-xs font-semibold text-gray-700 mt-3 block" style="color: #1F2A22;">Deskripsi (Opsional)</label>
                <textarea name="description" rows="2" placeholder="Deskripsi singkat folder..." class="form-input w-full mt-1 rounded-xl border-gray-200 text-sm"></textarea>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('createFolderModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-medium" style="background-color: #2F7D46;">Simpan Folder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rename Folder Modal -->
    <div id="renameFolderModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold text-base" style="color: #1F2A22;">Ubah Nama Folder</h3>
            <form id="renameFolderForm" method="POST" action="" class="mt-4">
                @csrf
                @method('PUT')
                <label class="text-xs font-semibold text-gray-700">Nama Folder</label>
                <input type="text" id="renameFolderName" name="name" required class="form-input w-full mt-1 rounded-xl border-gray-200 text-sm">
                <label class="text-xs font-semibold text-gray-700 mt-3 block">Deskripsi (Opsional)</label>
                <textarea id="renameFolderDesc" name="description" rows="2" class="form-input w-full mt-1 rounded-xl border-gray-200 text-sm"></textarea>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('renameFolderModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-medium" style="background-color: #2F7D46;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move File Modal -->
    <div id="moveFileModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold text-base" style="color: #1F2A22;">Pindahkan File</h3>
            <p class="text-xs text-gray-500 mt-1">Pindahkan file <span id="moveFileName" class="font-semibold text-ink"></span> ke folder tujuan.</p>
            <form id="moveFileForm" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pilih Folder Tujuan</label>
                    <select id="moveFileFolderSelect" name="folder_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-primary focus:ring-primary">
                        <option value="">📁 Root (Folder Utama)</option>
                        @foreach($allFolders as $folder)
                            <option value="{{ $folder->id }}">📁 {{ $folder->name }} {{ $folder->parent ? '('.$folder->parent->name.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('moveFileModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-white text-sm font-medium hover:bg-primary/90 transition shadow-sm">Pindahkan File</button>
                </div>
            </form>
        </div>
    </div>

    <div id="uploadModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold" style="color: #1F2A22;">Upload Document</h3>
            <form method="POST" action="{{ route('regulatory-dossier.documents.store') }}" enctype="multipart/form-data" class="mt-4" x-data="uploadModal()">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">

                <!-- Drop Zone -->
                <div class="border-2 border-dashed rounded-xl transition-all duration-200"
                     :class="{ 'border-green-400 bg-green-50': isDragging, 'border-gray-200': !isDragging }"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)">
                    <div class="p-8 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2" :class="isDragging ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-medium" style="color: #1F2A22;" x-text="isDragging ? 'Lepaskan file di sini' : 'Drag & Drop file here'"></p>
                        <p class="text-xs text-gray-400 my-2">or</p>
                        <label class="inline-block px-4 py-2 rounded-xl border border-gray-200 text-sm cursor-pointer hover:bg-gray-50">
                            Browse Files
                            <input type="file" name="files[]" multiple @change="handleFiles($event.target.files)" class="hidden">
                        </label>
                        <p class="text-xs text-gray-400 mt-2">Max 100MB per file. PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, JPG, PNG, GIF, WEBP, ZIP, RAR, TXT.</p>
                    </div>
                </div>

                <!-- Selected Files List -->
                <div x-show="files.length > 0" x-transition class="mt-4 space-y-2 border-t pt-4">
                    <h4 class="text-xs font-medium text-gray-400 uppercase tracking-wide">File yang siap diunggah (<span x-text="files.length"></span>)</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2">
                        <template x-for="(file, index) in files" :key="file.name + file.size + index">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                     :class="getFileIconClass(file.type)">
                                    <span x-text="getFileIcon(file.type)" class="text-xs font-bold"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" style="color: #1F2A22;" x-text="file.name"></p>
                                    <p class="text-xs text-gray-400" x-text="formatSize(file.size)"></p>
                                </div>
                                <button type="button" @click="removeFile(index)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition flex items-center justify-center" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-xs text-gray-500" x-text="'Total: ' + formatSize(totalSize)"></span>
                        <span class="text-xs text-gray-400" x-text="files.length + ' file'"></span>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="files.length === 0" class="text-center py-4 text-gray-400 text-sm">
                    Belum ada file dipilih
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="reset()" class="px-4 py-2 rounded-xl border text-sm hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="files.length === 0 || uploading" class="px-6 py-2 rounded-xl text-white text-sm font-medium transition" style="background-color: #2F7D46;"
                            x-text="uploading ? 'Mengunggah...' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

    <script>
        function showToast(message, type = 'error') {
            const container = document.getElementById('toastContainer');
            const colors = type === 'error' ? 'bg-red-500' : 'bg-green-500';
            const icons = type === 'error'
                ? '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
                : '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            const el = document.createElement('div');
            el.className = `flex items-center gap-2 px-4 py-3 rounded-xl text-white text-sm shadow-lg transition-opacity duration-300 ${colors}`;
            el.innerHTML = icons + `<span>${message}</span>`;
            container.appendChild(el);
            setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3500);
        }

        document.querySelectorAll('[id$="Modal"]').forEach(m => {
            m.addEventListener('click', e => { if(e.target === m) m.classList.add('hidden'); });
        });

        function openRenameFolderModal(folderId, name, desc) {
            const form = document.getElementById('renameFolderForm');
            form.action = '{{ url("regulatory-dossier/folders") }}/' + folderId;
            document.getElementById('renameFolderName').value = name;
            document.getElementById('renameFolderDesc').value = desc || '';
            document.getElementById('renameFolderModal').classList.remove('hidden');
        }

        function openMoveModal(docId, docName, currentFolderId) {
            const form = document.getElementById('moveFileForm');
            form.action = '{{ url("regulatory-dossier/documents") }}/' + docId + '/move';
            document.getElementById('moveFileName').textContent = `"${docName}"`;
            const select = document.getElementById('moveFileFolderSelect');
            if (select) {
                select.value = (currentFolderId !== null && currentFolderId !== undefined) ? String(currentFolderId) : '';
            }
            document.getElementById('moveFileModal').classList.remove('hidden');
        }

        window.openRenameFolderModal = openRenameFolderModal;
        window.openMoveModal = openMoveModal;

        function uploadModal() {
            return {
                files: [],
                isDragging: false,
                uploading: false,

                handleFiles(fileList) {
                    this.addFiles(fileList);
                },

                handleDrop(event) {
                    this.isDragging = false;
                    this.addFiles(event.dataTransfer.files);
                },

                addFiles(fileList) {
                    const allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar', 'txt'];
                    const maxSize = 100 * 1024 * 1024; // 100MB

                    for (let file of fileList) {
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (!allowedTypes.includes(ext)) {
                            showToast(`File "${file.name}" tidak diizinkan. Tipe: .${ext} tidak didukung.`);
                            continue;
                        }
                        if (file.size > maxSize) {
                            showToast(`File "${file.name}" melebihi batas 100MB.`);
                            continue;
                        }
                        // Check duplicate
                        if (!this.files.some(f => f.name === file.name && f.size === file.size)) {
                            this.files.push(file);
                        }
                    }
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },

                reset() {
                    this.files = [];
                    this.isDragging = false;
                    document.getElementById('uploadModal').classList.add('hidden');
                },

                get totalSize() {
                    return this.files.reduce((sum, f) => sum + f.size, 0);
                },

                formatSize(bytes) {
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
                    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
                    return bytes + ' B';
                },

                getFileIcon(type) {
                    const ext = type.split('/').pop().toLowerCase();
                    if (ext === 'pdf') return 'PDF';
                    if (['doc', 'docx'].includes(ext)) return 'DOC';
                    if (['xls', 'xlsx'].includes(ext)) return 'XLS';
                    if (['ppt', 'pptx'].includes(ext)) return 'PPT';
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return 'IMG';
                    if (['zip', 'rar'].includes(ext)) return 'ZIP';
                    return 'FILE';
                },

                getFileIconClass(type) {
                    const ext = type.split('/').pop().toLowerCase();
                    if (ext === 'pdf') return 'bg-red-50 text-red-600';
                    if (['doc', 'docx'].includes(ext)) return 'bg-blue-50 text-blue-600';
                    if (['xls', 'xlsx'].includes(ext)) return 'bg-green-50 text-green-600';
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return 'bg-purple-50 text-purple-600';
                    if (['zip', 'rar'].includes(ext)) return 'bg-orange-50 text-orange-600';
                    return 'bg-gray-100 text-gray-500';
                }
            };
        }
    </script>
</x-app-layout>
