<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('sample-evaluations.index') }}" class="hover:text-primary transition">Sample Evaluation</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Evaluasi Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Evaluasi Baru</h1>
            <p class="page-subtitle">Buat catatan evaluasi sampel baru</p>
        </div>
    </div>

    <div class="card max-w-2xl">
        <form method="POST" action="{{ route('sample-evaluations.store') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label for="sample_id" class="block text-sm font-medium text-gray-700 mb-1">Sample ID <span class="text-red-500">*</span></label>
                <input type="text" id="sample_id" name="sample_id" value="{{ old('sample_id', $autoSampleId) }}"
                       class="form-input @error('sample_id') border-red-400 @enderror" required>
                @error('sample_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="npd_proposal_id" class="block text-sm font-medium text-gray-700 mb-1">Product Name (Dari NPD Proposal) <span class="text-red-500">*</span></label>
                <select id="npd_proposal_id" name="npd_proposal_id" class="form-input @error('npd_proposal_id') border-red-400 @enderror" required>
                    <option value="">— Pilih Produk dari NPD Proposal —</option>
                    @foreach($npdProposals as $proposal)
                    <option value="{{ $proposal->id }}" {{ old('npd_proposal_id') == $proposal->id ? 'selected' : '' }}>
                        {{ $proposal->code }} — {{ $proposal->product_name }}
                    </option>
                    @endforeach
                </select>
                @error('npd_proposal_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="project_owner_id" class="block text-sm font-medium text-gray-700 mb-1">Project Owner <span class="text-red-500">*</span></label>
                <select id="project_owner_id" name="project_owner_id" class="form-input @error('project_owner_id') border-red-400 @enderror" required>
                    <option value="">— Pilih Project Owner —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('project_owner_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
                @error('project_owner_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="btn-primary">Simpan Evaluasi</button>
                <a href="{{ route('sample-evaluations.index') }}" class="btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>