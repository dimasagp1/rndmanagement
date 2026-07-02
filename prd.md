# PRD — R&D Management System
### PT Herbatech Innopharma Industry

| Info | Detail |
|---|---|
| Status | Final/Approved |
| Klasifikasi | Confidential — Internal Only |
| Tema | Research & Development Produk Herbal |
| Stack | Laravel (terbaru) + Tailwind CSS |

---

## 1. Latar Belakang & Tujuan

Sistem Manajemen R&D dirancang untuk mendigitalisasi dan mengotomatisasi seluruh proses formulasi dan pengujian produk di PT Herbatech Innopharma Industry. Tujuan utama: meningkatkan **efisiensi, akurasi, dan keterlacakan (traceability)** data dari fase eksperimen hingga tahap persetujuan produksi.

## 2. Ruang Lingkup — 3 Pilar Utama

| Pilar | Modul | Deskripsi |
|---|---|---|
| 1 | **Formulasi Raw Material (RM)** | Modul sentral untuk merekam & mengembangkan resep bahan baku dasar produk |
| 2 | **Catatan Trial Raw Material (RM)** | Modul eksekusi uji coba bahan baku, terintegrasi langsung dengan Pilar 1 untuk memverifikasi kelaikan formula |
| 3 | **Catatan Trial Packaging Material (PM)** | Modul terpisah untuk menguji kelaikan bahan kemas pada mesin pengemas bersama tim lintas departemen |

### Di luar ruang lingkup (asumsi)
- Manajemen stok/inventaris material (sistem hanya menarik data referensi).
- Manajemen data supplier (hanya dropdown referensi).
- Integrasi ERP/produksi komersial & modul costing.

## 3. Aktor & Pengguna

| Peran | Deskripsi | Hak Akses |
|---|---|---|
| **Staff R&D** | Inisiator | Create, Read, Update (sebelum disetujui) pada Formulasi RM & form Catatan Trial (RM & PM). Mengisi parameter eksekusi harian. |
| **Operational Manager** | Evaluator Teknis | Read seluruh modul. Memberi ulasan, revisi, atau Approval Tahap 1 terhadap hasil trial & formulasi. |
| **General Manager (Ibu Lisa)** | Final Approver | Read-only penuh ke seluruh riwayat. Update khusus untuk Approval Tahap 2 (final) menuju skala produksi komersial. |

## 4. Spesifikasi Fungsional

### 4.1 Modul Formulasi RM

**Alur:** Staff R&D buat draf formula baru → tambah bahan baku & persentase → ajukan untuk ditinjau.

| Kolom | Tipe Data | Deskripsi |
|---|---|---|
| Nama Produk | Text | Nama produk yang dikembangkan (mis. Sirup Herbal X) |
| Kode Formula | Auto-Generate | ID unik sistem (mis. `FRM-202607-001`) |
| Komposisi Material | Table/Array | Multi-input pilih material dari database inventaris internal |
| Supplier | Dropdown | Vendor resmi per material |
| Persentase (%) | Numeric | Rasio bahan baku; total sistem wajib tepat 100% |
| Tahapan Pengembangan | Dropdown | Draf, Pra-Trial, Optimalisasi, Final |
| Status Persetujuan Akhir | Status/Label | Otomatis berubah sesuai aksi GM (Pending/Approved/Rejected) |

### 4.2 Modul Catatan Trial RM (Raw Material)

**Alur:** Pull data dari Formulasi RM → eksekusi pencampuran → catat observasi → verifikasi parameter → putusan akhir.

| Kolom | Tipe Data | Deskripsi |
|---|---|---|
| Identitas Sampel | Text/Auto | Nomor batch trial (mis. `TRM-202607-001-A`) |
| Formula Trial | Lookup | Menarik data dari "Kode Formula" di Modul Formulasi RM |
| Tahapan Proses | Rich Text | Langkah pencampuran, suhu pelarutan, waktu homogenisasi |
| Verifikasi Hasil | Tabel Komparasi | Parameter organoleptik (Warna, Bau, Rasa) & Fisika-Kimia (pH, Viskositas, Berat Jenis) — Target vs Aktual |
| Keputusan Trial | Radio Button | Lulus (Approve) atau Reformulasi (Reject) |

### 4.3 Modul Catatan Trial PM (Packaging Material)

**Alur:** Inisiasi trial → input spesifikasi bahan kemas → pelaksanaan uji mesin → penilaian lintas departemen.

| Kolom | Tipe Data | Deskripsi |
|---|---|---|
| Data Bahan Kemas | Text/Dropdown | Nama/jenis bahan kemas (mis. Botol Kaca 60ml, Blister Foil) |
| Spesifikasi Fisik | Text Area | Dimensi, ketebalan, gramasi, standar kualitas |
| Parameter Pelaksanaan | Numeric/Text | Kecepatan mesin, suhu sealing, tekanan |
| Analisis Risiko | Text Area | Potensi cacat (kebocoran, seal tidak rapat, dll) |
| Kesimpulan Kelaikan | Checkboxes & Text | Persetujuan dari 4 departemen: R&D (estetik/stabilitas), QC (kualitas/uji kebocoran), Produksi (efisiensi kecepatan), Engineering (setting mesin) |

## 5. Integrasi & Aturan Bisnis Kunci

- Integrasi wajib Formulasi RM → Catatan Trial RM (data read-only).
- Auto-clone formula (versi baru) saat keputusan trial = Reformulasi.
- Approval Gate berjenjang: Staff → Operational Manager → General Manager.
- Validasi kolektif 4 departemen untuk Trial PM.

*(Detail lengkap lihat `rules.md`)*

## 6. Lampiran — Referensi Form Input

**FORM CATATAN TRIAL PRODUK/PROSES**
- Section 1
- No. Form
- Identitas Sample Trial
- Tujuan Trial

## 7. Referensi Dokumen Terkait

- `uiux.md` — Rancangan UI/UX, navbar, tema visual, struktur halaman
- `rules.md` — Aturan bisnis, validasi, RBAC, business rules checklist
