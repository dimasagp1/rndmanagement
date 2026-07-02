@php
    $resolveSigUrl = function($path) {
        if (!$path) return null;
        $path = str_starts_with($path, '/storage/') ? substr($path, 9) : $path;
        return asset('storage/' . $path);
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Form Trial PM - {{ $trialPm->code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Base ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A4 portrait;
            margin: 14mm 12mm 20mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
            background: #fff;
            padding-top: 22mm;
            padding-bottom: 18mm;
        }

        /* ── Fixed Header (repeats on every page) ─────────── */
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
            width: 35%;
        }
        .print-header .logo-icon {
            width: 10mm;
            height: 10mm;
            background: #1a6b3c;
            border-radius: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 900;
            font-size: 7pt;
            letter-spacing: 0.5pt;
        }
        .print-header .logo-text {
            font-size: 9pt;
            font-weight: 700;
            color: #1a6b3c;
            letter-spacing: 1pt;
        }
        .print-header .title-area {
            flex: 1;
            text-align: center;
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        .print-header .form-number {
            width: 35%;
            text-align: right;
            font-size: 9pt;
            font-weight: 400;
        }

        /* ── Fixed Footer (repeats on every page) ─────────── */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 16mm;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 0 2mm 2mm 2mm;
            background: #fff;
            z-index: 100;
        }
        .print-footer .lamp-text {
            font-size: 8pt;
            color: #333;
        }
        .print-footer .master-copy {
            border: 2px solid #000;
            padding: 1mm 4mm;
            font-size: 12pt;
            font-weight: 900;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }
        .print-footer .page-number {
            font-size: 8pt;
            color: #333;
        }

        /* ── Outer Border ─────────────────────────────────── */
        .form-container {
            border: 1.5px solid #000;
            padding: 4mm 4mm 3mm 4mm;
            min-height: 240mm;
        }

        /* ── Page Breaks ──────────────────────────────────── */
        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* ── Typography ───────────────────────────────────── */
        .section-title {
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2mm;
            letter-spacing: 0.3pt;
        }
        .section-subtitle {
            font-size: 8pt;
            font-style: italic;
            font-weight: 400;
            color: #333;
        }
        .font-signature {
            font-family: 'Dancing Script', cursive;
        }

        /* ── Field Rows ───────────────────────────────────── */
        .field-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 1.5mm;
            font-size: 10pt;
        }
        .field-label {
            font-weight: 700;
            min-width: 42mm;
            flex-shrink: 0;
        }
        .field-sep {
            margin: 0 2mm;
        }
        .field-value {
            flex: 1;
            border-bottom: 1px dotted #000;
            min-height: 4mm;
            padding-bottom: 0.5mm;
        }
        .field-note {
            font-style: italic;
            font-size: 9pt;
            margin-left: 3mm;
            color: #333;
        }

        /* ── Numbered List (Section A) ────────────────────── */
        .numbered-list {
            list-style: none;
            padding: 0;
        }
        .numbered-list li {
            display: flex;
            align-items: baseline;
            margin-bottom: 1.5mm;
            font-size: 10pt;
        }
        .numbered-list .num {
            font-weight: 700;
            min-width: 8mm;
            flex-shrink: 0;
        }
        .numbered-list .item-label {
            min-width: 55mm;
            flex-shrink: 0;
        }
        .numbered-list .item-sep {
            margin: 0 2mm;
        }
        .numbered-list .item-value {
            flex: 1;
            border-bottom: 1px dotted #000;
            min-height: 4mm;
            padding-bottom: 0.5mm;
        }

        /* ── Tables ───────────────────────────────────────── */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 9pt;
        }
        .form-table th,
        .form-table td {
            border: 1px solid #000;
            padding: 1.5mm 2mm;
            vertical-align: top;
        }
        .form-table thead th {
            background: #d9d9d9;
            font-weight: 700;
            text-align: center;
            font-size: 9pt;
        }
        .form-table tbody td {
            font-size: 9pt;
        }
        .form-table .col-center { text-align: center; }
        .form-table .col-right  { text-align: right; }

        /* ── Section C merged header ─────────────────────── */
        .merged-header th {
            background: #d9d9d9;
            font-weight: 700;
            text-align: center;
            border: 1px solid #000;
            font-size: 9pt;
            padding: 1.5mm 2mm;
        }

        /* ── Signature Table ──────────────────────────────── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .sig-table th,
        .sig-table td {
            border: 1px solid #000;
            padding: 2mm 3mm;
            vertical-align: middle;
        }
        .sig-table thead th {
            background: #d9d9d9;
            font-weight: 700;
            text-align: center;
        }
        .sig-image {
            height: 8mm;
            width: auto;
            max-width: 24mm;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
        }
        .sig-table .sig-image {
            height: 12mm;
            width: auto;
            max-width: 35mm;
            object-fit: contain;
        }

        /* ── Print color adjust ───────────────────────────── */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }
        }

        /* ── Utility ──────────────────────────────────────── */
        .text-bold   { font-weight: 700; }
        .text-center { text-align: center; }
        .text-italic { font-style: italic; }
        .mt-2 { margin-top: 2mm; }
        .mt-3 { margin-top: 3mm; }
        .mb-2 { margin-bottom: 2mm; }
        .mb-3 { margin-bottom: 3mm; }
        .empty-row { height: 10mm; }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════════════
         FIXED HEADER (repeats on every printed page)
    ════════════════════════════════════════════════════════ --}}
    <div class="print-header">
        <div class="logo-area">
            <div class="logo-icon">HT</div>
            <span class="logo-text">HERBATECH</span>
        </div>
        <div class="title-area">FORM CATATAN TRIAL<br>BAHAN PENGEMAS</div>
        <div class="form-number">No. CM-06/RD/002-03.00</div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         FIXED FOOTER (repeats on every printed page)
    ═══════════════════════════════════════════════════════ --}}
    <div class="print-footer">
        <span class="lamp-text">LAMP. D PR-06/RD/002.02</span>
        <span class="master-copy">MASTER COPY</span>
        <span class="page-number" id="footer-page">Halaman 1 dari 3</span>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE 1
    ═══════════════════════════════════════════════════════ --}}
    <div class="form-container" id="page-1">

        {{-- No. Usulan --}}
        <div class="field-row mb-3">
            <span class="field-label">No. Usulan</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialPm->proposal_number ?? '' }}</span>
            <span class="field-note">(diisi oleh Sub-Bagian R&D)</span>
        </div>

        {{-- Nama Bahan Kemas & Pemasok --}}
        <div class="field-row mb-2">
            <span class="field-label">Nama Bahan Kemas</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialPm->packaging_material }}</span>
        </div>
        <div class="field-row mb-3">
            <span class="field-label">Pemasok</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialPm->supplier }}</span>
        </div>

        {{-- A. DATA BAHAN KEMAS --}}
        <p class="section-title mb-2">A. DATA BAHAN KEMAS <span class="section-subtitle">(diisi oleh packaging development staff)</span></p>
        <ul class="numbered-list mb-3">
            <li>
                <span class="num">1.</span>
                <span class="item-label">Digunakan untuk produk</span>
                <span class="item-sep">:</span>
                <span class="item-value">{{ $trialPm->product_use }}</span>
            </li>
            <li>
                <span class="num">2.</span>
                <span class="item-label">Ditrial pada produk</span>
                <span class="item-sep">:</span>
                <span class="item-value">{{ $trialPm->product_trial }}</span>
            </li>
            <li>
                <span class="num">3.</span>
                <span class="item-label">Jumlah sampel yang ditrial</span>
                <span class="item-sep">:</span>
                <span class="item-value">{{ $trialPm->trial_sample_quantity }}</span>
            </li>
            <li>
                <span class="num">4.</span>
                <span class="item-label">Pemasok lama</span>
                <span class="item-sep">:</span>
                <span class="item-value">{{ $trialPm->old_supplier ?? '' }}</span>
            </li>
            <li>
                <span class="num">5.</span>
                <span class="item-label">Perbedaan dengan eksis</span>
                <span class="item-sep">:</span>
                <span class="item-value">{{ $trialPm->difference_with_existing ?? '' }}</span>
            </li>
        </ul>

        {{-- B. SPESIFIKASI BAHAN KEMAS --}}
        <p class="section-title mb-2">B. SPESIFIKASI BAHAN KEMAS</p>
        <table class="form-table mb-3">
            <thead>
                <tr>
                    <th style="width: 12mm;">No</th>
                    <th>Deskripsi Spesifikasi Bahan Pengemas</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($trialPm->specifications) && count($trialPm->specifications) > 0)
                    @foreach($trialPm->specifications as $index => $spec)
                    <tr>
                        <td class="col-center">{{ $index + 1 }}</td>
                        <td>{{ $spec }}</td>
                    </tr>
                    @endforeach
                @endif
                {{-- Filler empty rows to fill the table area --}}
                @php $specCount = is_array($trialPm->specifications) ? count($trialPm->specifications) : 0; @endphp
                @for($i = 0; $i < max(0, 6 - $specCount); $i++)
                <tr class="empty-row"><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>

        {{-- C. PELAKSANAAN TRIAL DAN HASIL TRIAL --}}
        <p class="section-title mb-2">C. PELAKSANAAN TRIAL DAN HASIL TRIAL <span class="section-subtitle">* (diisi oleh R&D, QC, Dept.Produksi, dan Dept.Engineering)</span></p>
        <table class="form-table mb-2">
            <thead>
                <tr class="merged-header">
                    <th rowspan="2" style="width: 10mm;">No.</th>
                    <th rowspan="2" style="width: 30mm;">Mesin Pengemas</th>
                    <th colspan="2">Parameter</th>
                    <th colspan="2">Waktu</th>
                    <th colspan="2">Hasil</th>
                    <th colspan="3">Paraf</th>
                </tr>
                <tr class="merged-header">
                    <th style="width: 22mm;">Setting</th>
                    <th style="width: 22mm;">Aktual</th>
                    <th style="width: 18mm;">Mulai</th>
                    <th style="width: 18mm;">Selesai</th>
                    <th style="width: 14mm;">Reject</th>
                    <th style="width: 14mm;">Baik</th>
                    <th style="width: 14mm;">Prod</th>
                    <th style="width: 14mm;">Eng</th>
                    <th style="width: 14mm;">QC</th>
                </tr>
            </thead>
            <tbody>
                @php $exeCount = is_array($trialPm->executions) ? count($trialPm->executions) : 0; @endphp
                @if($exeCount > 0)
                    @foreach($trialPm->executions as $index => $exe)
                    <tr>
                        <td class="col-center">{{ $index + 1 }}</td>
                        <td>{{ $exe['machine'] ?? '' }}</td>
                        <td>{{ $exe['setting'] ?? '' }}</td>
                        <td>{{ $exe['actual'] ?? '' }}</td>
                        <td class="col-center">{{ $exe['start_time'] ?? '' }}</td>
                        <td class="col-center">{{ $exe['end_time'] ?? '' }}</td>
                        <td class="col-center">{{ $exe['reject'] ?? '0' }}</td>
                        <td class="col-center">{{ $exe['good'] ?? '0' }}</td>
                        <td class="col-center">
                            @if(!empty($exe['paraf_prod']))
                                @if(!empty($exe['paraf_prod_signature']))
                                    <img src="{{ $resolveSigUrl($exe['paraf_prod_signature']) }}" class="sig-image" alt="Paraf">
                                @elseif(!empty($exe['paraf_prod_signed_name']))
                                    <span class="font-signature" style="font-size: 10pt;">{{ $exe['paraf_prod_signed_name'] }}</span>
                                @else
                                    &#10003;
                                @endif
                            @endif
                        </td>
                        <td class="col-center">
                            @if(!empty($exe['paraf_eng']))
                                @if(!empty($exe['paraf_eng_signature']))
                                    <img src="{{ $resolveSigUrl($exe['paraf_eng_signature']) }}" class="sig-image" alt="Paraf">
                                @elseif(!empty($exe['paraf_eng_signed_name']))
                                    <span class="font-signature" style="font-size: 10pt;">{{ $exe['paraf_eng_signed_name'] }}</span>
                                @else
                                    &#10003;
                                @endif
                            @endif
                        </td>
                        <td class="col-center">
                            @if(!empty($exe['paraf_qc']))
                                @if(!empty($exe['paraf_qc_signature']))
                                    <img src="{{ $resolveSigUrl($exe['paraf_qc_signature']) }}" class="sig-image" alt="Paraf">
                                @elseif(!empty($exe['paraf_qc_signed_name']))
                                    <span class="font-signature" style="font-size: 10pt;">{{ $exe['paraf_qc_signed_name'] }}</span>
                                @else
                                    &#10003;
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @endif
                {{-- Filler empty rows for page 1 (at least 3 rows) --}}
                @for($i = 0; $i < max(3, 4 - $exeCount); $i++)
                <tr class="empty-row">
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
            </tbody>
        </table>

    </div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE 2
    ════════════════════════════════════════════════════════ --}}
    <div class="form-container page-break" id="page-2">

        {{-- Continuation of Section C table (filler rows) --}}
        <table class="form-table mb-2">
            <thead>
                <tr class="merged-header">
                    <th rowspan="2" style="width: 10mm;">No.</th>
                    <th rowspan="2" style="width: 30mm;">Mesin Pengemas</th>
                    <th colspan="2">Parameter</th>
                    <th colspan="2">Waktu</th>
                    <th colspan="2">Hasil</th>
                    <th colspan="3">Paraf</th>
                </tr>
                <tr class="merged-header">
                    <th style="width: 22mm;">Setting</th>
                    <th style="width: 22mm;">Aktual</th>
                    <th style="width: 18mm;">Mulai</th>
                    <th style="width: 18mm;">Selesai</th>
                    <th style="width: 14mm;">Reject</th>
                    <th style="width: 14mm;">Baik</th>
                    <th style="width: 14mm;">Prod</th>
                    <th style="width: 14mm;">Eng</th>
                    <th style="width: 14mm;">QC</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 5; $i++)
                <tr class="empty-row">
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <p class="text-italic mb-3" style="font-size: 9pt;">*Catatan (lampirkan foto proses dan hasil trial)</p>

        {{-- D. PEMBAHASAN HASIL TRIAL --}}
        <p class="section-title mb-2">D. PEMBAHASAN HASIL TRIAL <span class="section-subtitle">(diisi oleh R&D)</span></p>
        <table class="form-table mb-2">
            <thead>
                <tr>
                    <th style="width: 10mm;">No</th>
                    <th style="width: 45mm;">Evaluasi</th>
                    <th style="width: 45mm;">Analisis Resiko</th>
                    <th style="width: 45mm;">Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($trialPm->discussion_rows) && count($trialPm->discussion_rows) > 0)
                    @foreach($trialPm->discussion_rows as $index => $disc)
                    <tr>
                        <td class="col-center">{{ $index + 1 }}</td>
                        <td>{{ $disc['evaluation'] ?? '' }}</td>
                        <td>{{ $disc['risk_analysis'] ?? '' }}</td>
                        <td>{{ $disc['recommendation'] ?? '' }}</td>
                    </tr>
                    @endforeach
                @endif
                @php $discCount = is_array($trialPm->discussion_rows) ? count($trialPm->discussion_rows) : 0; @endphp
                @for($i = 0; $i < max(2, 4 - $discCount); $i++)
                <tr class="empty-row"><td></td><td></td><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>

    </div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE 3
    ════════════════════════════════════════════════════════ --}}
    <div class="form-container page-break" id="page-3">

        {{-- E. KESIMPULAN --}}
        <p class="section-title mb-2">E. KESIMPULAN <span class="section-subtitle">(diisi oleh R&D, QC, Dept.Produksi, dan Dept.Engineering)</span></p>
        <table class="form-table mb-3">
            <thead>
                <tr>
                    <th style="width: 30mm;">Departemen</th>
                    <th style="width: 50mm;">Kesimpulan</th>
                    <th>Informasi Lain</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $deptOrder = ['rd', 'qc', 'production', 'engineering'];
                    $deptLabels = ['rd' => 'RD', 'qc' => 'QC', 'production' => 'Produksi', 'engineering' => 'Engineering'];
                    $approvals = $trialPm->departmentApprovals->keyBy('department');
                @endphp
                @foreach($deptOrder as $dept)
                <tr>
                    <td><span class="text-italic">{{ $deptLabels[$dept] }}</span></td>
                    <td>
                        @php $app = $approvals[$dept] ?? null; @endphp
                        @if($app && $app->approved_by)
                            @if($app->is_approved)
                                <strong>Bisa digunakan</strong> / Tidak bisa digunakan**
                            @else
                                Bisa digunakan / <strong>Tidak bisa digunakan**</strong>
                            @endif
                        @else
                            Bisa digunakan / Tidak bisa digunakan**
                        @endif
                    </td>
                    <td>
                        @if($app && $app->notes)
                            {{ $app->notes }}
                            @if($app->approver)
                                <br><span style="font-size: 8pt; color: #555;">{{ $app->approver->name }} &middot; {{ $app->approved_at?->format('d/m/Y') }}</span>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Signature Table --}}
        <table class="sig-table">
            <thead>
                <tr>
                    <th style="width: 30mm;"></th>
                    <th style="width: 55mm;">Nama</th>
                    <th>Tanda Tangan / Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-bold">Disusun oleh</td>
                    <td>
                        <br><br>
                        <span class="text-italic">(Staff Packaging Development)</span>
                        <br>
                        <strong>{{ $trialPm->creator?->name ?? '—' }}</strong>
                    </td>
                    <td>
                        @if($trialPm->creator?->signature_path)
                            <img src="{{ $resolveSigUrl($trialPm->creator->signature_path) }}" class="sig-image" alt="Signature">
                        @else
                            <span class="font-signature" style="font-size: 16pt;">{{ $trialPm->creator?->name ?? '' }}</span>
                        @endif
                        <br>
                        {{ $trialPm->created_at?->format('d/m/Y') ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td class="text-bold">Diperiksa oleh</td>
                    <td>
                        <br><br>
                        <span class="text-italic">(Operasional Manager)</span>
                        <br>
                        <strong>{{ $trialPm->operationalManager?->name ?? '—' }}</strong>
                    </td>
                    <td>
                        @if($trialPm->operationalManager?->signature_path)
                            <img src="{{ $resolveSigUrl($trialPm->operationalManager->signature_path) }}" class="sig-image" alt="Signature">
                        @else
                            <span class="font-signature" style="font-size: 16pt;">{{ $trialPm->operationalManager?->name ?? '' }}</span>
                        @endif
                        <br>
                        {{ $trialPm->approved_at?->format('d/m/Y') ?? '' }}
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE NUMBER SCRIPT (updates footer per page)
    ════════════════════════════════════════════════════════ --}}
    <script>
        // For screen preview: show page 1 footer by default.
        // When printing, browsers with fixed footer support will repeat it.
        // For multi-page preview, we use CSS counters approach via separate footers per page.
    </script>

    {{-- Per-page footer overrides using CSS counters --}}
    <style>
        /* Hide the global footer page number and use per-page counters */
        @media print {
            #page-1 ~ .print-footer .page-number,
            #page-2 ~ .print-footer .page-number {
                /* handled by browser */
            }
        }
        /* Per-page footer text via pseudo-elements on each page container */
        #page-1::after {
            content: "Halaman 1 dari 3";
            position: fixed;
            bottom: 2mm;
            right: 12mm;
            font-size: 8pt;
            color: #333;
            background: #fff;
            padding: 0 2mm;
        }
        #page-2::after {
            content: "Halaman 2 dari 3";
            position: fixed;
            bottom: 2mm;
            right: 12mm;
            font-size: 8pt;
            color: #333;
            background: #fff;
            padding: 0 2mm;
        }
        #page-3::after {
            content: "Halaman 3 dari 3";
            position: fixed;
            bottom: 2mm;
            right: 12mm;
            font-size: 8pt;
            color: #333;
            background: #fff;
            padding: 0 2mm;
        }
        /* Hide the default footer page number since we use per-page */
        .print-footer .page-number { display: none; }
    </style>

</body>
</html>
