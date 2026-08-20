<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary transition shrink-0">Stability Test</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Edit {{ $stabilityTest->title }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit Stability Test</h1>
            <p class="text-sm text-gray-500">{{ $stabilityTest->title }}</p>
        </header>

        <form method="POST" action="{{ route('stability-tests.update', $stabilityTest) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card card-body space-y-5">
                <div>
                    <label for="title" class="form-label">
                        Judul Tes <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                           value="{{ old('title', $stabilityTest->title) }}"
                           class="form-input {{ $errors->has('title') ? 'border-red-400' : '' }}">
                    @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('stability-tests.show', $stabilityTest) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>