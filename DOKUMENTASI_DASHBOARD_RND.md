# 📊 DOKUMENTASI RESMI DASHBOARD WEB
### R&D Management System — PT. Herbatech Innopharma Industry

---

> **Versi Dokumen:** 1.0  
> **Tanggal Dokumen:** 21 Juli 2026  
> **Penulis:** Herbatech Dev Team  
> **Repository:** `c:\laragon\www\rnd-management`  
> **Framework:** Laravel 11/12 (PHP 8.2+)

---

## 🎯 1. Identitas & Ringkasan Project

**R&D Management System (Dashboard Web)** adalah platform manajemen terpadu yang dirancang khusus untuk mendigitalisasi, mendokumentasikan, dan mengontrol alur formulasi dan pengujian produk herbal di **PT. Herbatech Innopharma Industry**.

### Informasi Utama Project
| Item | Detail |
|------|--------|
| **Nama Project** | **R&D Management System** |
| **Perusahaan** | PT. Herbatech Innopharma Industry |
| **Tanggal Mulai (First Commit)** | **2 Juli 2026** (10:01:28 WIB) |
| **Pembaruan Terakhir** | **13 Juli 2026** |
| **Status Project** | Production Ready / Active |
| **Tim Developer** | `Herbatech Dev`, `Antigravity` |

### Tujuan Utama Dashboard Web
- Menyajikan indikator kinerja utama (**KPI & Stat Cards**) pengajuan formulasi dan trial produk secara real-time.
- Memberikan visibilitas **Approval Pipeline** berjenjang (Staff $\rightarrow$ Operational Manager $\rightarrow$ General Manager).
- Menghadirkan umpan aktivitas (*Activity Feed*) real-time yang terintegrasi dengan audit log.
- Membantu manajemen dan staff memprioritaskan dokumen yang membutuhkan tindakan atau revisi.

---

## 🛠️ 2. Stack Teknologi Dashboard Web

### Backend Engine
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **PHP** | ^8.2 | Bahasa pemrograman utama |
| **Laravel** | ^11.x / 12.x | Backend Framework Monolith |
| **Spatie Laravel Permission** | ^6.x | Hak akses berbasis peran (RBAC) |
| **Spatie Activitylog** | ^4.x | Pelacakan aktivitas & audit trail real-time |
| **DomPDF** | ^3.x | Engine cetak dokumen PDF |

### Frontend UI & Dashboard
| Teknologi | Fungsi |
|-----------|--------|
| **Tailwind CSS** | Styling modern responsif & dark mode support |
| **Alpine.js** | Interaktivitas modal, toggle, & live calculator |
| **TomSelect** | Searchable dropdown selector untuk material & supplier |
| **Blade Engine** | Dynamic views & component layouting |

---

## 🖥️ 3. Fitur Utama & Modul Dashboard Web

### 📊 3.1 Kartu Statistik Real-Time (Stat Cards)
- **Total Formulasi RM Approved**: Jumlah formula bahan baku yang telah disetujui penuh oleh General Manager (GM).
- **Total Trial RM**: Jumlah pengujian sampel bahan baku yang telah dieksekusi.
- **Total Trial PM**: Jumlah pengujian bahan kemas bersama tim lintas departemen.
- **Pending Approvals Counter**: Kontekstual berbasis Role Pengguna:
  - **Operational Manager (OM)**: Menampilkan jumlah dokumen `Pending Tahap 1`.
  - **General Manager (GM)**: Menampilkan jumlah dokumen `Pending Tahap 2`.
  - **Staff R&D**: Menampilkan jumlah dokumen milik sendiri yang `Ditolak/Membutuhkan Revisi`.

### 📈 3.2 Pipeline Persetujuan Manajerial (Approval Pipeline)
Visualisasi langsung status dokumen formulasi dalam pipa persetujuan:
```
Draft ──► Pending Tahap 1 (OM) ──► Pending Tahap 2 (GM) ──► Approved
```

### ⏱️ 3.3 Dynamic Activity Feed (Aktivitas Terakhir)
- Mengintegrasikan **Spatie Activitylog** untuk merekam setiap aktivitas pembuatan, pembaruan, dan persetujuan di 3 pilar:
  1. **Formulasi RM** (`Formula`)
  2. **Trial RM** (`TrialRm`)
  3. **Trial PM** (`TrialPm`)
- Menyediakan shortcut langsung ke halaman detail dokumen, nama pemrakarsa (causer), badge status, dan waktu kejadian.

### 👤 3.4 My Items Widget (Staff R&D View)
- Widget khusus untuk peran Staff R&D yang menampilkan 5 item draf/formulasi terbaru buatan sendiri untuk akses cepat.

### ⚙️ 3.5 Dynamic Branding & Settings
- Konfigurasi logo utama, favicon, nama aplikasi, dan logo cetak yang dapat diperbarui secara dinamis oleh Superadmin melalui menu Pengaturan.

---

## 📜 4. Kronologi Perubahan Dashboard & Sistem (Awal s/d Terkini)

```
[2 Jul 2026] ──► [3 Jul 2026] ──► [6 Jul 2026] ──► [7-8 Jul 2026] ──► [9-13 Jul 2026]
 Baseline Launch    TomSelect & Code    Modal Print Preview   Print Watermark    UI Polish & Final
```

### Tabel Commit History

| Tanggal | Developer | Perubahan & Fitur Utama |
| :--- | :--- | :--- |
| **02 Jul 2026** | `Antigravity` | **Initial Launch**: Peluncuran awal R&D Management System (Checkpoints 1-10), Superadmin Settings, Master Data CRUD, & Dashboard awal. |
| **02 Jul 2026** | `Herbatech Dev` | Redesign Formulasi RM, paraf admin, dan layout cetak untuk Trial PM, Trial RM, & Formula. |
| **03 Jul 2026** | `Herbatech Dev` | Integrasi **TomSelect** untuk searchable dropdown material/supplier, penyesuaian label sediaan/aplikasi, dan otomatisasi nomor proposal Trial PM. |
| **03 Jul 2026** | `Antigravity` | Perbaikan toggle Alpine.js (`x-show`) pada penilaian Trial PM. |
| **06 Jul 2026** | `Herbatech Dev` | **Approval Workflow UI Inline**: Lencana premium, penanganan HTTPS hosting, override public path, dan **Log Book PM**. |
| **06 Jul 2026** | `Herbatech Dev` | Modal print preview inline menggunakan Alpine.js & iframe, serta pengeditan langsung dosis/satuan pada Formula. |
| **07 Jul 2026** | `Antigravity` | **Dynamic Logo Settings**: Pengaturan logo cetak khusus, background watermark `CONFIDENTIAL`, dan perbaikan margining PDF. |
| **08 Jul 2026** | `Antigravity` | Support multiple product pada Trial PM, seeder *idempotent*, dan pembukaan akses Approval Center bagi Superadmin. |
| **09 Jul 2026** | `Antigravity` | Perbaikan layout cetak (min-height, footer, page number removal). |
| **13 Jul 2026** | `Antigravity` | Cleaning up login page & finalisasi tampilan UI Dashboard. |

---

## 🚀 5. Cara Menjalankan Project

```bash
# 1. Masuk Direktori Project
cd c:\laragon\www\rnd-management

# 2. Install Dependensi & Build Asset
composer install
npm install && npm run build

# 3. Setup Konfigurasi (.env)
cp .env.example .env
php artisan key:generate

# 4. Migrasi & Seed Database
php artisan migrate --seed

# 5. Jalankan Local Server
php artisan serve
```

---

*Dokumen ini disusun dan diperbarui secara otomatis berdasarkan rekam jejak Git history dan arsitektur kode R&D Management System per 21 Juli 2026.*
