<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('formula-approvals.index') }}" class="hover:text-primary transition shrink-0">Formula Approval</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Edit {{ $formApproval->product_name }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit Form Approval</h1>
            <p class="text-sm text-gray-500">{{ $formApproval->product_name }}</p>
        </header>

        <form method="POST" action="{{ route('formula-approvals.update', $formApproval) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card card-body space-y-5">
                @include('formula-approvals.partials.form-fields', ['form' => $formApproval, 'categories' => $categories])
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('formula-approvals.show', $formApproval) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>