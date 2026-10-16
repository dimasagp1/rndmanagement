# Laporan Inspeksi Kode — R&D Management

- **Tanggal:** 15 Oktober 2026
- **Branch:** `amar` (commit `bfa9e5e`)
- **Cakupan:** `app/`, `routes/`, `database/migrations`, `database/seeders`, `resources/views`, `tests/`
- **Status:** hanya laporan. Belum ada kode project yang diubah.

---

## 1. Ringkasan

| Pemeriksaan otomatis | Hasil |
|---|---|
| `php -l` (lint sintaks) semua file PHP di app, routes, database, config | ✅ Tidak ada syntax error |
| `php artisan route:list` | ✅ 241 route terdaftar, tetapi **5 route mengarah ke method yang tidak ada** (lihat K-02) |
| `php artisan view:cache` (kompilasi semua Blade) | ❌ **Gagal**: komponen `x-commercial-productions.partials.tree-node` tidak ditemukan (lihat S-07) |
| `php artisan test` (apa adanya) | ❌ **95 dari 97 test error.** Penyebabnya satu migration yang hanya jalan di MySQL (lihat S-06) |
| `php artisan test` (migration itu dilewati sementara di SQLite) | 69 lulus, 12 gagal, 16 error. Sebagian besar karena test sudah usang (lihat bagian 6) |
| Cek relasi Eloquent di `with()` / `load()` lewat script | ❌ 7 relasi tidak ada di model `PreformulationStudy` (lihat K-01) |

**Jumlah temuan menurut tingkat keparahan**

| Tingkat | Jumlah | Arti |
|---|---|---|
| 🔴 Kritis / Tinggi | 8 | Error 500, celah otorisasi, atau data hilang. Semuanya **sudah dibuktikan dengan test runtime** |
| 🟠 Sedang | 12 | Bug logika atau alur kerja yang buntu |
| 🟡 Rendah | 9 | Potensi error, file yatim (orphan), atau masalah performa |
| 🔵 Ambigu / Code smell | 10 | Aturan yang tidak konsisten, kode mati, atau kode duplikat |

> Temuan bertanda **✅ Terverifikasi** sudah dibuktikan dengan test sementara di SQLite in-memory. File test berada di luar project dan tidak di-commit. Hasilnya ditulis di setiap temuan.

---

## 2. 🔴 Kritis / Tinggi

### K-01. Halaman detail Preformulation Study selalu error 500 ✅ Terverifikasi
**File:** [app/Http/Controllers/PreformulationStudyController.php:101-114](app/Http/Controllers/PreformulationStudyController.php:101)

```php
$preformulationStudy->load([
    'creator', 'npdProposal', 'documents', 'approvedByOm', 'approvedByGm',
    'qtpp.attributes',   // ❌ relasi tidak ada
    'cqas',              // ❌
    'cmas',              // ❌
    'cpps',              // ❌
    'riskAssessments',   // ❌
    'designSpaces',      // ❌
    'controlStrategies', // ❌
]);
```
Model `App\Models\PreformulationStudy` hanya punya relasi `npdProposal`, `product`, `creator`, `approvedByOm`, `approvedByGm`, dan `documents`. Ketujuh relasi QbD di atas juga tidak dipakai di view `preformulation-studies/show.blade.php`. Relasi ini peninggalan commit `fc3937c`.

**Hasil test:** `GET /preformulation-studies/{id}` mengembalikan **500** dengan `RelationNotFoundException: Call to undefined relationship [qtpp]`.

**Perbaikan:** hapus 7 baris relasi tersebut dari `load()`.

---

### K-02. Lima route `show` mengarah ke method yang tidak ada (error 500) ✅ Terverifikasi
**File:** [routes/web.php:446](routes/web.php:446), [routes/web.php:454-457](routes/web.php:454)

```php
Route::resource('users', UserController::class)->middleware('role:Superadmin');
Route::resource('materials', MaterialController::class)->...;
Route::resource('suppliers', SupplierController::class)->...;
Route::resource('product-categories', ProductCategoryController::class)->...;
Route::resource('products', ProductController::class)->...;
```
`Route::resource` mendaftarkan `GET /{id}` → `show()`, tetapi kelima controller ini tidak punya method `show()`.

**Hasil test:** `GET /users/1` dan `GET /materials/1` mengembalikan **500** (`Error: Call to undefined method`).

**Perbaikan:** tambahkan `->except('show')` pada kelima resource tersebut.

---

### K-03. Menyimpan formula dengan baris material tanpa supplier → error 500 ✅ Terverifikasi
**File:**
- [database/migrations/2026_07_01_092840_create_formula_materials_table.php:18](database/migrations/2026_07_01_092840_create_formula_materials_table.php:18): `supplier_id` dibuat **NOT NULL**
- [app/Http/Controllers/FormulaController.php:108](app/Http/Controllers/FormulaController.php:108): validasinya `'materials.*.supplier_id' => 'nullable|...'`
- [app/Services/FormulaService.php:261](app/Services/FormulaService.php:261): `'supplier_id' => $mat['supplier_id'] ?: null`
- Form `formulas/create.blade.php:177` punya pilihan kosong `-- Pilih Supplier --`

Aturan validasi dan form sama-sama membolehkan supplier kosong, tetapi database menolaknya.

**Hasil test:** POST formula dengan supplier kosong mengembalikan **500** dengan `Integrity constraint violation: NOT NULL constraint failed: formula_materials.supplier_id`.

Ada masalah tambahan: `$mat['supplier_id'] ?: null` akan memunculkan *Undefined array key* jika key `supplier_id` tidak dikirim. Gunakan `?? null`.

**Perbaikan:** pilih salah satu.
- Buat migration baru yang menjadikan `supplier_id` nullable, dengan `nullOnDelete`, **atau**
- Ubah validasi menjadi `required`.

---

### K-04. Staff dapat menyetujui sendiri Log Book PM, melewati Operational Manager ✅ Terverifikasi
**File:** [app/Http/Controllers/LogbookPmController.php:309](app/Http/Controllers/LogbookPmController.php:309)

```php
private function validateEntry(Request $request, ?int $ignoreId = null): array
{
    return $request->validate([
        ...
        'om_approval' => 'nullable|in:Pending,Approved,Rejected',   // ❌ field milik OM
        ...
    ]);
}
```
Hasil `validateEntry()` langsung dipakai di `LogbookPm::create($validated)` dan `update($validated)`. Kolom `om_approval` termasuk `$fillable`.

**Hasil test:** Staff R&D mengirim `om_approval=Approved` saat membuat entri, dan data tersimpan dengan `om_approval = Approved`. Alur `approve()` milik OM pun terlewati.

**Perbaikan:** hapus `om_approval` dari `validateEntry()`. Field ini cukup diubah lewat method `approve()`.

---

### K-05. Menghapus user ikut menghapus semua data yang pernah ia buat ✅ Terverifikasi
**File:** Foreign key `created_by ... ->onDelete('cascade')` ada di 7 migration:
`create_formulas_table`, `create_trial_rms_table`, `create_trial_pms_table`, `create_logbook_pms_table`, `create_prfs_table`, `create_npd_proposals_table`, `create_sample_evaluations_table`.
Eksekusinya ada di [app/Http/Controllers/UserController.php:114](app/Http/Controllers/UserController.php:114) (`$user->delete()`).

**Hasil test:** user yang punya 1 formula **Approved** dihapus. Jumlah formula berubah dari 1 menjadi **0**. Jejak audit R&D ikut hilang permanen. Untuk modul farmasi/regulasi, ini fatal.

Ada juga inkonsistensi. `preformulation_studies.created_by` ([migration:27](database/migrations/2026_08_15_090000_create_preformulation_studies_table.php:27)) tidak punya `onDelete`, jadi di MySQL defaultnya RESTRICT. Akibatnya, menghapus user yang pernah membuat Preformulation Study akan **gagal dengan error 500**.

**Perbaikan:**
- Jangan hapus user secara fisik. Nonaktifkan saja (SoftDeletes atau kolom `is_active`), **atau**
- Ubah FK `created_by` menjadi `restrict` / `nullOnDelete`, lalu tampilkan pesan yang jelas ke Superadmin.

---

### K-06. Menghapus Material atau Supplier mengubah komposisi formula yang sudah Approved
**File:** [database/migrations/2026_07_01_092840_create_formula_materials_table.php:17-18](database/migrations/2026_07_01_092840_create_formula_materials_table.php:17)

```php
$table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
$table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
```
[MaterialController::destroy](app/Http/Controllers/MaterialController.php:135) dan `SupplierController::destroy` tidak memeriksa apakah data masih dipakai. Siapa pun dengan role Staff R&D (menu Data Master) bisa menghapus bahan baku. Baris bahan itu lalu **hilang diam-diam dari semua formula**, termasuk yang sudah disetujui GM. Total persen formula Approved bisa berubah tanpa jejak.

**Perbaikan:** ubah FK menjadi `restrictOnDelete()`. Di controller, cek `formulaMaterials()->exists()` sebelum menghapus, atau pakai soft delete untuk data master.

---

### K-07. Registrasi publik terbuka, dan user tanpa role bisa melihat seluruh dashboard ✅ Terverifikasi
**File:**
- [routes/auth.php:15-18](routes/auth.php:15): `GET/POST /register` terbuka untuk tamu
- [app/Models/User.php:5](app/Models/User.php:5): `MustVerifyEmail` dikomentari, sehingga middleware `verified` tidak berpengaruh
- [app/Http/Controllers/TimelineController.php:56-117](app/Http/Controllers/TimelineController.php:56): tidak ada pengecekan role atau permission

**Hasil test:** user yang mendaftar sendiri (`roles = []`) mendapat **200** di `/timeline`. Halaman itu menampilkan semua produk, item dari 12 modul, activity log, dan workload per orang. Karena `!$isStaff`, user tanpa role diperlakukan seperti manajer.

**Perbaikan:** matikan route register (akun dibuat Superadmin lewat menu User), atau beri default role / permission check di Timeline.

---

### K-08. Approval Center: OM bisa menolak tahap GM, dan GM bisa menolak tahap OM
**File:** [app/Http/Controllers/ApprovalCenterController.php:151](app/Http/Controllers/ApprovalCenterController.php:151) (`rejectFormula`) dan [:195](app/Http/Controllers/ApprovalCenterController.php:195) (`rejectTrialRm`)

```php
public function rejectFormula(Request $request, Formula $formula)
{
    $request->validate([...]);
    $this->formulaService->reject($formula, $request->rejection_notes); // ❌ tanpa cek role vs tahap
```
`FormulaService::reject()` dan `TrialRmService::reject()` hanya memeriksa status `Pending Tahap 1/2`, bukan siapa yang menolak. Sebaliknya, `rejectPreformulationStudy` ([:289-294](app/Http/Controllers/ApprovalCenterController.php:289)) sudah memakai `match` role ↔ tahap dengan benar.

**Perbaikan:** terapkan pola `$canReject = match(true) {...}` yang sama pada `rejectFormula` dan `rejectTrialRm`.

---

## 3. 🟠 Sedang

### S-01. Trial RM berstatus Rejected tidak bisa direvisi ✅ Terverifikasi
- [app/Policies/TrialRmPolicy.php:28](app/Policies/TrialRmPolicy.php:28) mengizinkan edit untuk status `['Draft', 'Rejected']`.
- [app/Services/TrialRmService.php:93](app/Services/TrialRmService.php:93) menolak update jika status bukan `Draft`.
- Baris 108 di file yang sama (`if Draft || Rejected`) tidak pernah tercapai untuk status Rejected.

**Hasil test:** halaman edit terbuka (200), tetapi saat disimpan data tidak berubah karena validasi service gagal. User tidak bisa memperbaiki trial yang ditolak.

**Perbaikan:** ubah baris 93 menjadi `! in_array($trial->approval_status, ['Draft','Rejected'])`.

### S-02. Trial PM: produk yang dipilih di form tidak tersimpan ✅ Terverifikasi
`trial-pms/create.blade.php:57` punya `<select name="product_id" required>`, tetapi `product_id` tidak ada di aturan validasi [TrialPmController::store:70-101](app/Http/Controllers/TrialPmController.php:70). Akibatnya `TrialPmService::create` selalu menerima `null`.

**Hasil test:** produk dengan id 1 dikirim, tetapi yang tersimpan `product_id = NULL`. Trial PM tidak pernah terhubung ke produk di dashboard Timeline.

**Perbaikan:** tambahkan `'product_id' => 'required|exists:products,id'` di `store` (dan di `update` bila perlu).

### S-03. Trial PM yang Rejected jadi buntu (tidak bisa diedit atau diajukan ulang)
- [TrialPmPolicy::edit/submit](app/Policies/TrialPmPolicy.php:25) hanya mengizinkan status `Draft`.
- [TrialPmService::update:99](app/Services/TrialPmService.php:99) dan `submitForReview:181` juga hanya menerima `Draft`.
- Padahal `approveDepartment()` dan `rejectOM()` bisa mengubah status menjadi `Rejected`. Setelah itu tidak ada jalan kembali. Modul Formula dan Trial RM mengizinkan revisi dari Rejected, jadi perilakunya tidak konsisten.

### S-04. Status Log Book PM sudah diganti, tetapi sebagian kode masih memakai nilai lama
Migration [2026_09_01_200000](database/migrations/2026_09_01_200000_update_status_pengujian_enum_in_logbook_pms.php) mengganti nilai `Proses / Lulus / Tidak Lulus` menjadi `On Going Trial / Passed / Rejected`. Yang belum ikut diubah:
- [resources/views/logbook-pm/index.blade.php:92](resources/views/logbook-pm/index.blade.php:92): dropdown filter masih `['Pending','Proses','Lulus','Tidak Lulus']`, sehingga memilih filter selain Pending **selalu menghasilkan 0 data**.
- [app/Models/LogbookPm.php:84-91](app/Models/LogbookPm.php:84): `getStatusBadgeClassAttribute()` masih mencocokkan nilai lama.
- [app/Http/Controllers/TimelineController.php:98](app/Http/Controllers/TimelineController.php:98) masih memakai `'Lulus'`.

### S-05. Formula Design tidak pernah muncul di Approval Center, tetapi ikut dihitung di badge notifikasi
- [ApprovalCenterController.php:66-70, 114-118](app/Http/Controllers/ApprovalCenterController.php:66) memfilter `->where('source', 'formula-approval')`.
- [AppServiceProvider.php:99, 110](app/Providers/AppServiceProvider.php:99) menghitung `FormulaApprovalForm` dari **semua source**, termasuk `approval-formula-design`.
- [TimelineController.php:407](app/Http/Controllers/TimelineController.php:407) juga tanpa filter source.

Akibatnya GM melihat angka badge yang tidak cocok dengan isi antrean, dan dokumen dari menu "Approval Formula & Design" hanya bisa disetujui dari halaman modul itu sendiri.

### S-06. Migration khusus MySQL membuat test suite rusak total
[database/migrations/2026_09_01_200000_update_status_pengujian_enum_in_logbook_pms.php:10-23](database/migrations/2026_09_01_200000_update_status_pengujian_enum_in_logbook_pms.php:10) memakai `ALTER TABLE ... MODIFY COLUMN` tanpa guard driver. Migration lain sudah memakai `if (DB::connection()->getDriverName() === 'mysql')`. Dampaknya: **95/97 test error** di `phpunit.xml` (SQLite `:memory:`).

**Perbaikan:** bungkus kedua `ALTER` dengan pengecekan driver. Statement `UPDATE` tetap dijalankan di semua driver.

### S-07. `php artisan view:cache` / `optimize` gagal karena komponen Blade tidak valid
[resources/views/commercial-productions/partials/tree-node.blade.php:13](resources/views/commercial-productions/partials/tree-node.blade.php:13)

```blade
<x-commercial-productions.partials.tree-node :node="$child" :depth="$depth + 1"/>
```
Tag `<x-...>` hanya mencari di `resources/views/components/`, jadi kompilasi gagal: *Unable to locate a class or view for component*. File ini juga **tidak dipakai di mana pun** (kode mati). Masalahnya, ia menggagalkan `view:cache` saat deploy production. Class Tailwind dinamis `ml-{{ $depth*4 }}` di baris 3 juga tidak akan ter-generate oleh JIT.

**Perbaikan:** hapus file tersebut, atau ganti dengan `@include` seperti versi regulatory-dossier.

### S-08. Mengganti gambar paraf di Settings merusak paraf pada Trial PM lama
- [SettingController.php:99-108](app/Http/Controllers/SettingController.php:99) menghapus file paraf lama dari storage saat gambar baru diunggah.
- [TrialPmService.php:166](app/Services/TrialPmService.php:166) menyimpan **path** paraf ke JSON `executions` setiap Trial PM.

Akibatnya, setelah paraf diganti, semua Trial PM lama menampilkan gambar rusak di halaman show/print. Masalah yang sama berlaku untuk logo cetak (`print_logo`).

**Perbaikan:** jangan hapus file lama (biarkan sebagai arsip), atau simpan salinan per dokumen.

### S-09. Reformulasi tidak menyalin produk dan perhitungan versinya keliru
[app/Services/FormulaService.php:185-228](app/Services/FormulaService.php:185)
- `product_id`, `target_dose_*`, `target_sachet*`, dan `notes` tidak disalin. Formula versi baru tidak terhubung ke produk, padahal `product_id` wajib saat create/update.
- Versi dihitung dengan `where('parent_formula_id', $formula->id)->orWhere('id', $formula->id)`. Jika yang direformulasi adalah V2 (anak dari root), anak-anak root yang lain tidak ikut terhitung. Akibatnya bisa muncul **nomor versi ganda** (misalnya dua formula V3). Seharusnya memakai `$rootId = $formula->parent_formula_id ?? $formula->id`.

### S-10. Duplicate (revisi baru) Formula Approval langsung berstatus Pending tanpa pengecekan
[FormulaApprovalController.php:306-353](app/Http/Controllers/FormulaApprovalController.php:306) dan [ApprovalFormulaDesignController.php:288-328](app/Http/Controllers/ApprovalFormulaDesignController.php:288)
- Hanya mengecek `formula.edit`. Siapa pun bisa menduplikasi form milik orang lain, termasuk yang masih Draft.
- `approval_status = 'Pending'` langsung masuk antrean GM tanpa lewat `submit()`, sementara `submitted_at = null`. Data dan alurnya jadi ambigu.

### S-11. Tracker status bisa diubah oleh siapa saja yang punya `formula.view`
[FormulaApprovalController::updateTracker:582](app/Http/Controllers/FormulaApprovalController.php:582) dan [ApprovalFormulaDesignController::updateTracker:435](app/Http/Controllers/ApprovalFormulaDesignController.php:435). Status seperti "Approved Direktur Utama" bisa diisi oleh Staff atau OM. Tidak ada pembatasan role.

### S-12. Aturan validasi `in:` dibangun dari nama kategori
[ApprovalFormulaDesignController.php:81, 205](app/Http/Controllers/ApprovalFormulaDesignController.php:81)
```php
'bentuk_sediaan' => 'nullable|in:' . ProductCategory::pluck('name')->implode(','),
```
Nama kategori yang mengandung koma akan memecah aturan. Jika tabel kategori kosong, aturannya menjadi `in:` yang menolak semua nilai. Gunakan `Rule::in(ProductCategory::pluck('name'))`.

---

## 4. 🟡 Rendah

| # | Lokasi | Masalah |
|---|---|---|
| R-01 | [RegulatoryDossierController.php:238](app/Http/Controllers/RegulatoryDossierController.php:238), dan baris yang sama di CommercialProductionController | `$validated['folder_id'] ? ...`: jika `folder_id` tidak dikirim, muncul *Undefined array key* (500) **setelah** file tersimpan dan baris DB terbuat. Gunakan `!empty($validated['folder_id'])`. |
| R-02 | [TrialPmController.php:269](app/Http/Controllers/TrialPmController.php:269) | `$validated['notes']` bisa memicu *Undefined array key* jika field `notes` tidak terkirim. Gunakan `?? null`. |
| R-03 | [SampleEvaluationController.php:263, 298-299](app/Http/Controllers/SampleEvaluationController.php:263) | `$validated['parameters'][$parameter]['score']` tanpa aturan `required` per parameter. Error ditangkap `catch (\Throwable)`, lalu **pesan exception mentah ditampilkan ke user**. File yang sudah di-upload tidak dihapus saat transaksi rollback. `attachment_types[]` tidak divalidasi. |
| R-04 | [PrfController.php:156](app/Http/Controllers/PrfController.php:156), [NpdProposalController.php:238](app/Http/Controllers/NpdProposalController.php:238), [PreformulationStudyController.php:191](app/Http/Controllers/PreformulationStudyController.php:191) | `destroyDocument()` hanya menghapus baris DB, **file fisik tertinggal** di storage. Begitu juga `destroy()` pada PRF, NPD, Preformulation, Trial PM (foto), dan Log Book PM (lampiran/scan). |
| R-05 | Edit view PRF, NPD, QbD, NIE, Sample Evaluation, Stability, Tech Transfer, Trial PM | Form edit tidak punya pilihan `product_id`, tetapi `product_name` bisa diketik bebas. Akibatnya `product_name` dan `product_id` bisa tidak sinkron (nama berubah, relasi produk tetap). |
| R-06 | [PreformulationStudyService.php:25, 43-50](app/Services/PreformulationStudyService.php:25) | `create()` mengabaikan `code` yang divalidasi dari form dan membuat kode baru, sehingga kode di layar bisa berbeda dengan yang tersimpan. `update()` memakai `?? $study->x`, jadi field nullable (misalnya `end_date`) **tidak bisa dikosongkan**. Controller `update` juga tidak memvalidasi `product_id`. |
| R-07 | [CheckMaintenanceMode.php:26-32](app/Http/Middleware/CheckMaintenanceMode.php:26) | Pola pengecualian `password/*` tidak cocok dengan route `forgot-password` / `reset-password/{token}`. Saat maintenance, user yang diizinkan bypass tidak bisa reset password. |
| R-08 | [app/Helpers/setting.php](app/Helpers/setting.php), [TimelineController.php:248-283](app/Http/Controllers/TimelineController.php:248) | **Performa.** `setting()` menjalankan query DB setiap dipanggil, tanpa cache. Middleware maintenance memanggilnya di setiap request. Timeline memuat **seluruh baris dari 12 tabel** ke memori setiap kali dibuka, dan `getWorkload()` menjalankan `User::find` dalam loop. |
| R-09 | Semua upload ke disk `public` | Dokumen R&D/regulasi yang rahasia bisa diakses langsung lewat `/storage/...` tanpa login, melewati permission dan audit log download. Nama file acak mengurangi risikonya, tetapi tidak menghilangkannya. Pertimbangkan disk `local` dengan download lewat controller. |

---

## 5. 🔵 Ambigu / Code smell

1. **Permission yang tidak pernah diberikan.** `formula.delete`, `trial_rm.delete`, dan `trial_pm.delete` dibuat di [RolePermissionSeeder](database/seeders/RolePermissionSeeder.php), tetapi tidak di-assign ke role mana pun. Akibatnya Staff tidak pernah bisa menghapus Draft Formula, Trial RM, Trial PM, maupun Formula Approval miliknya sendiri. Policy-nya ada, tetapi selalu `false` (kecuali Superadmin).
2. **Nama permission tidak konsisten.**
   - QbD, NIE, Tech Transfer, dan Stability Test memakai `*.edit` untuk *create* (route dan controller), sehingga permission `*.create` tidak terpakai.
   - Semua route Stability Test hanya memakai `can:stability_test.view`. Pengecekan sebenarnya ada di controller, jadi middleware route menyesatkan.
   - `PreformulationStudyPolicy` memakai permission `npd_proposal.*`, dan route-nya memakai `role:` alih-alih `can:`.
3. **Sisa alur "Approval by OM".** OM sudah dihapus dari Formula Approval, tetapi status `'Approval by OM'`, method `approveOm()` (selalu 422), kolom `approved_by_om/approved_at_om`, dan relasi `omApprover` masih tersebar di controller, provider, dan Timeline.
4. **Kode mati.**
   - `FormulaApprovalController::canMutate()` dan konstanta `MUTATION_BLOCKED` tidak pernah dipanggil.
   - [app/Http/Middleware/RoleMiddleware.php](app/Http/Middleware/RoleMiddleware.php) tidak dipakai, karena alias `role` di `bootstrap/app.php` mengarah ke middleware milik Spatie.
   - `$pendingApprovalFormulaDesigns` di [ApprovalCenterController:95](app/Http/Controllers/ApprovalCenterController.php:95) tidak dipakai.
   - `$prf = Prf::findOrFail(...)` di [NpdProposalController:107](app/Http/Controllers/NpdProposalController.php:107) tidak dipakai.
   - Permission `packaging_development.*` tetap di-seed padahal modulnya sudah dihapus.
5. **Kode duplikat.**
   - `RegulatoryDossierController` dan `CommercialProductionController` **identik** (hanya berbeda nama), begitu juga model, export, dan view-nya.
   - `FormulaApprovalController` dan `ApprovalFormulaDesignController` sekitar 90% sama dan memakai model yang sama.
   - Akibatnya setiap perbaikan bug (misalnya R-01) harus dilakukan dua kali.
6. **Satu model diakses dari dua modul tanpa cek `source`.** Form `source = approval-formula-design` tetap bisa dibuka atau di-approve lewat `/formula-approvals/{id}`, dan sebaliknya.
7. **Field `name` di validasi Formula tidak berguna.** `name` divalidasi lalu selalu ditimpa dengan `Product::name` ([FormulaController.php:119-120](app/Http/Controllers/FormulaController.php:119)). Untuk tipe Formula, `artwork_title` diisi dengan `product_name` ([FormulaApprovalController.php:148](app/Http/Controllers/FormulaApprovalController.php:148)).
8. **Dashboard GM** ([TimelineController.php:350](app/Http/Controllers/TimelineController.php:350)) menampilkan item "Approve T1" yang hanya bisa di-approve OM. Sebaliknya, OM tidak melihat Trial PM `Pending Approval` yang menjadi tugasnya.
9. **Generator kode** (`generateCode` di semua service) tidak aman dari race condition. Dua user yang menyimpan bersamaan bisa mendapat kode sama dan terkena error unique. `TrialRmService` menaikkan huruf `Z` menjadi `[`. Pesan seeder menulis "4 Roles", padahal yang dibuat 5.
10. **Kebersihan repo.** Folder `node-temp/` (binary Node.js 22, **2.447 file**) ikut ter-commit di git.

---

## 6. Status test suite (dijalankan di SQLite, dengan migration S-06 dilewati sementara)

| Kelompok | Jumlah | Penyebab | Bug kode atau test usang? |
|---|---|---|---|
| `PackagingDevelopmentFeatureTest` | 13 error | Model `PackagingDevelopment` dan route `packaging-developments.*` sudah dihapus, tetapi test, migration `2026_08_19_120000`, seeder, dan permission-nya masih ada | Test usang dan modul dihapus setengah jalan |
| `Auth\Authentication/Registration/EmailVerification` | 3 gagal | Test mengharapkan redirect ke `/dashboard`, sekarang ke `/timeline` | Test usang |
| `FormulaTest` | 2 gagal | Test tidak mengirim `product_id`, yang sekarang wajib | Test usang |
| `FormulaApprovalSmokeTest` | 4 gagal + 2 error | Test tidak mengirim field `type` yang sekarang wajib, dan masih menguji alur OM | Test usang |
| `ApprovalCenterTest` (form approval) | 2 gagal | Masih mengharapkan tahap `Approval by OM` | Test usang |
| `SampleEvaluationSmokeTest` | 1 gagal | Mengirim `trial_batch` sebagai integer, sekarang string | Test usang |

Kesimpulan: kegagalan di tabel ini **bukan bug baru**. Test belum diperbarui setelah perubahan fitur. Namun karena test tidak berjalan (S-06), bug seperti K-01 sampai K-04 lolos tanpa terdeteksi.

---

## 7. Urutan perbaikan yang disarankan

1. **K-01, K-02, S-07**: perbaikan satu atau dua baris, langsung menghilangkan error 500 dan kegagalan deploy.
2. **K-04, K-08, K-07**: menutup celah otorisasi.
3. **K-05, K-06, K-03**: perlu migration baru (FK `restrict`/`nullOnDelete`, `supplier_id` nullable). Backup database dulu.
4. **S-06** lalu perbarui test usang (bagian 6), supaya test suite bisa menjadi pengaman.
5. **S-01 sampai S-05, S-08 sampai S-12**: bug alur kerja.
6. Bagian 4 dan 5: refactor bertahap (gabungkan controller duplikat, rapikan permission).
