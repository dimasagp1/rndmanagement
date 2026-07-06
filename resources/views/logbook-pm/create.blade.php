<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('logbook-pm.index') }}" class="hover:text-primary">Log Book PM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Tambah Entri Baru</span>
        </div>
    </x-slot>

    @if($errors->any())
    <div class="alert-danger mb-4">
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('logbook-pm.store') }}" enctype="multipart/form-data"
          x-data="logbookForm()" id="logbook-create-form">
        @csrf

        <div class="page-header mb-4">
            <h1 class="page-title">Tambah Entri Log Book PM</h1>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary" id="btn-simpan-logbook">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Entri
                </button>
                <a href="{{ route('logbook-pm.index') }}" class="btn-ghost">Batal</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- ─── KOLOM KIRI ─── --}}
            <div class="space-y-5">

                {{-- A. IDENTITAS BAHAN --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">A. Identitas Bahan Pengemas</h2>
                    </div>
                    <div class="card-body space-y-3">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="tanggal_terima">Tanggal Terima *</label>
                                <input type="date" id="tanggal_terima" name="tanggal_terima"
                                       value="{{ old('tanggal_terima', date('Y-m-d')) }}"
                                       class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label" for="jenis_kemasan">Jenis Kemasan *</label>
                                <input type="text" id="jenis_kemasan" name="jenis_kemasan"
                                       value="{{ old('jenis_kemasan') }}"
                                       placeholder="sachet, botol, blister, tube..."
                                       class="form-input" required>
                            </div>
                        </div>

                        {{-- Supplier --}}
                        <div>
                            <label class="form-label" for="supplier_id">Supplier/Produsen</label>
                            <select id="supplier_id" name="supplier_id" x-model="supplierId"
                                    @change="onSupplierChange()" class="form-input">
                                <option value="">-- Pilih dari Master Data --</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->name }}
                                </option>
                                @endforeach
                                <option value="manual">+ Ketik Manual</option>
                            </select>
                        </div>
                        <div x-show="showManualSupplier" x-transition>
                            <label class="form-label" for="nama_supplier_manual">Nama Supplier Manual</label>
                            <input type="text" id="nama_supplier_manual" name="nama_supplier_manual"
                                   value="{{ old('nama_supplier_manual') }}"
                                   placeholder="Nama supplier/produsen..."
                                   class="form-input">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="nama_material">Nama Material *</label>
                                <input type="text" id="nama_material" name="nama_material"
                                       value="{{ old('nama_material') }}"
                                       class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label" for="deskripsi_material">Deskripsi Material</label>
                                <input type="text" id="deskripsi_material" name="deskripsi_material"
                                       value="{{ old('deskripsi_material') }}"
                                       placeholder="Ukuran, warna, bahan..."
                                       class="form-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="kode_bahan">Kode Bahan</label>
                                <input type="text" id="kode_bahan" name="kode_bahan"
                                       value="{{ old('kode_bahan') }}"
                                       class="form-input font-mono">
                            </div>
                            <div>
                                <label class="form-label" for="no_sample">No. Sample</label>
                                <input type="text" id="no_sample" name="no_sample"
                                       value="{{ old('no_sample') }}"
                                       class="form-input font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="jumlah_diterima">Jumlah Diterima *</label>
                                <input type="number" id="jumlah_diterima" name="jumlah_diterima"
                                       value="{{ old('jumlah_diterima') }}"
                                       step="0.001" min="0"
                                       class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label" for="satuan">Satuan *</label>
                                <input type="text" id="satuan" name="satuan"
                                       value="{{ old('satuan') }}"
                                       placeholder="pcs, kg, roll, lembar..."
                                       class="form-input" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="lokasi_penyimpanan">Lokasi Penyimpanan</label>
                            <input type="text" id="lokasi_penyimpanan" name="lokasi_penyimpanan"
                                   value="{{ old('lokasi_penyimpanan') }}"
                                   placeholder="Gudang A / Rak 3 / ..."
                                   class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="nama_penerima">Nama Penerima (R&D) *</label>
                            <input type="text" id="nama_penerima" name="nama_penerima"
                                   value="{{ old('nama_penerima', auth()->user()->name) }}"
                                   class="form-input" required>
                        </div>
                    </div>
                </div>

                {{-- B. KONDISI PENERIMAAN --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">B. Kondisi Penerimaan</h2>
                    </div>
                    <div class="card-body space-y-3">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="kelengkapan_dokumen">Kelengkapan Dokumen *</label>
                                <select id="kelengkapan_dokumen" name="kelengkapan_dokumen" class="form-input" required>
                                    @foreach(['Lengkap','Sebagian','Tidak Lengkap'] as $opt)
                                    <option value="{{ $opt }}" {{ old('kelengkapan_dokumen', 'Lengkap') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="kondisi_fisik">Fisik (Ringkasan)</label>
                                <select id="kondisi_fisik" name="kondisi_fisik" class="form-input">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Baik','Cacat','Rusak','Perlu Review'] as $opt)
                                    <option value="{{ $opt }}" {{ old('kondisi_fisik') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="kondisi_fisik_aktual">Kondisi Fisik Aktual *</label>
                            <textarea id="kondisi_fisik_aktual" name="kondisi_fisik_aktual" rows="3"
                                      placeholder="Deskripsikan kondisi fisik bahan pengemas yang diterima..."
                                      class="form-input">{{ old('kondisi_fisik_aktual') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ─── KOLOM KANAN ─── --}}
            <div class="space-y-5">

                {{-- C. CATATAN TRIAL --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">C. Catatan Trial</h2>
                    </div>
                    <div class="card-body space-y-3">

                        <div>
                            <label class="form-label" for="trial_pm_id">Link ke Trial PM (Opsional)</label>
                            <select id="trial_pm_id" name="trial_pm_id" x-model="trialPmId"
                                    @change="loadTrialData()" class="form-input">
                                <option value="">-- Tidak ada / isi manual --</option>
                                @foreach($trialPms as $tp)
                                <option value="{{ $tp->id }}" {{ old('trial_pm_id') == $tp->id ? 'selected' : '' }}>
                                    {{ $tp->code }} — {{ $tp->packaging_material }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Pilih Trial PM untuk auto-isi Catatan Trial</p>
                        </div>

                        <div x-show="loadingTrial" class="flex items-center gap-2 text-xs text-primary">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Mengambil data trial...
                        </div>

                        <div>
                            <label class="form-label" for="catatan_trial">Catatan Trial</label>
                            <textarea id="catatan_trial" name="catatan_trial" rows="6"
                                      x-model="catatanTrial"
                                      placeholder="Catatan hasil trial bahan pengemas ini. Bisa diisi otomatis dari Trial PM di atas."
                                      class="form-input font-mono text-xs">{{ old('catatan_trial') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" for="status_pengujian">Status Pengujian *</label>
                                <select id="status_pengujian" name="status_pengujian" class="form-input" required>
                                    @foreach(['Pending','Proses','Lulus','Tidak Lulus'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status_pengujian', 'Pending') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- D. DOKUMENTASI --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">D. Dokumentasi</h2>
                    </div>
                    <div class="card-body space-y-3">

                        <div>
                            <label class="form-label" for="lampiran_files">Lampiran Dokumentasi (Foto / PDF)</label>
                            <input type="file" id="lampiran_files" name="lampiran_files[]"
                                   multiple accept="image/*,.pdf"
                                   class="form-input text-sm py-1.5">
                            <p class="text-xs text-gray-400 mt-1">Bisa upload beberapa file. Max 5 MB/file.</p>
                        </div>

                        <div>
                            <label class="form-label" for="file_scan_upload">File Scan (CoA / DO / Sertifikat)</label>
                            <input type="file" id="file_scan_upload" name="file_scan_upload"
                                   accept="image/*,.pdf"
                                   class="form-input text-sm py-1.5">
                            <p class="text-xs text-gray-400 mt-1">1 file. Max 10 MB.</p>
                        </div>

                    </div>
                </div>

                {{-- E. KETERANGAN --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">E. Keterangan Tambahan</h2>
                    </div>
                    <div class="card-body">
                        <textarea id="keterangan" name="keterangan" rows="3"
                                  placeholder="Catatan tambahan lainnya..."
                                  class="form-input">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

            </div>
        </div>

    </form>

</x-app-layout>

<script>
function logbookForm() {
    return {
        supplierId: '{{ old('supplier_id', '') }}',
        showManualSupplier: {{ old('nama_supplier_manual') ? 'true' : 'false' }},
        trialPmId: '{{ old('trial_pm_id', '') }}',
        catatanTrial: `{{ old('catatan_trial', '') }}`,
        loadingTrial: false,

        onSupplierChange() {
            this.showManualSupplier = this.supplierId === 'manual';
            if (this.supplierId === 'manual') {
                // Clear the actual supplier_id so it won't be submitted
                this.$nextTick(() => {
                    document.getElementById('supplier_id').value = '';
                });
                this.supplierId = '';
                this.showManualSupplier = true;
            }
        },

        async loadTrialData() {
            if (!this.trialPmId) return;
            this.loadingTrial = true;
            try {
                const res = await fetch(`/logbook-pm/get-trial-data/${this.trialPmId}`);
                const data = await res.json();
                this.catatanTrial = data.summary;
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingTrial = false;
            }
        }
    };
}
</script>
