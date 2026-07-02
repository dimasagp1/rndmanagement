# Business Rules & Validation — R&D Management System
### PT Herbatech Innopharma Industry

---

## 1. Aturan RBAC (Role-Based Access Control)

| Role | Create | Read | Update | Delete | Approve |
|---|---|---|---|---|---|
| **Staff R&D** | ✅ Formulasi RM, Trial RM, Trial PM | ✅ Semua modul | ✅ hanya *sebelum* disetujui | ❌ | ❌ |
| **Operational Manager** | ❌ | ✅ Semua modul | ❌ | ❌ | ✅ Approval Tahap 1 |
| **General Manager (Ibu Lisa)** | ❌ | ✅ Read-only penuh (riwayat) | ✅ khusus approval final | ❌ | ✅ Approval Tahap 2 (final) |

**Catatan implementasi:** setelah suatu record berstatus selain Draf/Pending revisi, field-field input untuk Staff R&D otomatis terkunci (`disabled`) — validasi dilakukan di layer Policy, bukan hanya UI.

---

## 2. Aturan Validasi — Modul Formulasi RM

| # | Aturan | Trigger | Aksi Sistem |
|---|---|---|---|
| R1 | Total Persentase Komposisi Material harus **tepat 100%** | Saat submit/simpan | Blokir simpan, tampilkan error jika total ≠ 100% |
| R2 | Kode Formula unik & auto-generate | Saat create draf baru | Format `FRM-YYYYMM-XXX`, increment otomatis per bulan |
| R3 | Field hanya dapat diedit oleh Staff R&D sebelum status Approved | Setiap update | Cek status saat ini via Policy sebelum izinkan update |
| R4 | Status Persetujuan Akhir bersifat read-only bagi Staff R&D | Selalu | Hanya berubah otomatis oleh aksi approval GM |

---

## 3. Aturan Validasi — Modul Catatan Trial RM

| # | Aturan | Trigger | Aksi Sistem |
|---|---|---|---|
| T1 | Integrasi wajib ke Formulasi RM | Create Trial RM baru | Sistem wajib minta input "Kode Formula"; setelah dipilih, tarik ulang tabel Komposisi Material, Supplier, Persentase secara **read-only** |
| T2 | Identitas Sampel auto-generate | Create Trial RM baru | Format `TRM-YYYYMM-XXX-A`, suffix huruf increment per batch dalam kode formula yang sama |
| T3 | Aturan Iterasi/Reformulasi | Keputusan Trial = **Reformulasi** | Sistem otomatis clone Formulasi RM asal menjadi versi baru (`FRM-YYYYMM-XXX-V2`); Staff R&D **hanya boleh** memodifikasi versi terbaru |
| T4 | Verifikasi Hasil menampilkan Target vs Aktual | Selalu (parameter organoleptik & fisika-kimia) | UI menampilkan selisih/indikator visual, tidak memblokir simpan |

---

## 4. Aturan Validasi — Modul Catatan Trial PM

| # | Aturan | Trigger | Aksi Sistem |
|---|---|---|---|
| P1 | Validasi Persetujuan Kolektif | Approve Trial PM | Status **tidak dapat** menjadi Approved apabila salah satu dari 4 departemen (R&D, QC, Produksi, Engineering) belum menyetujui Kesimpulan Kelaikan |
| P2 | Setiap departemen mengisi catatan individual | Saat centang checklist | Wajib isi catatan teks per departemen sebelum checklist dianggap valid |

---

## 5. Hierarki Approval Gate (berlaku lintas modul)

| # | Aturan | Detail |
|---|---|---|
| A1 | Approval berjenjang wajib | Trial **tidak dapat** diajukan ke tahap General Manager apabila Operational Manager belum memberikan Approval Tahap 1 |
| A2 | Approval Tahap 1 (Operational Manager) | Read seluruh modul; berwenang beri ulasan / minta revisi / approve teknis |
| A3 | Approval Tahap 2 (General Manager) | Hanya bisa dieksekusi setelah A1 terpenuhi; keputusan final menuju skala produksi komersial |
| A4 | Rejected di tahap manapun | Record kembali ke Staff R&D untuk revisi; status berubah menjadi Rejected/Perlu Revisi |

**Diagram Gate:**
```
Staff R&D ──submit──▶ Operational Manager ──Approval Tahap 1──▶ General Manager ──Approval Tahap 2──▶ FINAL
                │ reject/revisi                    │ reject/revisi
                ▼                                   ▼
         kembali ke Staff R&D                kembali ke Staff R&D
```

---

## 6. Aturan Auto-Generate & Penomoran

| Entitas | Format | Contoh | Increment |
|---|---|---|---|
| Kode Formula | `FRM-YYYYMM-XXX` | `FRM-202607-001` | per bulan, 3 digit |
| Versi Formula (reformulasi) | `FRM-YYYYMM-XXX-V{n}` | `FRM-202607-001-V2` | per iterasi reformulasi |
| Identitas Sampel Trial RM | `TRM-YYYYMM-XXX-{A,B,C...}` | `TRM-202607-001-A` | huruf per batch dalam formula yang sama |

---

## 7. Matriks Status (State Machine)

### Status Formulasi RM
```
Draf → Pending Tahap 1 → Pending Tahap 2 → Approved
                     ↘ Rejected/Revisi ↗
```

### Status Trial RM
```
Draf → Diajukan → Lulus → Pending Approval Gate → Approved
                ↘ Reformulasi → (clone formula versi baru, mulai ulang siklus)
```

### Status Trial PM
```
Draf → Menunggu Penilaian 4 Departemen → (4/4 approve) → Approved
                                       ↘ (ada yang reject) → Rejected/Revisi
```

---

## 8. Business Rules Checklist (Ringkas — untuk QA/Testing)

- [ ] R1: Simpan Formulasi RM ditolak jika total % ≠ 100.
- [ ] R2: Kode Formula ter-generate otomatis dan unik.
- [ ] R3: Staff R&D tidak bisa edit Formulasi RM setelah status Approved.
- [ ] T1: Data komposisi di Trial RM read-only dan sesuai Formulasi RM terpilih.
- [ ] T3: Keputusan "Reformulasi" memicu clone otomatis + versi baru.
- [ ] P1: Trial PM tidak bisa Approved bila salah satu dari 4 departemen belum setuju.
- [ ] A1: Tombol/aksi Approval Tahap 2 (GM) disabled selama Approval Tahap 1 belum selesai.
- [ ] Audit trail mencatat setiap perubahan status & data penting (siapa, kapan, apa).

---

## 9. Catatan Non-Fungsional Terkait Rules

- Semua validasi bisnis di atas **wajib** diterapkan di layer **Backend (Form Request/Policy/Service)**, validasi UI (Alpine.js) hanya sebagai *first-line feedback*, bukan satu-satunya lapisan keamanan.
- Setiap transisi status wajib tercatat di `activity_log` (siapa, kapan, status sebelum/sesudah) untuk memenuhi kebutuhan traceability pada dokumen PRD.
