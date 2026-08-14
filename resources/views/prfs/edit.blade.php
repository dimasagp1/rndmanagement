<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">PRF</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Edit {{ $prf->code }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit PRF</h1>
            <p class="text-sm text-gray-500">
                Perbaiki PRF {{ $prf->code }} sebelum diajukan kembali untuk approval.
            </p>
        </header>

        <form method="POST" action="{{ route('prfs.update', $prf) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card card-body space-y-4">
                <h2 class="text-sm font-heading font-semibold text-ink">Informasi PRF</h2>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Nomor PRF</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $prf->code) }}"
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-primary">
                    @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="requestor" class="block text-sm font-medium text-gray-700 mb-1">Requestor *</label>
                        <input type="text" id="requestor" name="requestor" value="{{ old('requestor', $prf->requestor) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('requestor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                        <select id="department" name="department"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ old('department', $prf->department) === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                        @error('department') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="product_concept" class="block text-sm font-medium text-gray-700 mb-1">Product Concept *</label>
                    <textarea id="product_concept" name="product_concept" rows="4"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('product_concept', $prf->product_concept) }}</textarea>
                    @error('product_concept') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="target_market" class="block text-sm font-medium text-gray-700 mb-1">Target Market</label>
                        <input type="text" id="target_market" name="target_market" value="{{ old('target_market', $prf->target_market) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('target_market') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_category" class="block text-sm font-medium text-gray-700 mb-1">Product Category</label>
                        <input type="text" id="product_category" name="product_category" value="{{ old('product_category', $prf->product_category) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('product_category') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="target_launch" class="block text-sm font-medium text-gray-700 mb-1">Target Launch</label>
                        <input type="date" id="target_launch" name="target_launch"
                               value="{{ old('target_launch', $prf->target_launch?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('target_launch') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name', $prf->product_name) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('product_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('prfs.show', $prf) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>