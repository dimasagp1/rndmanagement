# Prompt untuk Antigravity — Build R&D Management System

Salin seluruh isi di bawah ini ke Antigravity (pastikan `prd.md`, `uiux.md`, dan `rules.md` sudah berada di root/workspace project).

---

```
Kamu adalah AI software engineer yang akan membangun aplikasi web "R&D Management System"
untuk PT Herbatech Innopharma Industry.

LANGKAH PERTAMA — WAJIB SEBELUM CODING:
1. Baca dan pahami secara menyeluruh 3 file berikut di root project:
   - prd.md   → kebutuhan bisnis, aktor/role, spesifikasi fungsional tiap modul
   - uiux.md  → desain UI/UX, navbar, tema warna herbal, struktur halaman, komponen
   - rules.md → aturan bisnis, validasi, RBAC, approval gate, state machine status
2. Jangan mulai generate kode apa pun sebelum ketiga file itu selesai dibaca dan dipahami.
3. Buat ringkasan rencana implementasi (implementation plan) berdasarkan ketiga file
   tersebut, dan tampilkan ke saya untuk saya review SEBELUM kamu mulai build.

TECH STACK (ikuti persis):
- Backend : Laravel versi terbaru (PHP 8.3+), gunakan `laravel new` dengan Blade starter kit
- Frontend: Blade Templates + Tailwind CSS v4 (bawaan Laravel 12, tanpa tailwind.config.js,
  kustomisasi tema lewat @theme di resources/css/app.css)
- Interaktivitas ringan: Alpine.js
- RBAC   : spatie/laravel-permission
- Audit trail: spatie/laravel-activitylog
- Export : maatwebsite/excel + barryvdh/laravel-dompdf
- Database: MySQL

ATURAN KERJA:
- Ikuti struktur modul, alur, dan role-permission PERSIS seperti di prd.md dan rules.md —
  jangan menambah/mengurangi fitur di luar dokumen tanpa konfirmasi ke saya.
- Semua validasi bisnis (misal: total persentase komposisi = 100%, approval gate berjenjang,
  validasi kolektif 4 departemen di Trial PM) WAJIB diimplementasikan di layer backend
  (Form Request / Policy / Service), bukan hanya validasi frontend.
- Gunakan palet warna, navbar, dan komponen UI PERSIS seperti spesifikasi di uiux.md
  (warna primary hijau daun, secondary cokelat herbal, accent emas/kunyit).
- Buat migration, model, relasi, factory, dan seeder untuk 3 modul utama:
  Formulasi RM, Catatan Trial RM, Catatan Trial PM — sesuai skema kolom di prd.md.
- Implementasikan auto-generate kode (FRM-YYYYMM-XXX, TRM-YYYYMM-XXX-A) dan versioning
  formula (FRM-YYYYMM-XXX-V2) sesuai rules.md.
- Buat Policy per modul untuk RBAC 3 role: Staff R&D, Operational Manager, General Manager.

URUTAN PROSES BUILD (kerjakan bertahap, tunggu konfirmasi saya di tiap checkpoint):
1. Setup project Laravel + Tailwind v4 + dependency (spatie/permission, activitylog, dll)
2. Migration + Model + relasi untuk: Users, Roles, Formula (+versioning), TrialRm, TrialPm,
   Material, Supplier
3. Seeder: role & permission, user dummy per role (Staff R&D, Operational Manager, General Manager)
4. Auth (login) + middleware role-based routing
5. Layout utama: navbar sesuai uiux.md (role-aware menu) + tema warna herbal
6. Modul Formulasi RM: CRUD + validasi total 100% + approval gate
7. Modul Catatan Trial RM: lookup formula read-only + tabel Target vs Aktual + logic reformulasi/clone
8. Modul Catatan Trial PM: checklist 4 departemen + validasi kolektif approval
9. Approval Center (Operational Manager & General Manager)
10. Dashboard ringkasan + notifikasi pending approval
11. Audit trail / activity log di setiap perubahan status
12. Testing dasar (feature test untuk aturan bisnis kritis di rules.md, minimal: validasi 100%,
    approval gate, validasi kolektif Trial PM)

OUTPUT YANG DIHARAPKAN SETIAP CHECKPOINT:
- Ringkasan file yang dibuat/diubah
- Command yang perlu saya jalankan (migrate, seed, npm run dev, dll)
- Hal yang perlu saya cek/tes secara manual sebelum lanjut ke tahap berikutnya

Jika ada bagian dari prd.md, uiux.md, atau rules.md yang ambigu atau bertentangan,
STOP dan tanyakan ke saya dulu — jangan berasumsi sendiri.
```

---

### Cara pakai
1. Letakkan `prd.md`, `uiux.md`, `rules.md` di root folder project (atau folder `docs/`).
2. Buka project tersebut di Antigravity.
3. Tempel blok prompt di atas (di dalam ``` ```) sebagai instruksi awal.
4. Review implementation plan yang dihasilkan Antigravity sebelum menyetujui lanjut ke tahap coding.
