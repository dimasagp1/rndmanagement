<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_pms', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->date('tanggal_terima');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('nama_supplier_manual')->nullable(); // jika tidak ada di master data
            $table->string('jenis_kemasan');                   // sachet, botol, blister, tube, dll
            $table->string('nama_material');
            $table->text('deskripsi_material')->nullable();
            $table->string('kode_bahan')->nullable();
            $table->string('no_sample')->nullable();

            // Kuantitas
            $table->decimal('jumlah_diterima', 10, 3);
            $table->string('satuan', 50);                      // pcs, kg, roll, lembar, dll

            // Kondisi Penerimaan
            $table->enum('kelengkapan_dokumen', ['Lengkap', 'Tidak Lengkap', 'Sebagian'])->default('Lengkap');
            $table->text('kondisi_fisik_aktual');
            $table->string('kondisi_fisik', 50)->nullable();   // ringkasan: Baik / Cacat / Rusak

            // Catatan Trial (link ke Trial PM atau free-text)
            $table->foreignId('trial_pm_id')->nullable()->constrained('trial_pms')->onDelete('set null');
            $table->text('catatan_trial')->nullable();

            // Status Pengujian
            $table->enum('status_pengujian', ['Pending', 'Proses', 'Lulus', 'Tidak Lulus'])->default('Pending');

            // Dokumentasi
            $table->json('lampiran_dokumentasi')->nullable();   // array of file paths
            $table->string('file_scan')->nullable();            // CoA, DO, dsb

            // Approval OM
            $table->enum('om_approval', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('om_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('om_approved_at')->nullable();
            $table->text('om_notes')->nullable();

            // Penyimpanan & Penerima
            $table->string('lokasi_penyimpanan')->nullable();
            $table->string('nama_penerima');                    // Nama Penerima R&D
            $table->text('keterangan')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_pms');
    }
};
