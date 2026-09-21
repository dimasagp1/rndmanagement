<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('formula-approvals.index', ['type' => $formApproval->type]) }}" class="hover:text-primary transition shrink-0">Approval {{ $formApproval->type }}</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Edit {{ $formApproval->product_name }} — {{ $formApproval->revision_label }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit Approval {{ $formApproval->type }}</h1>
            <p class="text-sm text-gray-500">{{ $formApproval->code }} · {{ $formApproval->revision_label }} · Status {{ $formApproval->approval_status }}</p>
        </header>

        {{-- Flash & Error Alerts --}}
        @if ($errors->any())
        <div class="alert-danger mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700" role="alert">
            <div class="font-semibold mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Gagal menyimpan perubahan. Harap periksa kolom berikut:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-0.5 ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if (session('error'))
        <div class="alert-danger mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700" role="alert">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('formula-approvals.update', $formApproval) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card card-body space-y-5">
                @include('formula-approvals.partials.form-fields', ['form' => $formApproval, 'categories' => $categories, 'products' => $products, 'type' => $formApproval->type])
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('formula-approvals.show', ['formApproval' => $formApproval, 'type' => $formApproval->type]) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
