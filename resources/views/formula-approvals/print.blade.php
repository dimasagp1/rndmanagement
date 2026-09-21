<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Approval Form - {{ $formApproval->product_name }} - {{ $formApproval->code }}</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 portrait;
            margin: 18mm 12mm 20mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.35;
            background: #fff;
            padding: 0;
            position: relative;
        }

        /* ── Master Print Layout Table ─────────────────────── */
        table.print-layout-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin: 0;
            padding: 0;
        }

        table.print-layout-table>thead {
            display: table-header-group;
        }

        table.print-layout-table>tfoot {
            display: table-footer-group;
        }

        table.print-layout-table>tbody>tr>td {
            border: none;
            padding: 0;
        }

        /* ── Fixed Header ─────────────────────────────────── */
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 14mm;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #000;
            padding: 0 2mm;
            background: #fff;
            z-index: 100;
        }

        .print-header .logo-area {
            display: flex;
            align-items: center;
            gap: 3mm;
            width: 32%;
        }

        .print-header .logo-icon {
            width: 9mm;
            height: 9mm;
            background: #1a6b3c;
            border-radius: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 900;
            font-size: 6.5pt;
            letter-spacing: 0.5pt;
        }

        .print-header .logo-text {
            font-size: 8.5pt;
            font-weight: 700;
            color: #1a6b3c;
            letter-spacing: 1pt;
        }

        .print-header .title-area {
            flex: 1;
            text-align: center;
            font-size: 11pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .print-header .form-number {
            width: 32%;
            text-align: right;
            font-size: 9pt;
            font-weight: 600;
        }

        /* ── Fixed Footer ─────────────────────────────────── */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 12mm;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 0 2mm 2mm 2mm;
            background: #fff;
            z-index: 100;
        }

        .print-footer .lamp-text {
            font-size: 8pt;
            color: #111;
            font-weight: 600;
        }

        .print-footer .page-text {
            font-size: 8pt;
            color: #111;
            font-weight: 600;
        }

        /* ── Watermark ────────────────────────────── */
        .watermark {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 64pt;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.07);
            letter-spacing: 10pt;
            z-index: 2;
            pointer-events: none;
            white-space: nowrap;
            user-select: none;
        }

        /* ── Form Container ───────────────────────── */
        .form-container {
            border: 1.5px solid #000;
            padding: 4mm 5mm;
            min-height: 236mm;
            box-sizing: border-box;
            position: relative;
        }

        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* ── Sections & Typography ────────────────── */
        .section-header {
            font-weight: 700;
            font-size: 9.5pt;
            margin-top: 3mm;
            margin-bottom: 2mm;
        }

        .section-note {
            font-style: italic;
            font-weight: 400;
            font-size: 8.5pt;
        }

        .numbered-list {
            list-style: none;
            padding-left: 5mm;
            margin-bottom: 3mm;
        }

        .numbered-list li {
            display: flex;
            margin-bottom: 1.2mm;
            align-items: baseline;
        }

        .numbered-list .num {
            width: 5mm;
            flex-shrink: 0;
        }

        .numbered-list .label {
            width: 32mm;
            flex-shrink: 0;
        }

        .numbered-list .sep {
            margin-right: 2.5mm;
        }

        .numbered-list .val {
            flex: 1;
            font-weight: 500;
        }

        /* ── Data Tables ──────────────────────────── */
        table.review-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 3mm;
        }

        table.review-table th,
        table.review-table td {
            border: 1px solid #000;
            padding: 2.5mm 3mm;
            vertical-align: top;
        }

        table.review-table th {
            background-color: #d9d9d9;
            font-weight: 700;
            text-align: center;
        }

        /* ── Organoleptic Inner Table / Rows ──────── */
        .organo-grid {
            display: table;
            width: 100%;
        }

        .organo-row {
            display: table-row;
            line-height: 1.5;
        }

        .organo-label {
            display: table-cell;
            width: 22mm;
            font-weight: 500;
            padding-bottom: 1mm;
        }

        .organo-sep {
            display: table-cell;
            width: 4mm;
            padding-bottom: 1mm;
        }

        .organo-val {
            display: table-cell;
            padding-bottom: 1mm;
        }

        /* ── Checkbox Items ───────────────────────── */
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 2mm;
            margin-bottom: 1.5mm;
            font-size: 9pt;
        }

        .checkbox-box {
            width: 3.5mm;
            height: 3.5mm;
            border: 1.2px solid #000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1;
        }

        /* ── Approval Signature Table (Section E) ──── */
        table.approval-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 2mm;
            table-layout: fixed;
        }

        table.approval-table th,
        table.approval-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 2mm 1.5mm;
            word-wrap: break-word;
        }

        table.approval-table .company-header {
            background-color: #d9d9d9;
            font-weight: 700;
            font-size: 9.5pt;
            padding: 2.5mm;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }

        table.approval-table .role-header {
            background-color: #d9d9d9;
            font-weight: 700;
            font-size: 8.5pt;
            height: 11mm;
        }

        table.approval-table .signature-box {
            height: 32mm;
            background-color: #fff;
        }

        table.approval-table .name-footer {
            font-weight: 700;
            font-size: 8.5pt;
            padding: 2mm 1mm;
            min-height: 8mm;
        }

        /* ── Print Media Controls ─────────────────── */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    @php
        $organo = $formApproval->organoleptic_details;
        $signers = $formApproval->approval_signers_list;
        $isLanjut = ($formApproval->owner_decision === 'Lanjut commercial production' || $formApproval->approval_status === 'Approved');
        $isReview = ($formApproval->owner_decision === 'Review kembali' || $formApproval->approval_status === 'Rejected');
    @endphp

    {{-- ═══════════════════════════════════════════════════════
         FIXED HEADER & FOOTER (Repeats on all printed pages)
    ════════════════════════════════════════════════════════ --}}
    <div class="print-header">
        <div class="logo-area">
            @if(setting('print_logo'))
                <img src="{{ asset('storage/' . setting('print_logo')) }}"
                     style="height: 9mm; width: auto; max-width: 26mm; object-fit: contain; vertical-align: middle;">
            @else
                <div class="logo-icon">HT</div>
                <span class="logo-text">{{ strtoupper(setting('app_name', 'HERBATECH')) }}</span>
            @endif
        </div>
        <div class="title-area">SAMPLE APPROVAL FORM</div>
        <div class="form-number">No. CM-06/RD/001-05.00</div>
    </div>

    <div class="print-footer">
        <span class="lamp-text">LAMP. E PR-06/RD/001.00</span>
        <span class="page-text" id="pageNumberSpan"></span>
    </div>

    <table class="print-layout-table">
        <thead>
            <tr>
                <td>
                    <div style="height: 16mm;"></div>
                </td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td>
                    <div style="height: 14mm;"></div>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td>
                    {{-- ═══════════════════════════════════════════════════════
                         HALAMAN 1: Data Produk, Hasil Review, Keputusan, Lampiran
                    ════════════════════════════════════════════════════════ --}}
                    <div class="form-container">

                        {{-- No. Usulan --}}
                        <div style="display: flex; align-items: baseline; margin-bottom: 2mm; font-weight: 700;">
                            <span style="width: 24mm;">No. Usulan</span>
                            <span style="margin-right: 2.5mm;">:</span>
                            <span>{{ $formApproval->proposal_number ?? ($formApproval->sample_code ? preg_replace('/[^0-9]/', '', $formApproval->sample_code) : str_pad((string)$formApproval->id, 3, '0', STR_PAD_LEFT)) }}</span>
                        </div>

                        {{-- A. DATA PRODUK --}}
                        <div class="section-header">A. DATA PRODUK <span class="section-note">(diisi oleh R&D)</span></div>
                        <ul class="numbered-list">
                            <li>
                                <span class="num">1.</span>
                                <span class="label">Nama Produk</span>
                                <span class="sep">:</span>
                                <span class="val">{{ $formApproval->product_name }}</span>
                            </li>
                            <li>
                                <span class="num">2.</span>
                                <span class="label">Kode Sampel</span>
                                <span class="sep">:</span>
                                <span class="val">{{ $formApproval->sample_code ?? '—' }}</span>
                            </li>
                            <li>
                                <span class="num">3.</span>
                                <span class="label">Kategori</span>
                                <span class="sep">:</span>
                                <span class="val">{{ $formApproval->kategori ?? 'New Product' }}</span>
                            </li>
                            <li>
                                <span class="num">4.</span>
                                <span class="label">Komoditi</span>
                                <span class="sep">:</span>
                                <span class="val">{{ $formApproval->komoditi ?? 'Pangan' }}</span>
                            </li>
                            <li>
                                <span class="num">5.</span>
                                <span class="label">Bentuk Sediaan</span>
                                <span class="sep">:</span>
                                <span class="val">{{ $formApproval->bentuk_sediaan ?? 'Serbuk' }}</span>
                            </li>
                        </ul>

                        {{-- B. HASIL REVIEW --}}
                        <div class="section-header">B. HASIL REVIEW <span class="section-note">(diisi oleh R&D)</span></div>
                        <table class="review-table">
                            <thead>
                                <tr>
                                    <th style="width: 34%;">Parameter</th>
                                    <th style="width: 66%;">Hasil Review</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Row 1: Organoleptik --}}
                                <tr>
                                    <td>
                                        <strong>Organoleptik</strong> (bau, warna, rasa, konsistensi, saat aplikasi)
                                    </td>
                                    <td>
                                        <div class="organo-grid">
                                            <div class="organo-row">
                                                <span class="organo-label">Bentuk</span>
                                                <span class="organo-sep">:</span>
                                                <span class="organo-val">{{ $organo['bentuk'] ?: ($formApproval->bentuk_sediaan ?? '—') }}</span>
                                            </div>
                                            <div class="organo-row">
                                                <span class="organo-label">Warna</span>
                                                <span class="organo-sep">:</span>
                                                <span class="organo-val">{{ $organo['warna'] ?: '—' }}</span>
                                            </div>
                                            <div class="organo-row">
                                                <span class="organo-label">Aroma</span>
                                                <span class="organo-sep">:</span>
                                                <span class="organo-val">{{ $organo['aroma'] ?: '—' }}</span>
                                            </div>
                                            <div class="organo-row">
                                                <span class="organo-label">Rasa</span>
                                                <span class="organo-sep">:</span>
                                                <span class="organo-val">{{ $organo['rasa'] ?: '—' }}</span>
                                            </div>
                                        </div>
                                        @if($formApproval->sensory_product && empty($organo['warna']) && empty($organo['aroma']) && empty($organo['rasa']))
                                            <div style="margin-top: 1mm; font-size: 8.5pt; color: #333;">
                                                {{ $formApproval->sensory_product }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Row 2: Detail Formulasi --}}
                                <tr>
                                    <td><strong>Detail Formulasi</strong></td>
                                    <td style="text-align: justify;">
                                        @if($formApproval->komposisi)
                                            {{ $formApproval->komposisi }}
                                        @elseif($formApproval->formula && $formApproval->formula->materials->isNotEmpty())
                                            {{ $formApproval->formula->materials->map(fn($m) => ($m->material?->name ?? '') . ($m->percentage > 0 ? ' (' . number_format($m->percentage, 2) . '%)' : ''))->filter()->implode(', ') }}
                                        @else
                                            <span style="color: #666;">— Belum ada detail formulasi —</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Row 3: Hasil Panel --}}
                                <tr>
                                    <td>
                                        <strong>Hasil Panel</strong>
                                        <div style="font-size: 8pt; font-style: italic; color: #444;">(Diisi oleh EICK)</div>
                                    </td>
                                    <td style="white-space: pre-line;">
                                        @if($formApproval->panel_result)
                                            {{ $formApproval->panel_result }}
                                        @else
                                            Berdasarkan uji sampel {{ $formApproval->sample_code ?? $formApproval->product_name }} yang telah dilaksanakan oleh responden, diperoleh hasil penilaian sampel yang memenuhi kualifikasi mutu dan penerimaan panelis.
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- C. KEPUTUSAN --}}
                        <div class="section-header">C. KEPUTUSAN <span class="section-note">(diisi oleh Pemilik Produk/Merk)</span></div>
                        <div style="padding-left: 3mm; margin-bottom: 3mm;">
                            <div class="checkbox-item">
                                <span class="checkbox-box">{{ $isLanjut ? 'V' : '' }}</span>
                                <span>Lanjut commercial production</span>
                            </div>
                            <div class="checkbox-item">
                                <span class="checkbox-box">{{ $isReview ? 'V' : '' }}</span>
                                <span>Review kembali</span>
                            </div>
                            <div style="margin-top: 1.5mm; font-size: 9pt;">
                                <strong>Alasan:</strong> {{ $formApproval->owner_decision_reason ?? $formApproval->decision_reason ?? 'Penilaian responden terhadap sampel Overall sudah oke' }}
                            </div>
                        </div>

                        {{-- D. LAMPIRAN --}}
                        <div class="section-header">D. LAMPIRAN</div>
                        <div style="padding-left: 3mm; font-size: 8.5pt;">
                            <ul style="list-style-type: disc; margin-left: 4mm;">
                                <li>Hasil panel sampel {{ $formApproval->product_name }} ({{ $formApproval->sample_code ?? '—' }})</li>
                                @foreach($formApproval->attachments as $att)
                                    <li>{{ $att->original_name }} ({{ $att->document_type }})</li>
                                @endforeach
                            </ul>

                            {{-- Preview Gambar Lampiran jika ada --}}
                            @php
                                $imageAttachment = $formApproval->attachments->first(function($a) {
                                    $ext = strtolower(pathinfo($a->original_name, PATHINFO_EXTENSION));
                                    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                });
                            @endphp
                            @if($imageAttachment)
                                <div style="margin-top: 2mm; text-align: center;">
                                    <img src="{{ asset('storage/' . $imageAttachment->file_path) }}"
                                         style="max-width: 90%; max-height: 48mm; object-fit: contain; border: 1px solid #ccc; border-radius: 2px;">
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- ═══════════════════════════════════════════════════════
                         HALAMAN 2: Persetujuan (Section E)
                    ════════════════════════════════════════════════════════ --}}
                    <div class="page-break"></div>

                    <div class="form-container" style="min-height: 236mm;">
                        {{-- Watermark CONFIDENTIAL on Page 2 --}}
                        <div class="watermark">CONFIDENTIAL</div>

                        {{-- E. PERSETUJUAN --}}
                        <div class="section-header" style="margin-top: 1mm; margin-bottom: 3mm;">E. PERSETUJUAN</div>

                        <table class="approval-table">
                            <thead>
                                <tr>
                                    <th colspan="{{ count($signers) }}" class="company-header">
                                        {{ $formApproval->approval_company ?? 'PT Erhanesia Idea Cipta Karsa' }}
                                    </th>
                                </tr>
                                <tr>
                                    @foreach($signers as $signer)
                                        <th class="role-header">
                                            {{ $signer['title'] ?? 'Approver' }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    {{-- Kolom Tanda Tangan: KOSONG polos untuk tanda tangan basah manual --}}
                                    @foreach($signers as $signer)
                                        <td class="signature-box">
                                            {{-- Dikosongkan sesuai permintaan pengguna --}}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    {{-- Baris Nama Pejabat / Penandatangan --}}
                                    @foreach($signers as $signer)
                                        <td class="name-footer">
                                            <span style="text-decoration: underline;">{{ $signer['name'] ?: '—' }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </td>
            </tr>
        </tbody>
    </table>

    {{-- Script untuk penomoran halaman dinamis & auto-print jika ada query ?autoprint=1 --}}
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                window.print();
            }
        });
    </script>

</body>

</html>
