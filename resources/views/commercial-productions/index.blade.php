<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('timeline.index') }}" class="text-gray-400 hover:text-ink">Dashboard</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-semibold text-ink">Commercial Production</span>
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
                <form x-show="open" x-transition method="GET" action="{{ route('commercial-productions.index') }}" class="flex items-center gap-2 w-full">
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
            <div class="flex items-center gap-3 ml-auto">
                <!-- Add File -->
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 px-10 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm transition" style="background-color: #2F7D46;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add File
                </button>

                <!-- More -->
                <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center hover:bg-gray-50 transition">⋯</button>
                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-10">
                    <button onclick="document.getElementById('createFolderModal').classList.remove('hidden'); open = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create folder
                    </button>
                    <button onclick="location.reload()" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </button>
                </div>
            </div>
            </div>
        </div>

        <!-- Page Title -->
        <div class="mb-2">
            <h1 class="text-[32px] font-bold" style="color: #1F2A22;">Commercial Production</h1>
            @if($currentFolder)
                <nav class="flex items-center gap-1.5 text-sm mt-2 flex-wrap">
                    <a href="{{ route('commercial-productions.index') }}" class="text-gray-400 hover:text-ink">Commercial Production</a>
                    @foreach($breadcrumbs as $crumb)
                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <a href="{{ route('commercial-productions.index', ['folder' => $crumb->id]) }}" class="{{ $loop->last ? 'font-semibold text-ink' : 'text-gray-500 hover:text-ink' }}">{{ $crumb->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-6 border-b border-gray-100 mb-6">
            <a href="{{ route('commercial-productions.index', array_merge(request()->only('folder','search'), ['tab' => 'files'])) }}" class="pb-3 pt-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'files' ? 'border-ink text-ink' : 'border-transparent text-gray-400 hover:text-ink' }}">Files</a>
            <a href="{{ route('commercial-productions.index', array_merge(request()->only('folder','search'), ['tab' => 'activities'])) }}" class="pb-3 pt-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'activities' ? 'border-ink text-ink' : 'border-transparent text-gray-400 hover:text-ink' }}">Activities</a>
            <a href="{{ route('commercial-productions.index', array_merge(request()->only('folder','search'), ['tab' => 'overviews'])) }}" class="pb-3 pt-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'overviews' ? 'border-ink text-ink' : 'border-transparent text-gray-400 hover:text-ink' }}">Overviews</a>
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
                    <form method="GET" action="{{ route('commercial-productions.index') }}" class="flex items-center gap-2">
                        @if($currentFolder)<input type="hidden" name="folder" value="{{ $currentFolder->id }}">@endif
                        <select name="sort" onchange="this.form.submit()" class="text-sm border-0 bg-transparent text-gray-500 focus:ring-0 cursor-pointer">
                            <option value="">Sort by: Name</option>
                            <option value="newest" {{ request('sort')==='newest'?'selected':'' }}>Newest</option>
                            <option value="oldest" {{ request('sort')==='oldest'?'selected':'' }}>Oldest</option>
                            <option value="size" {{ request('sort')==='size'?'selected':'' }}>Size</option>
                        </select>
                    </form>
                    <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden">
                        <a href="{{ route('commercial-productions.index', array_merge(request()->all(), ['view' => 'grid'])) }}" class="p-2 {{ $viewMode==='grid' ? 'text-white' : 'bg-white text-gray-400 hover:text-ink' }} transition" style="{{ $viewMode==='grid' ? 'background-color: #1F2A22;' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </a>
                        <a href="{{ route('commercial-productions.index', array_merge(request()->all(), ['view' => 'list'])) }}" class="p-2 {{ $viewMode==='list' ? 'text-white' : 'bg-white text-gray-400 hover:text-ink' }} transition" style="{{ $viewMode==='list' ? 'background-color: #1F2A22;' : '' }}">
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
                </div>
            @else
                <!-- Folder Grid -->
                <div class="grid gap-4 mb-8" style="grid-template-columns: repeat(auto-fill, minmax(180px, 220px));">
                    @foreach($folders as $index => $folder)
                        <a href="{{ route('commercial-productions.index', ['folder' => $folder->id]) }}" class="group bg-white border border-gray-100 rounded-2xl px-3 py-3 hover:shadow-sm hover:border-gray-200 transition flex flex-col" style="min-height: 170px;">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color: #F8F8F6;">
                                <svg class="w-6 h-6" style="color: #1F2A22;" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                            </div>
                            <p class="text-[15px] font-medium truncate" style="color: #1F2A22;">{{ $folder->name }}</p>
                            <p class="text-sm text-gray-400 mt-auto pt-4 text-right">{{ $folder->documents()->count() }}</p>
                        </a>
                    @endforeach
                    <!-- Add New Folder Card -->
                    <button onclick="document.getElementById('createFolderModal').classList.remove('hidden')" class="border-2 border-dashed border-gray-200 rounded-2xl p-5 flex flex-col items-center justify-center hover:border-gray-300 hover:bg-gray-50/50 transition" style="min-height: 170px;">
                        <div class="w-10 h-10 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-500">Add New Folder</span>
                    </button>
                </div>

                <!-- Files -->
                <h2 class="text-lg font-semibold mb-3" style="color: #1F2A22;">Files</h2>
                @if($documents->isEmpty())
                    <div class="bg-white border border-gray-100 rounded-2xl py-12 text-center">
                        <p class="text-sm text-gray-400">No files in this folder</p>
                    </div>
                @else
                    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
                        @if($viewMode === 'grid')
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4">
                                @foreach($documents as $doc)
                                    <div class="group border border-gray-100 rounded-2xl p-3 hover:shadow-sm hover:border-gray-200 transition overflow-hidden min-w-0">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2
                                            @if($doc->extension==='pdf') bg-red-50 text-red-600
                                            @elseif(in_array($doc->extension,['doc','docx'])) bg-blue-50 text-blue-600
                                            @else bg-gray-100 text-gray-500 @endif">
                                            @if($doc->extension==='pdf')<span class="text-xs font-bold">PDF</span>@elseif(in_array($doc->extension,['doc','docx']))<span class="text-xs font-bold">DOC</span>@else{{ $doc->icon }}@endif
                                        </div>
                                        <a href="{{ route('commercial-productions.documents.show', $doc) }}" class="text-sm font-medium hover:text-primary line-clamp-2 leading-tight" style="color: #1F2A22;">{{ $doc->original_name }}</a>
                                        <p class="text-xs text-gray-400 mt-1">{{ $doc->formatted_size }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="divide-y divide-gray-50">
                                @foreach($documents as $doc)
                                    <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition group">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm flex-shrink-0">{{ $doc->icon }}</div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('commercial-productions.documents.show', $doc) }}" class="text-sm font-medium hover:text-primary truncate block" style="color: #1F2A22;">{{ $doc->original_name }}</a>
                                            <p class="text-xs text-gray-400">{{ $doc->formatted_size }} · v{{ $doc->version }}</p>
                                        </div>
                                        <span class="hidden sm:block text-xs text-gray-400">{{ $doc->updated_at->format('d M Y') }}</span>
                                        <a href="{{ route('commercial-productions.documents.download', $doc) }}" class="w-8 h-8 rounded-full bg-white border flex items-center justify-center hover:bg-gray-50">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="px-4 py-3 border-t bg-gray-50/30 flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ $documents->total() }} file</span>
                            {{ $documents->links() }}
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>

    <!-- Modals -->
    <div id="createFolderModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold" style="color: #1F2A22;">Create New Folder</h3>
            <form method="POST" action="{{ route('commercial-productions.folders.store') }}" class="mt-4">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                <label class="text-sm font-medium" style="color: #1F2A22;">Folder Name</label>
                <input type="text" name="name" required placeholder="Folder Name" class="form-input w-full mt-1">
                <label class="text-sm font-medium mt-3 block" style="color: #1F2A22;">Description</label>
                <textarea name="description" rows="2" placeholder="Description" class="form-input w-full mt-1"></textarea>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('createFolderModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-medium" style="background-color: #2F7D46;">Create Folder</button>
                </div>
            </form>
        </div>
    </div>

    <div id="uploadModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold" style="color: #1F2A22;">Upload Document</h3>
            <form method="POST" action="{{ route('commercial-productions.documents.store') }}" enctype="multipart/form-data" class="mt-4" x-data="uploadModal()">
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
