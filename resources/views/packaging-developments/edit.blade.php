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
            <a href="{{ route('packaging-developments.show', $packagingDevelopment) }}" class="hover:text-primary transition">{{ $packagingDevelopment->code }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Edit</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Packaging Development</h1>
            <p class="page-subtitle">{{ $packagingDevelopment->code }} — {{ $packagingDevelopment->product_name }}</p>
        </div>
    </div>

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('packaging-developments.update', $packagingDevelopment) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            @include('packaging-developments.partials.form-fields')

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('packaging-developments.show', $packagingDevelopment) }}" class="btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>