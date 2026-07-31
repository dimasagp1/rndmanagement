# 📘 DOKUMENTASI LENGKAP R&D MANAGEMENT SYSTEM
### PT. Herbatech Innopharma Industry

---

## 📌 1. Identitas & Informasi Umum Project

| Parameter | Detail |
|---|---|
| **Nama Project** | **R&D Management System** (System Manajemen Research & Development) |
| **Perusahaan** | PT. Herbatech Innopharma Industry |
| **Tanggal Mulai Project** | **2 Juli 2026** (Commit Pertama pada pukul 10:01:28 WIB) |
| **Tanggal Terakhir Diperbarui** | **13 Juli 2026** |
| **Tech Stack Utama** | Laravel 11/12 (PHP 8.2+), Tailwind CSS, Alpine.js, TomSelect, Spatie Permission & Activitylog, DomPDF |
| **Kontributor / Dev** | `Antigravity` & `Herbatech Dev` |
| **Lokasi Repository** | `c:\laragon\www\rnd-management` |

---

## 🎯 2. Penjelasan Sistem & Arsitektur (Apa yang Dibuat)

**R&D Management System** adalah platform web enterprise yang dirancang khusus untuk mendigitalisasi, mengotomatisasi, serta mengontrol seluruh alur kerja formulasi dan pengujian produk herbal di **PT. Herbatech Innopharma Industry**, mulai dari tahap prapembuatan resep hingga keputusan skala produksi komersial.

### 🏛️ 3 Pilar Utama Sistem

```
┌─────────────────────────┐       ┌─────────────────────────┐       ┌─────────────────────────┐
│   1. Formulasi RM       │ ──►   │    2. Trial RM          │       │    3. Trial PM          │
│   (Raw Material)        │       │    (Raw Material)       │       │  (Packaging Material)   │
└─────────────────────────┘       └─────────────────────────┘       └─────────────────────────┘
  • Draf Resep & Komposisi          • Uji Pencampuran Sampel          • Uji Bahan Kemas di Mesin
  • Total Rasio Wajib 100%          • Organoleptik & Fis-Kim          • Verifikasi 4 Departemen
  • Auto-Clone Reformulasi          • Target vs Aktual                • Log Book PM & Cetak
```

1. **Formulasi Raw Material (RM)**: Modul sentral perekaman resep bahan baku dasar produk herbal, komposisi rasio persentase (wajib 100%), kalkulasi dosis & HPP, supplier, serta penanganan reformulasi otomatis (versioning).
2. **Catatan Trial Raw Material (RM)**: Modul eksekusi uji coba laboratorium. Menarik data formula dari Pilar 1 secara otomatis untuk menguji kelaikan fisik, kimia, dan organoleptik (Warna, Rasa, Bau, pH, Viskositas, Berat Jenis).
3. **Catatan Trial Packaging Material (PM) & Log Book PM**: Modul pengujian bahan kemas pada mesin pengemas yang melibatkan persetujuan kolektif 4 departemen (R&D, QC, Produksi, Engineering). Lengkap dengan modul Log Book PM dan cetak laporan.

---

## 👥 3. Struktur Pengguna & Peran (RBAC)

| Peran | Hak Akses & Tanggung Jawab |
|---|---|
| **Superadmin** | Akses penuh ke seluruh fitur, manajemen user, konfigurasi branding (Logo, Favicon, Name), master data, dan Approval Center. |
| **Staff R&D** | Inisiator (Create, Edit draf, Submit). Mengisi data komposisi, parameter trial harian, upload dokumen/sample. |
| **Operational Manager (OM)** | Evaluator Teknis Tahap 1. Meninjau draf, memberikan ulasan/catatan revisi, dan menyetujui Tahap 1. |
| **General Manager (Ibu Lisa)** | Final Approver (Tahap 2). Hak akses Read-only penuh dan eksekusi persetujuan final skala produksi komersial. |

---

## 💻 4. Fitur-Fitur Utama Dashboard & Aplikasi

1. **Dashboard Web Real-Time**:
   - **Stat Cards**: Total Formulasi RM Approved, Total Trial RM, Total Trial PM, dan Pending Approvals kontekstual sesuai role logged-in user.
   - **Approval Pipeline Visualizer**: Tracing status dokumen `Draft ➔ Pending Tahap 1 ➔ Pending Tahap 2 ➔ Approved`.
   - **Activity Feed**: Log aktivitas real-time menggunakan `Spatie Activitylog` untuk audit trail.
   - **My Items Widget**: Pintasan draf & pengajuan terbaru bagi Staff R&D.

2. **Approval Center Berjenjang**:
   - Pusat persetujuan terpusat untuk meninjau, menyetujui, atau menolak dokumen Formulasi RM, Trial RM, dan Trial PM.

3. **Modul Log Book PM & Document Preview**:
   - Pratinjau cetak inline via modal iframe berbasis Alpine.js.
   - Popup preview berkas/scan nomor sampel.
   - Cetak masal seluruh entri Log Book PM.

4. **Dynamic System Settings**:
   - Pengaturan dynamic branding (Logo Aplikasi, Favicon, Nama Aplikasi, dan Logo Khusus Cetak/Print Layout).

5. **TomSelect Searchable Dropdown**:
   - Pencarian cepat dan presisi untuk dropdown Bahan Baku (Material) dan Supplier.

---

## 📜 5. Kronologi Perubahan Lengkap (Dari Awal sampai Akhir)

Berikut adalah rekam jejak evolusi pengembangan project dari commit pertama hingga pembaruan terakhir:

```
[02 Jul 2026] ──────► [03 Jul 2026] ──────► [06 Jul 2026] ──────► [07-08 Jul 2026] ──────► [09-13 Jul 2026]
Baseline Launch       TomSelect & Dropdown   Log Book PM & Modal    Branding & Watermark    Layout & Cleanup
```

### 🗓️ Detail Rincian Commit History

| Tanggal | Dev / Committer | Pesan Commit & Rincian Perubahan |
|---|---|---|
| **02 Juli 2026** | `Antigravity` | **Initial System Launch**: Peluncuran seluruh fondasi R&D Management System (Checkpoints 1-10). Menyiapkan struktur database, auth, Spatie RBAC, Formulasi RM, Trial RM, Trial PM, Approval Center, Superadmin settings, dan Master Data CRUD. |
| **02 Juli 2026** | `Antigravity` | **Superadmin & Master Data**: Menyelesaikan modul Superadmin settings, upload dinamis logo & favicon, serta master data R&D Admin. |
| **02 Juli 2026** | `Herbatech Dev` | **Formulation & Print Views Redesign**: Redesign tampilan Formula RM, penambahan slot paraf admin, serta penyempurnaan layout cetak (print views) untuk Trial PM, Trial RM, dan Formula. |
| **03 Juli 2026** | `Herbatech Dev` | **UX Dropdown & Form Labeling**: Integrasi library **TomSelect** untuk pencarian dropdown material & supplier, pembaruan label bentuk sediaan & aplikasi penggunaan, serta penambahan script `run-dev`. |
| **03 Juli 2026** | `Herbatech Dev` | **Asset Compilation**: Kompilasi asset untuk produksi dan perbaikan `app.js` menggunakan `MutationObserver` agar TomSelect berjalan stabil di dynamic DOM. |
| **03 Juli 2026** | `Herbatech Dev` | **Trial PM & RM Code Rules**: Menyertakan proposal_number ke dalam generator kode Trial PM, kemudian menghapus prefix `TPM-` agar murni menggunakan nomor proposal, serta membuka akses pengeditan manual untuk kode Formula & Trial RM. |
| **03 Juli 2026** | `Antigravity` | **Alpine Fix**: Memperbaiki toggle form input penilaian Trial PM dengan mengganti `x-collapse` menjadi `x-show`. |
| **06 Juli 2026** | `Herbatech Dev` | **Workflow UI & Shared Hosting Compatibility**: Redesign UI Approval Workflow inline, lencana status premium, perataan watermark, penambahan penanganan HTTPS paksa (`Force HTTPS`), override dynamic public path untuk environment shared hosting (Laravel 11 `bootstrap/app.php`). |
| **06 Juli 2026** | `Herbatech Dev` | **Formula Editable Fields & Log Book PM**: Membuka input pengeditan dosis `dose_2g`, `dose_05g`, `hpp_rm`, serta satuan di view formula. Peluncuran modul baru **Log Book PM** beserta link sidebar. |
| **06 Juli 2026** | `Herbatech Dev` | **Modal Print Preview & File Preview**: Implementasi modal inline print preview menggunakan Alpine.js & iframe pada Log Book PM. Penambahan modal preview lampiran/dokumen scan pada index page dan detail entri. |
| **07 Juli 2026** | `Antigravity` | **Guest Layout & Dynamic Print Logo**: Perbaikan loading logo/favicon dinamis di guest layout, penambahan tanggal formula cast ke `date`, pembuatan fitur **Logo Menu Cetak** terpisah untuk template print, dan penambahan watermark `CONFIDENTIAL`. |
| **07 Juli 2026** | `Herbatech Dev` | **Print Layout & Margins Polish**: Perbaikan margin, break line, dan page breaks pada template cetak Formula, Trial RM, dan Trial PM. |
| **08 Juli 2026** | `Antigravity` | **Multi-Product Trial PM & Workflow Fixes**: Dukungan multiple products pada field `product_use` & `product_trial` Trial PM. Perbaikan database seeders agar *idempotent*. Perbaikan routing workflow persetujuan (wajib approval OM) dan pembukaan akses Approval Center bagi Superadmin. |
| **09 Juli 2026** | `Antigravity` | **Print Layout Cleanups**: Penghapusan watermark `CONFIDENTIAL` pada print layout Trial PM, penyesuaian footer cetak, penyingkiran nomor halaman otomatis, dan perbaikan CSS container min-height. |
| **13 Juli 2026** | `Antigravity` | **Login Page Cleanups & Final Release**: Penghapusan blok demo credentials pada halaman login dan finalisasi UI dashboard. |

---

## 🚀 6. Ringkasan Eksekutif

Project **R&D Management System** dimulai pada tanggal **2 Juli 2026** dan telah melalui serangkaian iterasi intensif hingga versi stabil pada **13 Juli 2026**. Aplikasi ini kini sepenuhnya siap digunakan (*Production Ready*) oleh **PT. Herbatech Innopharma Industry** untuk mengelola seluruh siklus riset dan pengujian produk herbal secara digital, akurat, dan dapat dipertanggungjawabkan.
