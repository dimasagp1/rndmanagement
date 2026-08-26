<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('packaging-developments.index') }}" class="hover:text-primary transition">Packaging Development</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Packaging Development Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Packaging Development Baru</h1>
            <p class="page-subtitle">Buat project pengembangan kemasan baru. Data lengkap (spesifikasi, packaging, trial, dll) diisi di halaman detail.</p>
        </div>
    </div>

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('packaging-developments.store') }}" class="p-6 space-y-5">
            @csrf
            @include('packaging-developments.partials.form-fields')

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="btn-primary">Simpan & Lanjutkan</button>
                <a href="{{ route('packaging-developments.index') }}" class="btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>