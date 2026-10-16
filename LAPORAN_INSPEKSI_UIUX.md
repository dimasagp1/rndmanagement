# Laporan Inspeksi UI/UX — R&D Management

- **Tanggal:** 16 Oktober 2026
- **Versi yang diperiksa:** branch `main`, commit `a009944` (setelah perbaikan Tahap 1–5)
- **Status:** hanya laporan. **Belum ada perubahan kode.**

## Cara pemeriksaan

1. **Aplikasi dijalankan langsung.** Saya memakai database demo SQLite dari seeder project (bukan data produksi) dan Vite dev server, supaya tampilan sesuai kode terbaru.
2. **Penelusuran di browser dengan 3 role:** Staff R&D, Operational Manager, dan Superadmin.
   - Ukuran layar laptop (1366×768) dan ponsel (375×812).
   - 56 halaman utama dicek status muatnya.
3. **Pemindaian statis semua view Blade:** teks, atribut, pola escape, dan kelengkapan menu.

**Hasil umum:** semua halaman memuat tanpa error 500 dan tidak ada error JavaScript di console. Tampilan secara umum rapi dan konsisten (warna, kartu, badge, empty state). Masalah utamanya ada pada **konsistensi bahasa dan istilah**, beberapa **bug tampilan kecil**, dan **tabel lebar** di layar sedang atau kecil.

| Kategori | Jumlah |
|---|---|
| 🔴 Bug tampilan / perilaku salah | 6 |
| 🟠 Bahasa & istilah tidak konsisten | 6 |
| 🟡 Tata letak & keterbacaan | 8 |
| 🟡 Kejelasan form & interaksi | 7 |

---

## 1. 🔴 Bug tampilan / perilaku salah

### UI-01. Teks "&amp;amp;" tampil mentah di halaman Design Approval
Saat daftar kosong, teksnya tampil **"Final approval formula &amp;amp; artwork…"**, bukan "&".
- **Penyebab:** [approval-formula-designs/index.blade.php:73](resources/views/approval-formula-designs/index.blade.php:73) mengirim teks ke komponen lewat `description="{{ ... }}"`, sehingga di-escape dua kali.
- **Pola yang sama** ada di 7 halaman lain: formulas, formula-approvals, sample-evaluations, trial-pms, trial-rms, products, dan approval-formula-designs (judul). Masalahnya akan muncul begitu teks mengandung `&`, `<`, atau tanda kutip.
- **Saran:** ganti menjadi `:description="..."`.

### UI-02. Staff Packdev tampil sebagai "Staff R&D" di Manajemen User
Akun Emir (Staff Packdev) mendapat badge **"🔬 Staff R&D"**. Penyebabnya, [users/index.blade.php:62-70](resources/views/users/index.blade.php:62) memakai `@else` untuk semua role selain Superadmin, GM, dan OM.

### UI-03. Filter status "Pending" di Dashboard tidak lengkap
Kartu ringkasan menulis **Pending 3**, tetapi filter "Pending" hanya memunculkan **1 item** (sudah diuji). Penyebabnya, [TimelineController.php:95](app/Http/Controllers/TimelineController.php:95) mencocokkan status secara persis, sehingga "Pending Tahap 1", "Pending Tahap 2", "Pending Review", dan "Pending Approval" tidak ikut.

### UI-04. Modul Preformulation Study tidak ada di menu
Halaman `/preformulation-studies` berfungsi, tetapi **tidak ada di sidebar**. Daftar menu di [GeneralController](app/Http/Controllers/GeneralController.php) dan [layouts/app.blade.php:100](resources/views/layouts/app.blade.php:100) hanya memuat "QbD".
- Pengguna hanya bisa sampai lewat URL, Dashboard, atau Approval Center.
- Saat halaman itu dibuka, tidak ada item menu yang ditandai aktif.

### UI-05. Tombol "Setujui" di Approval Center langsung jalan tanpa konfirmasi
Satu klik langsung menyetujui dokumen dan tidak bisa dibatalkan ([approval-center/index.blade.php:118](resources/views/approval-center/index.blade.php:118) dan 4 tombol lain). Sebaliknya, "Tolak" meminta catatan. Risiko salah klik cukup tinggi, terutama di layar sentuh.

### UI-06. Build aset tampilan sudah usang
`public/build` bertanggal **21 Sep 2026**, sedangkan view terakhir diubah 15 Okt 2026. Kalau server di-deploy tanpa `npm run build`, class Tailwind yang ditambahkan setelah 21 Sep tidak akan punya CSS dan tampilannya bisa rusak.

---

## 2. 🟠 Bahasa & istilah tidak konsisten

### UI-07. Teks sistem masih berbahasa Inggris
`APP_LOCALE=en` dan project belum punya folder terjemahan (`lang/`). Akibatnya:
- **Login gagal:** "These credentials do not match our records."
- **Semua pesan validasi:** misalnya "The kategori field is required."
- **Waktu relatif** di Dashboard, Formula, dan Approval Center: "2 hours ago", "1 week ago".
- **Halaman error 403, 404, 419, dan 500** memakai bawaan Laravel: berbahasa Inggris, tanpa logo, dan tanpa tombol kembali. Contohnya, Staff yang membuka Approval Center lewat URL hanya melihat "403 Forbidden".
- **Komponen form:** "Select an option" (pilihan bahan/supplier) dan "Choose File / No file chosen".

### UI-08. Label dalam satu halaman bercampur Indonesia dan Inggris
- **Buat Formula:** "INFORMATION", "Product Name", "Date", "Type (choose 1)", "Material", "Note", dan "Result" berdampingan dengan "Cara Penyajian" dan "Buat Formula Baru".
- **Regulatory Dossier dan Commercial Production seluruhnya berbahasa Inggris:** "Files", "Activities", "Overviews", "Folders", "This folder is empty", "New Folder", "Add File", "Sort by: Name".
- **Sidebar:** "Category" dan "Data Master" berdampingan dengan "Nama Produk", "Akses Kontrol", dan "Pengaturan Sistem".

### UI-09. Label status bercampur bahasa
[status-badge.blade.php](resources/views/components/status-badge.blade.php) menampilkan **"Draf"** dan **"Ditolak"** (Indonesia) di samping **"Approved"**, **"Pending"**, dan **"Completed"** (Inggris). Di Log Book PM, kartu ringkasan berlabel "Lulus/Tidak Lulus", sedangkan statusnya "Passed/Rejected".

### UI-10. Satu modul punya beberapa nama
| Di menu | Di tempat lain |
|---|---|
| Trial Formulation | "Trial RM" (filter dan Quick Actions Dashboard), "Catatan Trial RM" (tab Approval Center), "Belum Ada Trial RM" (empty state) |
| Formula RM | "Formulation Development" (judul halaman) |
| Design Approval | "Belum Ada Final Approval" (empty state) |
| Akses Kontrol | "Manajemen User & Akses" (judul halaman) |
| Operational Manager | "OP Manager", "Manager" (badge user) |

### UI-11. Status lama masih tampil
- **Formula Approval** masih menampilkan status **"Approval by OM"**, padahal tahap OM sudah dihapus.
- **Tahapan Formula** di data lama memakai "Draf, Pra-Trial, Optimalisasi, Final". Form sekarang memakai "Product Form, Laboratory Trial, Sensory Test, Plant Trial, Market Test". Akibatnya, daftar Formula memakai dua kosakata sekaligus.

### UI-12. Label Dashboard menyesatkan
Untuk Staff, judul Dashboard bertuliskan **"Item saya & pipeline NPD"**, tetapi yang ditampilkan adalah **semua** item dari semua orang. Datanya tidak difilter per pengguna.

---

## 3. 🟡 Tata letak & keterbacaan

| # | Lokasi | Masalah |
|---|---|---|
| UI-13 | Buat/Edit Formula, tabel bahan | Di layar 1366px, kolom **Persentase** (inti form) tersembunyi di balik scroll horizontal |
| UI-14 | Detail Formula, Komposisi Material | Kolom kanan ("30 Sachet") terpotong |
| UI-15 | Log Book PM, daftar kosong | Pesan "Belum ada entri Log Book PM. Tambah sekarang" terpotong di kanan, karena ada di dalam tabel yang lebar |
| UI-16 | Daftar Formula & NPD Proposal | Kode dokumen pecah menjadi 3 baris ("FRM-" / "202607-" / "003"). Di NPD, nama produk juga diulang di bawah kode |
| UI-17 | Daftar PRF | Tautan "Detail →" pecah: panahnya turun ke baris kedua |
| UI-18 | Semua halaman | Judul tab browser selalu "Herbatech R&D" (kecuali halaman cetak), jadi tab dan riwayat sulit dibedakan. Layout sudah mendukung `$title`, tetapi tidak pernah diisi |
| UI-19 | PRF, NPD Proposal, Preformulation Study | Breadcrumb berbeda format: "PRF / Product Request Form", tanpa "Dashboard" dan dengan pemisah "/". Halaman lain: "Dashboard › Modul" |
| UI-20 | Tampilan ponsel | Dashboard menampilkan 8 kartu produk lebih dulu (beberapa layar scroll) sebelum ringkasan dan antrean. Breadcrumb 3 tingkat berdesakan di top bar. Tabel hanya memperlihatkan 3–4 kolom pertama, sehingga **status tidak terlihat** tanpa scroll ke samping |

---

## 4. 🟡 Kejelasan form & interaksi

| # | Lokasi | Masalah |
|---|---|---|
| UI-21 | Buat Formula | Subjudul berbunyi "Kode akan di-generate otomatis setelah disimpan" ([formulas/create.blade.php:15](resources/views/formulas/create.blade.php:15)), tetapi field Kode terisi dan bisa diedit |
| UI-22 | Buat Formula | Field wajib tanpa tanda `*`: **Supplier** (wajib sejak Tahap 1) dan **Product Development Stage** (wajib kecuali tipe Existing) |
| UI-23 | Detail PRF | Teks "wajib minimal 1 file untuk mengajukan PRF" ([prfs/show.blade.php:118](resources/views/prfs/show.blade.php:118)) muncul, padahal PRF tidak lagi punya alur pengajuan. Kartu samping juga tetap menulis "Siap digunakan untuk NPD Proposal" walau belum ada file. Dua pesan ini bertentangan |
| UI-24 | Approval Center (OM) | Tab "Formula Approval (0)" selalu tampil untuk OM, padahal tidak akan pernah berisi. Panel "Aturan Approval" ([baris 424](resources/views/approval-center/index.blade.php:424)) menyatakan semua dokumen melewati OM lalu GM. Kenyataannya, Trial PM hanya OM dan Formula Approval hanya GM |
| UI-25 | Seluruh aplikasi | Modal konfirmasi kustom membaca teks pesan dari atribut `onclick`/`onsubmit` dengan regex. Pesan yang mengandung tanda kutip (misalnya nama produk dengan apostrof) akan terpotong atau jatuh ke teks bawaan. *(Koreksi: semula ditulis sebagian dialog memakai `confirm()` bawaan browser. Ternyata keduanya sudah ditangani modal kustom.)* |
| UI-26 | Dashboard | Nama produk panjang di kartu terpotong ("Jahe Merah Han…", "Lidah Buaya Gel Her…") tanpa cara melihat nama lengkapnya |
| UI-27 | Aksesibilitas | Gambar logo (sidebar, login, cetak, maintenance) dan pratinjau lampiran tidak punya atribut `alt` |

---

## 5. Hal yang sudah baik

- Tampilan visual konsisten: palet hijau, kartu, badge status berwarna, dan ikon yang seragam.
- Halaman login rapi dan profesional.
- Sebagian besar halaman punya empty state yang informatif.
- Sidebar di ponsel bisa dibuka dan ditutup dengan baik, dan menu tersembunyi sesuai hak akses.
- Flash message sukses/gagal jelas dan bisa ditutup.
- Tidak ada error JavaScript, dan semua halaman yang diuji memuat normal.

## 6. Temuan sampingan (non-UI)

- `TimelineSeeder` gagal dijalankan dengan error "Undefined array key 2" di [TimelineSeeder.php:72](database/seeders/TimelineSeeder.php:72). Seeder ini tidak dipanggil `DatabaseSeeder`, jadi hanya berdampak saat dijalankan manual.

---

## 7. Usulan urutan perbaikan (belum dikerjakan)

1. **Bug cepat:** UI-01 sampai UI-05. Masing-masing perubahan kecil.
2. **Deploy:** jalankan `npm run build` di setiap rilis (UI-06).
3. **Lokalisasi (UI-07):**
   - set `APP_LOCALE=id`;
   - tambahkan terjemahan Indonesia untuk validasi, autentikasi, dan paginasi;
   - atur waktu relatif ke bahasa Indonesia;
   - buat halaman error 403/404/419/500 dengan tampilan aplikasi.
4. **Penyeragaman istilah dan bahasa (UI-08 sampai UI-12):** perlu **keputusan pengguna** dulu. Lihat bagian 8.
5. **Tata letak:** tabel responsif, kode dokumen tidak terpotong, judul tab per halaman, dan breadcrumb seragam (UI-13 sampai UI-20).
6. **Kejelasan form:** UI-21 sampai UI-27.

## 8. Keputusan yang dibutuhkan dari pengguna

| Pertanyaan | Usulan |
|---|---|
| Bahasa antarmuka: Indonesia penuh, atau Indonesia dengan istilah teknis tetap Inggris (Approved, Pending, Trial, Formula Approval)? | Indonesia untuk teks dan label, istilah teknis yang sudah lazim di tim boleh tetap |
| Nama resmi modul trial bahan baku: "Trial RM" atau "Trial Formulation"? | [Ditentukan pengguna] |
| Status di badge: "Draf/Ditolak" atau "Draft/Rejected"? | Pilih satu bahasa, berlaku untuk semua |
| Data Formula lama dengan tahapan "Pra-Trial/Optimalisasi": dikonversi ke tahapan baru atau dibiarkan? | [Ditentukan pengguna] |
| Preformulation Study ditaruh di menu mana? | Di grup "Development & Regulatory", sebelum QbD |
