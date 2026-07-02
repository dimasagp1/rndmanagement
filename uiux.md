# UI/UX Design — R&D Management System
### PT Herbatech Innopharma Industry

> Stack UI: **Laravel Blade + Tailwind CSS + Alpine.js**
> Tema: *Research & Development produk herbal* — natural, clean, tenang, terpercaya.

---

## 1. Prinsip Desain

- **Clarity over decoration** — data teknis (parameter %, pH, viskositas) harus mudah dipindai, tidak tenggelam dalam ornamen.
- **Status selalu terlihat** — setiap record punya indikator status (badge warna) yang konsisten di semua modul.
- **Role-aware UI** — elemen aksi (tombol Create/Edit/Approve) hanya muncul sesuai hak akses role yang login.
- **Traceability terlihat** — histori & timeline approval selalu dapat diakses dari halaman detail.

## 2. Tema Visual & Design Tokens

### 2.1 Palet Warna (herbal — hijau daun & cokelat tanah)

```js
// tailwind.config.js
colors: {
  primary: {
    DEFAULT: '#2F6B3C', // hijau daun utama
    dark:    '#1F4A28',
    light:   '#4E8C5C',
  },
  secondary: {
    DEFAULT: '#8B5E3C', // cokelat herbal/tanah
    light:   '#B08968',
  },
  accent: '#C9A227',     // emas/kunyit — untuk highlight & badge penting
  surface: '#F7F5F0',    // krem lembut — background halaman
  ink: '#1F2A1F',        // teks utama, hijau gelap keabuan
}
```

### 2.2 Warna Status (semantik, konsisten di semua modul)

| Status | Warna | Tailwind |
|---|---|---|
| Draf | Abu-abu | `bg-gray-100 text-gray-700` |
| Pending / Menunggu Approval | Kuning | `bg-amber-100 text-amber-800` |
| Approved | Hijau | `bg-primary/10 text-primary` |
| Rejected | Merah | `bg-red-100 text-red-700` |
| Reformulasi | Oranye | `bg-orange-100 text-orange-700` |

### 2.3 Tipografi
- Heading: `Poppins` (semi-bold) — kesan modern-organik.
- Body/Data: `Inter` — keterbacaan tinggi untuk tabel angka.
- Ukuran dasar: `text-sm` untuk tabel data, `text-base` untuk form.

### 2.4 Ikon & Imagery
- Ikon set: `Heroicons` (outline untuk navigasi, solid untuk status).
- Motif dekoratif tipis daun/leaf pattern pada login page & empty state — subtle, tidak dominan.

---

## 3. Navbar Global

```
┌────────────────────────────────────────────────────────────────────────┐
│ 🌿 Herbatech R&D   │ Dashboard  Formulasi RM  Trial RM  Trial PM  ⋮    │
│                                            🔔3   👤 Ibu Lisa ▾         │
└────────────────────────────────────────────────────────────────────────┘
```

**Perilaku navbar per role:**

| Menu | Staff R&D | Operational Manager | General Manager |
|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ |
| Formulasi RM | ✅ (+ tombol Tambah) | ✅ (read) | ✅ (read) |
| Trial RM | ✅ (+ tombol Tambah) | ✅ (read + review) | ✅ (read + final approve) |
| Trial PM | ✅ (+ tombol Tambah) | ✅ (read + review) | ✅ (read + final approve) |
| Approval Center | ❌ | ✅ | ✅ |
| Notifikasi (🔔) | item butuh revisi | item butuh Approval Tahap 1 | item butuh Approval Tahap 2 |

- Sticky di atas (`sticky top-0 z-50`), background `bg-white/90 backdrop-blur`.
- Mobile: menu berubah menjadi hamburger + slide-over drawer (Alpine `x-data="{open:false}"`).

---

## 4. Struktur Halaman & Wireframe (deskriptif)

### 4.1 Dashboard
```
[Card] Total Formula Aktif   [Card] Trial Berjalan   [Card] Menunggu Approval
─────────────────────────────────────────────────────────────
Tabel: Aktivitas Terbaru (5 baris) — Modul | Kode | Status | Diupdate
```

### 4.2 Daftar Formulasi RM
- Header: search bar + filter (Status, Tahapan Pengembangan) + tombol `+ Formula Baru` (khusus Staff R&D).
- Tabel kolom: Kode Formula | Nama Produk | Tahapan | Status | PIC | Aksi.
- Baris tabel klik → detail.

### 4.3 Form/Detail Formulasi RM
```
┌─ Info Dasar ─────────────────────────────┐
│ Nama Produk        [__________]          │
│ Kode Formula        FRM-202607-001 (auto)│
│ Tahapan Pengembangan [Dropdown ▾]        │
└───────────────────────────────────────────┘
┌─ Komposisi Material ───────────────────────────────────┐
│ Material     Supplier    %        [+ Tambah baris]     │
│ Ekstrak Jahe  PT Alam    30%                             │
│ Madu Murni    CV Sehat   20%                             │
│ ...                                                       │
│ Total: 100% ✅  (live-validated, merah jika ≠100%)        │
└─────────────────────────────────────────────────────────┘
┌─ Timeline Approval ───────────────────────────────────┐
│ ● Staff R&D (submitted)                                │
│ ○ Operational Manager (Approval Tahap 1)                │
│ ○ General Manager (Approval Tahap 2)                     │
└─────────────────────────────────────────────────────────┘
```

### 4.4 Form Trial RM
```
Formula Trial: [Cari Kode Formula ▾] → auto-fill komposisi (read-only, abu-abu)
Identitas Sampel: TRM-202607-001-A (auto)

┌─ Tahapan Proses (rich text) ────────────┐
│ ... langkah pencampuran, suhu, waktu ... │
└────────────────────────────────────────────┘

┌─ Verifikasi Hasil ───────────────────────────────┐
│ Parameter     Target        Aktual      Selisih  │
│ Warna         Kuning muda   Kuning muda   ✅      │
│ pH            5.5–6.5       5.8           ✅      │
│ Viskositas    ...           ...           ⚠️      │
└────────────────────────────────────────────────────┘

Keputusan Trial:  ( ) Lulus   ( ) Reformulasi
```
> Jika "Reformulasi" dipilih → modal konfirmasi: *"Sistem akan membuat versi baru formula FRM-202607-001-V2. Lanjutkan?"*

### 4.5 Form Trial PM
```
Data Bahan Kemas: [Dropdown/Text]
Spesifikasi Fisik: [Textarea]
Parameter Pelaksanaan: Speed [__] Suhu [__] Tekanan [__]
Analisis Risiko: [Textarea]

┌─ Kesimpulan Kelaikan (4 Departemen) ─────────────┐
│ ☐ R&D (estetik/stabilitas)        [Catatan...]    │
│ ☐ QC (kualitas/uji kebocoran)     [Catatan...]    │
│ ☐ Produksi (efisiensi kecepatan)  [Catatan...]    │
│ ☐ Engineering (setting mesin)     [Catatan...]    │
└──────────────────────────────────────────────────┘
Status: Approved hanya aktif jika 4/4 checklist tercentang.
```

### 4.6 Approval Center (Operational Manager / General Manager)
```
Tab: [Formulasi RM] [Trial RM] [Trial PM]
Tabel antrian: Kode | Diajukan Oleh | Tanggal | Status | [Review] [Approve] [Reject]
```

---

## 5. Komponen UI Reusable (Blade Components)

| Komponen | Fungsi |
|---|---|
| `<x-status-badge :status="$status" />` | Badge warna sesuai tabel status semantik di atas |
| `<x-approval-timeline :steps="$steps" />` | Stepper visual 3 tahap approval |
| `<x-percentage-table :items="$materials" />` | Tabel komposisi dengan live-sum validasi 100% (Alpine.js) |
| `<x-comparison-table :target="$t" :actual="$a" />` | Tabel Target vs Aktual dengan highlight selisih |
| `<x-department-checklist :depts="$depts" />` | Checklist 4 departemen Trial PM |
| `<x-empty-state />` | Ilustrasi daun tipis + teks saat tabel kosong |

## 6. Responsiveness

- Breakpoint utama: `sm` (mobile), `lg` (desktop admin).
- Tabel data → di mobile berubah jadi stacked card (`hidden lg:table` + `lg:hidden card-list`).
- Form multi-kolom → 1 kolom di mobile (`grid-cols-1 lg:grid-cols-2`).

## 7. Aksesibilitas
- Kontras warna primary terhadap surface memenuhi WCAG AA.
- Semua form field punya `<label>` eksplisit.
- Status badge disertai teks (bukan warna saja) untuk color-blind friendliness.
