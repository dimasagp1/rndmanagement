<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary transition">Stability Test</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Stability Test Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Stability Test Baru</h1>
            <p class="page-subtitle">Buat uji stabilitas untuk produk yang belum memiliki Stability Test.</p>
        </div>
    </div>

    <div class="card max-w-2xl">
        <form method="POST" action="{{ route('stability-tests.store') }}" class="p-6 space-y-5">
            @csrf
            @include('stability-tests.partials.form-fields')

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="btn-primary">Simpan Stability Test</button>
                <a href="{{ route('stability-tests.index') }}" class="btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>