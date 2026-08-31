<x-app-layout>

    {{-- =========================================================
        HEADER / BREADCRUMB
    ========================================================== --}}
    <x-slot name="header">
        <div class="flex items-center gap-1.5 text-sm text-gray-500 flex-wrap">
            <a href="{{ route('commercial-productions.index') }}"
               class="hover:text-primary transition">
                Commercial Production
            </a>

            @foreach($breadcrumbs as $crumb)
                <svg class="w-3 h-3 text-gray-300 flex-shrink-0"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>

                <a href="{{ route('commercial-productions.index', ['folder' => $crumb->id]) }}"
                   class="hover:text-primary transition truncate max-w-[180px]">
                    {{ $crumb->name }}
                </a>
            @endforeach

            <svg class="w-3 h-3 text-gray-300 flex-shrink-0"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"/>
            </svg>

            <span class="text-ink font-medium truncate max-w-[220px]">
                {{ $document->original_name }}
            </span>
        </div>
    </x-slot>


    {{-- =========================================================
        DOCUMENT HEADER
    ========================================================== --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-5 mb-5">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

            {{-- Document Identity --}}
            <div class="flex items-center gap-3 min-w-0">

                {{-- File Icon --}}
                <div class="w-12 h-12 rounded-xl flex items-center justify-center
                            text-xl flex-shrink-0
                    @if($document->extension === 'pdf')
                        bg-red-50
                    @elseif(in_array($document->extension, ['doc', 'docx']))
                        bg-blue-50
                    @elseif(in_array($document->extension, ['xls', 'xlsx']))
                        bg-green-50
                    @else
                        bg-gray-50
                    @endif">

                    {{ $document->icon }}

                </div>


                {{-- Document Information --}}
                <div class="min-w-0">

                    <h1 class="text-lg sm:text-xl font-bold text-ink leading-tight truncate">
                        {{ $document->original_name }}
                    </h1>

                    <div class="flex items-center gap-2 mt-1 flex-wrap">

                        {{-- Extension --}}
                        <span class="inline-flex items-center px-2 py-0.5
                                     rounded-full text-xs font-medium
                            @if($document->extension === 'pdf')
                                bg-red-50 text-red-600
                            @elseif(in_array($document->extension, ['doc', 'docx']))
                                bg-blue-50 text-blue-600
                            @else
                                bg-gray-100 text-gray-500
                            @endif">

                            {{ strtoupper($document->extension ?? '-') }}

                        </span>

                        {{-- Size + Version --}}
                        <span class="text-xs text-gray-400">
                            {{ $document->formatted_size }}
                            · v{{ $document->version }}
                        </span>

                        <span class="text-xs text-gray-300">
                            ·
                        </span>

                        {{-- Uploader --}}
                        <span class="text-xs text-gray-500">
                            {{ $document->uploader?->name ?? '—' }}
                        </span>

                    </div>
                </div>

            </div>


            {{-- Back Button --}}
            <a href="{{ route('commercial-productions.index', ['folder' => $document->folder_id]) }}"
               class="inline-flex items-center justify-center gap-1.5
                      px-3.5 py-2 rounded-xl
                      border border-gray-200 bg-white
                      text-sm text-gray-600
                      hover:bg-gray-50 transition
                      flex-shrink-0">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>

                </svg>

                Kembali

            </a>

        </div>
    </div>


    {{-- =========================================================
        MAIN CONTENT
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">


        {{-- =====================================================
            LEFT COLUMN
        ====================================================== --}}
        <div class="lg:col-span-2 space-y-5">


            {{-- =================================================
                DOCUMENT DETAIL
            ================================================== --}}
            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">


                {{-- Card Header --}}
                <div class="px-4 sm:px-5 py-3
                            border-b border-gray-100
                            flex items-center justify-between gap-3">

                    <h2 class="text-sm font-semibold text-ink">
                        Detail Dokumen
                    </h2>

                    <span class="text-xs px-2.5 py-1
                                 rounded-full
                                 bg-primary/10
                                 text-primary
                                 font-medium
                                 flex-shrink-0">

                        v{{ $document->version }}

                        {{ $document->version == 1
                            ? ' · Original'
                            : ' · Current'
                        }}

                    </span>

                </div>


                {{-- Card Body --}}
                <div class="p-4 sm:p-5">


                    {{-- Document Metadata --}}
                    <dl class="grid grid-cols-1 sm:grid-cols-2
                               gap-x-8 gap-y-4 text-sm">


                        {{-- Folder --}}
                        <div>

                            <dt class="text-xs text-gray-400">
                                Folder
                            </dt>

                            <dd class="font-medium text-ink mt-1">

                                @if($document->folder)

                                    <a href="{{ route('commercial-productions.index', ['folder' => $document->folder->id]) }}"
                                       class="inline-flex items-center gap-1.5
                                              text-primary
                                              hover:underline">

                                        <svg class="w-3.5 h-3.5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/>

                                        </svg>

                                        <span class="truncate">
                                            {{ $document->folder->name }}
                                        </span>

                                    </a>

                                @else

                                    <span class="inline-flex items-center gap-1.5">

                                        <svg class="w-3.5 h-3.5 text-gray-400"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/>

                                        </svg>

                                        Root

                                    </span>

                                @endif

                            </dd>

                        </div>


                        {{-- File Type --}}
                        <div>

                            <dt class="text-xs text-gray-400">
                                Tipe File
                            </dt>

                            <dd class="font-medium text-ink mt-1">
                                {{ strtoupper($document->extension ?? '-') }}

                                <span class="font-normal text-gray-400">
                                    ({{ $document->mime_type ?? '-' }})
                                </span>
                            </dd>

                        </div>


                        {{-- Size --}}
                        <div>

                            <dt class="text-xs text-gray-400">
                                Ukuran
                            </dt>

                            <dd class="font-medium text-ink mt-1">
                                {{ $document->formatted_size }}
                            </dd>

                        </div>


                        {{-- Uploader --}}
                        <div>

                            <dt class="text-xs text-gray-400">
                                Diunggah Oleh
                            </dt>

                            <dd class="font-medium text-ink mt-1
                                       flex items-center gap-1.5">

                                <span class="w-6 h-6 rounded-full
                                             bg-primary/10
                                             flex items-center justify-center
                                             text-xs font-bold text-primary">

                                    {{ strtoupper(
                                        substr(
                                            $document->uploader?->name ?? '?',
                                            0,
                                            1
                                        )
                                    ) }}

                                </span>

                                <span class="truncate">
                                    {{ $document->uploader?->name ?? '—' }}
                                </span>

                            </dd>

                        </div>


                        {{-- Upload Date --}}
                        <div>

                            <dt class="text-xs text-gray-400">
                                Tanggal Upload
                            </dt>

                            <dd class="font-medium text-ink mt-1">
                                {{ $document->created_at->format('d M Y H:i') }}
                            </dd>

                        </div>


                        {{-- Updated Date --}}
                        <div>

                            <dt class="text-xs text-gray-400">
                                Terakhir Diperbarui
                            </dt>

                            <dd class="font-medium text-ink mt-1">
                                {{ $document->updated_at->format('d M Y H:i') }}
                            </dd>

                        </div>

                    </dl>


                    {{-- =================================================
                        DESCRIPTION
                    ================================================== --}}
                    @if($document->description)

                        <div class="mt-5 pt-4
                                    border-t border-gray-100">

                            <p class="text-xs font-medium text-gray-400">
                                Deskripsi
                            </p>

                            <p class="text-sm text-gray-600
                                      mt-1 leading-relaxed">
                                {{ $document->description }}
                            </p>

                        </div>

                    @endif


                    {{-- =================================================
                        ACTION BUTTONS
                    ================================================== --}}
                    <div class="flex flex-wrap items-center gap-2
                                mt-5 pt-4
                                border-t border-gray-100">


                        {{-- Preview --}}
                        @if($document->is_previewable)

                            <a href="{{ route('commercial-productions.documents.preview', $document) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2
                                      px-3.5 py-2
                                      rounded-xl
                                      bg-primary
                                      text-white
                                      text-sm font-medium
                                      hover:bg-primary/90
                                      transition">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>

                                </svg>

                                Preview

                            </a>

                        @endif


                        {{-- Download --}}
                        <a href="{{ route('commercial-productions.documents.download', $document) }}"
                           class="inline-flex items-center gap-2
                                  px-3.5 py-2
                                  rounded-xl
                                  border border-gray-200
                                  bg-white
                                  text-sm font-medium
                                  text-gray-600
                                  hover:bg-gray-50
                                  transition">

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>

                            </svg>

                            Download

                        </a>


                        {{-- Rename --}}
                        <button
                            onclick="document.getElementById('renameModal').classList.remove('hidden')"
                            class="px-3.5 py-2
                                   rounded-xl
                                   border border-gray-200
                                   bg-white
                                   text-sm
                                   text-gray-600
                                   hover:bg-gray-50
                                   transition">

                            Rename

                        </button>


                        {{-- Move --}}
                        <button
                            onclick="document.getElementById('moveModal').classList.remove('hidden')"
                            class="px-3.5 py-2
                                   rounded-xl
                                   border border-gray-200
                                   bg-white
                                   text-sm
                                   text-gray-600
                                   hover:bg-gray-50
                                   transition">

                            Move

                        </button>


                        {{-- Delete --}}
                        <form method="POST"
                              action="{{ route('commercial-productions.documents.destroy', $document) }}"
                              onsubmit="return confirm('Hapus file ini beserta semua versinya?')"
                              class="inline">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="px-3.5 py-2
                                           rounded-xl
                                           bg-red-50
                                           text-red-600
                                           text-sm font-medium
                                           hover:bg-red-100
                                           transition">

                                Delete

                            </button>

                        </form>

                    </div>

                </div>

            </div>


            {{-- =================================================
                VERSION HISTORY
            ================================================== --}}
            @if($document->versions->count() > 0)

                <div class="bg-white border border-gray-100
                            rounded-2xl overflow-hidden">


                    {{-- History Header --}}
                    <div class="px-4 sm:px-5 py-3
                                border-b border-gray-100
                                flex items-center justify-between gap-3">

                        <h2 class="text-sm font-semibold text-ink">
                            Riwayat Versi
                        </h2>

                        <span class="text-xs px-2 py-1
                                     rounded-full
                                     bg-gray-100
                                     text-gray-500
                                     flex-shrink-0">

                            {{ $document->versions->count() }}
                            versi sebelumnya

                        </span>

                    </div>


                    {{-- History List --}}
                    <div class="divide-y divide-gray-50">

                        @foreach($document->versions as $version)

                            <div class="px-4 sm:px-5 py-3.5
                                        flex items-center justify-between
                                        gap-4
                                        hover:bg-gray-50/50
                                        transition">


                                {{-- Version Info --}}
                                <div class="flex items-center gap-3 min-w-0">

                                    <div class="w-8 h-8 rounded-lg
                                                bg-gray-100
                                                flex items-center justify-center
                                                text-xs font-bold
                                                text-gray-500
                                                flex-shrink-0">

                                        v{{ $version->version }}

                                    </div>


                                    <div class="min-w-0">

                                        <p class="text-sm font-medium
                                                  text-ink truncate">

                                            {{ $version->file_name }}

                                        </p>

                                        <p class="text-xs text-gray-400
                                                  mt-0.5 truncate">

                                            {{ $version->formatted_size }}
                                            ·
                                            {{ $version->uploader?->name ?? '—' }}
                                            ·
                                            {{ $version->created_at->format('d M Y H:i') }}

                                        </p>

                                    </div>

                                </div>


                                {{-- Download Version --}}
                                <a href="{{ route('commercial-productions.versions.download', $version) }}"
                                   class="inline-flex items-center
                                          px-3 py-1.5
                                          rounded-lg
                                          border border-gray-200
                                          text-xs font-medium
                                          text-gray-600
                                          hover:bg-white
                                          transition
                                          flex-shrink-0">

                                    Download

                                </a>

                            </div>

                        @endforeach

                    </div>

                </div>

            @endif

        </div>


        {{-- =====================================================
            RIGHT COLUMN - PREVIEW
        ====================================================== --}}
        <div class="lg:col-span-1">

            <div class="bg-white border border-gray-100
                        rounded-2xl overflow-hidden
                        sticky top-4">


                {{-- Preview Header --}}
                <div class="px-4 sm:px-5 py-3
                            border-b border-gray-100">

                    <h2 class="text-sm font-semibold text-ink">
                        Preview
                    </h2>

                    <p class="text-xs text-gray-400 mt-0.5">
                        Pratinjau dokumen jika didukung
                    </p>

                </div>


                {{-- Preview Body --}}
                <div class="p-3 sm:p-4">


                    {{-- PDF --}}
                    @if(strtolower($document->extension ?? '') === 'pdf')

                        <iframe
                            src="{{ route('commercial-productions.documents.preview', $document) }}"
                            class="w-full h-[500px]
                                   border border-gray-100
                                   rounded-xl
                                   bg-gray-50">
                        </iframe>


                    {{-- IMAGE --}}
                    @elseif(in_array(
                        strtolower($document->extension ?? ''),
                        ['jpg', 'jpeg', 'png', 'gif', 'webp']
                    ))

                        <img
                            src="{{ Storage::url($document->file_path) }}"
                            alt="Preview"
                            class="w-full
                                   rounded-xl
                                   border border-gray-100
                                   object-contain">


                    {{-- NO PREVIEW --}}
                    @else

                        <div class="text-center py-12 px-4">

                            <div class="w-14 h-14 rounded-2xl
                                        bg-gray-50
                                        flex items-center justify-center
                                        mx-auto mb-3
                                        text-2xl">

                                {{ $document->icon }}

                            </div>

                            <p class="text-sm font-medium text-gray-500">
                                Preview tidak tersedia
                            </p>

                            <p class="text-xs text-gray-400
                                      mt-1 leading-relaxed">

                                Tipe .{{ $document->extension }}
                                belum didukung pratinjau.
                                <br>
                                Silakan download untuk melihat.

                            </p>


                            <a href="{{ route('commercial-productions.documents.download', $document) }}"
                               class="inline-flex items-center gap-2
                                      mt-4
                                      px-4 py-2
                                      rounded-xl
                                      bg-ink
                                      text-white
                                      text-sm
                                      hover:bg-ink/90
                                      transition">

                                Download

                            </a>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        RENAME MODAL
    ========================================================== --}}
    <div id="renameModal"
         class="hidden fixed inset-0
                bg-black/50 backdrop-blur-sm
                flex items-center justify-center
                z-50 p-4">

        <div class="bg-white rounded-2xl
                    shadow-xl
                    p-5 sm:p-6
                    w-full max-w-md">


            <h3 class="font-semibold text-ink">
                Rename File
            </h3>

            <p class="text-xs text-gray-400 mt-1">
                Nama baru akan berlaku untuk versi saat ini
            </p>


            <form method="POST"
                  action="{{ route('commercial-productions.documents.update', $document) }}"
                  class="mt-4">

                @csrf
                @method('PUT')


                {{-- Name --}}
                <input
                    type="text"
                    name="original_name"
                    value="{{ $document->original_name }}"
                    required
                    class="form-input w-full">


                {{-- Description --}}
                <textarea
                    name="description"
                    rows="3"
                    placeholder="Deskripsi..."
                    class="form-input w-full mt-3">{{ $document->description }}</textarea>


                {{-- Actions --}}
                <div class="flex justify-end gap-2 mt-5">

                    <button
                        type="button"
                        onclick="document.getElementById('renameModal').classList.add('hidden')"
                        class="px-4 py-2
                               rounded-xl
                               border border-gray-200
                               text-sm
                               text-gray-600
                               hover:bg-gray-50
                               transition">

                        Batal

                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2
                               rounded-xl
                               bg-primary
                               text-white
                               text-sm
                               font-medium
                               hover:bg-primary/90
                               transition">

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
        MOVE MODAL
    ========================================================== --}}
    <div id="moveModal"
         class="hidden fixed inset-0
                bg-black/50 backdrop-blur-sm
                flex items-center justify-center
                z-50 p-4">

        <div class="bg-white rounded-2xl
                    shadow-xl
                    p-5 sm:p-6
                    w-full max-w-md">


            <h3 class="font-semibold text-ink">
                Pindahkan File
            </h3>

            <p class="text-xs text-gray-400 mt-1">
                Pilih folder tujuan baru
            </p>


            {{-- Information --}}
            <div class="mt-4 p-3
                        rounded-xl
                        bg-amber-50
                        border border-amber-100
                        text-sm text-amber-700
                        leading-relaxed">

                Fitur pindah folder akan tersedia di versi berikutnya.
                Untuk sekarang, hapus dan upload ulang ke folder tujuan.

            </div>


            {{-- Close --}}
            <div class="flex justify-end mt-5">

                <button
                    type="button"
                    onclick="document.getElementById('moveModal').classList.add('hidden')"
                    class="px-4 py-2
                           rounded-xl
                           bg-ink
                           text-white
                           text-sm
                           hover:bg-ink/90
                           transition">

                    Tutup

                </button>

            </div>

        </div>

    </div>

</x-app-layout>