<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LogbookPm extends Model
{
    use LogsActivity;

    protected $fillable = [
        'tanggal_terima',
        'supplier_id',
        'nama_supplier_manual',
        'jenis_kemasan',
        'nama_material',
        'deskripsi_material',
        'kode_bahan',
        'no_sample',
        'jumlah_diterima',
        'satuan',
        'kelengkapan_dokumen',
        'kondisi_fisik_aktual',
        'kondisi_fisik',
        'trial_pm_id',
        'catatan_trial',
        'status_pengujian',
        'lampiran_dokumentasi',
        'file_scan',
        'om_approval',
        'om_approved_by',
        'om_approved_at',
        'om_notes',
        'lokasi_penyimpanan',
        'nama_penerima',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_terima'       => 'date',
        'om_approved_at'       => 'datetime',
        'lampiran_dokumentasi' => 'array',
        'jumlah_diterima'      => 'decimal:3',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_material', 'status_pengujian', 'om_approval'])
            ->logOnlyDirty();
    }

    // Relasi
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function trialPm()
    {
        return $this->belongsTo(TrialPm::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function omApprover()
    {
        return $this->belongsTo(User::class, 'om_approved_by');
    }

    // Accessor: nama supplier (master data atau manual)
    public function getNamaSupplierDisplayAttribute(): string
    {
        return $this->supplier?->name ?? $this->nama_supplier_manual ?? '—';
    }

    // Badge color helpers
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_pengujian) {
            'Lulus'       => 'badge-success',
            'Tidak Lulus' => 'badge-danger',
            'Proses'      => 'badge-warning',
            default       => 'badge-secondary',
        };
    }

    public function getOmApprovalBadgeClassAttribute(): string
    {
        return match ($this->om_approval) {
            'Approved' => 'badge-success',
            'Rejected' => 'badge-danger',
            default    => 'badge-warning',
        };
    }
}
