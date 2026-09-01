<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('approval-formula-designs.index', ['type' => $type]) }}" class="hover:text-primary">Approval {{ $type }}</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Tambah</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Tambah Approval Formula & Design</h1>
        </header>

        <form method="POST" action="{{ route('approval-formula-designs.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="card card-body space-y-5">
                @include('approval-formula-designs.partials.form-fields', ['categories' => $categories, 'products' => $products, 'type' => $type])
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('approval-formula-designs.index', ['type' => $type]) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</x-app-layout>
