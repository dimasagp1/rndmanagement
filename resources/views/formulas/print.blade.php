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
    <title>Cetak Formula - {{ $formula->code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* Standard A4 Portrait Page Setup */
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
            padding: 0;
            position: relative;
        }

        /* ── Fixed Header & Footer (Root Level) ───────────────── */
        .print-header {
            position: fixed; top: 0; left: 0; right: 0; height: 14mm;
            display: flex; align-items: center;
            border-bottom: 2px solid #000;
            background: #fff; z-index: 100;
        }
        .print-header .logo-area { display: flex; align-items: center; gap: 3mm; width: 35%; }
        .print-header .logo-icon {
            width: 10mm; height: 10mm; background: #1a6b3c; border-radius: 1.5mm;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 900; font-size: 7pt; letter-spacing: 0.5pt;
        }
        .print-header .logo-text { font-size: 9pt; font-weight: 700; color: #1a6b3c; letter-spacing: 1pt; }
        .print-header .title-area { flex: 1; text-align: center; font-size: 10pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3pt; }
        .print-header .form-number { width: 35%; text-align: right; font-size: 9pt; font-weight: 400; }

        .print-footer {
            position: fixed; bottom: 0; left: 0; right: 0; height: 16mm;
            display: flex; align-items: flex-end; justify-content: space-between;
            background: #fff; z-index: 100;
        }
        .print-footer .lamp-text { font-size: 8pt; color: #333; }
        .print-footer .page-number { font-size: 8pt; color: #333; }
        .page-num-val::after { content: counter(page); }

        /* ── Watermark ────────────────────────────── */
        .watermark {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt; font-weight: 900;
            color: rgba(0,0,0,0.06);
            letter-spacing: 8pt;
            z-index: 50;
            pointer-events: none;
            white-space: nowrap;
        }

        /* ── Typography & Fields ───────────────────── */
        .section-title { font-size: 10pt; font-weight: 700; text-transform: uppercase; margin-bottom: 2mm; letter-spacing: 0.3pt; }
        .section-subtitle { font-size: 8pt; font-style: italic; font-weight: 400; color: #333; }
        .font-signature { font-family: 'Dancing Script', cursive; }

        .field-row { display: flex; align-items: baseline; margin-bottom: 1.5mm; font-size: 10pt; }
        .field-label { font-weight: 700; min-width: 42mm; flex-shrink: 0; }
        .info-label { font-weight: 700; min-width: 30mm; flex-shrink: 0; }
        .field-sep { margin: 0 2mm; }
        .field-value { flex: 1; border-bottom: 1px dotted #000; min-height: 4mm; padding-bottom: 0.5mm; }

        /* ── Tables ───────────────────────────────── */
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 1.5mm 2mm; }
        table.data-table th { background: #d9d9d9; font-weight: 700; text-align: center; }
        table.data-table td { vertical-align: middle; }

        .sig-image { height: 8mm; width: auto; max-width: 24mm; object-fit: contain; display: inline-block; vertical-align: middle; }

        /* ── Form Containers ──────────────────────── */
        .form-container { border: 1.5px solid #000; padding: 5mm 5mm; }

        /* ── Utility ──────────────────────────────── */
        .text-bold { font-weight: 700; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 2mm; }
        .mt-3 { margin-top: 3mm; }
        .mb-2 { margin-bottom: 2mm; }
        .mb-3 { margin-bottom: 3mm; }

        .checkbox-label { display: inline-flex; align-items: center; gap: 1.5mm; font-size: 8pt; margin-right: 4mm; }
        .checkbox-box { display: inline-block; width: 3mm; height: 3mm; border: 1px solid #000; text-align: center; line-height: 3mm; font-size: 7pt; }

        /* Page Layouts */
        .page-1, .page-2 {
            width: 100%;
            margin-top: 18mm;
            margin-bottom: 18mm;
        }

        .page-2 {
            page-break-before: always;
            break-before: page;
        }

        /* Specific Table Font Sizes */
        .page-1 table.data-table { font-size: 8pt; }
        .page-2 table.data-table { font-size: 9pt; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- CONFIDENTIAL Watermark --}}
    <div class="watermark">CONFIDENTIAL</div>

    {{-- ═══════════════════════════════════════════════════════
         GLOBAL FIXED HEADER & FOOTER
    ════════════════════════════════════════════════════════ --}}
    <div class="print-header">
        <div class="logo-area">
            @if(setting('print_logo'))
                <img src="{{ asset('storage/' . setting('print_logo')) }}" style="height: 10mm; width: auto; max-width: 28mm; object-fit: contain; vertical-align: middle;">
            @else
                <div class="logo-icon">HT</div>
                <span class="logo-text">{{ strtoupper(setting('app_name', 'HERBATECH')) }}</span>
            @endif
        </div>
        <div class="title-area">FORM FORMULASI RM</div>
        <div class="form-number">No. CM-04/RD/001.01</div>
    </div>

    <div class="print-footer">
        <span class="lamp-text">LAMP. A PR-04/RD/001.01</span>
        <span class="page-number">Halaman <span class="page-num-val"></span> dari 2</span>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         PAGE 1: COST & HPP FORMULA SHEET (PORTRAIT)
    ════════════════════════════════════════════════════════ -->
    <div class="page-1">
        <div class="form-container">
            {{-- INFORMATION --}}
            <div class="section-title">INFORMATION</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6mm;">
                <div>
                    <div class="field-row">
                        <span class="info-label">Product Name</span>
                        <span class="field-sep">:</span>
                        <span class="field-value">{{ $formula->name }}</span>
                    </div>
                    <div class="field-row">
                        <span class="info-label">Formula Code</span>
                        <span class="field-sep">:</span>
                        <span class="field-value">{{ $formula->code }}</span>
                    </div>
                </div>
                <div>
                    <div class="field-row">
                        <span class="info-label">Date</span>
                        <span class="field-sep">:</span>
                        <span class="field-value">{{ $formula->formula_date ? $formula->formula_date->format('d M Y') : ($formula->created_at->format('d M Y')) }}</span>
                    </div>
                    <div class="field-row">
                        <span class="info-label">Type</span>
                        <span class="field-sep">:</span>
                        <span class="field-value" style="border-bottom: none;">
                            <span class="checkbox-label"><span class="checkbox-box">{{ $formula->formula_type === 'existing' ? 'X' : '' }}</span> Existing</span>
                            <span class="checkbox-label"><span class="checkbox-box">{{ $formula->formula_type === 'new_product' ? 'X' : '' }}</span> New Product</span>
                            <span class="checkbox-label"><span class="checkbox-box">{{ $formula->formula_type === 'substitution' ? 'X' : '' }}</span> Substitution</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-2"></div>

            {{-- MATERIAL TABLE --}}
            <div class="section-title">Material</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 6mm;">No</th>
                        <th>Material</th>
                        <th>Supplier</th>
                        <th>Harga/kg</th>
                        <th>Harga/g</th>
                        <th>Persentase</th>
                        <th>{{ (float)$formula->target_dose_a }} {{ $formula->target_dose_a_unit ?? 'g' }}</th>
                        <th>{{ (float)$formula->target_dose_b }} {{ $formula->target_dose_b_unit ?? 'g' }}</th>
                        <th>{{ $formula->target_sachet }} {{ $formula->target_sachet_unit ?? 'sachet' }}</th>
                        <th>HPP RM</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formula->materials->sortByDesc('percentage') as $i => $fm)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $fm->material?->name ?? '' }}</td>
                        <td>{{ $fm->supplier?->name ?? '' }}</td>
                        <td class="text-right">{{ $fm->price_per_kg ? number_format($fm->price_per_kg, 0) : '' }}</td>
                        <td class="text-right">{{ $fm->price_per_gram ? 'Rp' . number_format($fm->price_per_gram, 2) : '' }}</td>
                        <td class="text-right">{{ number_format($fm->percentage, 3) }}%</td>
                        <td class="text-right">{{ $fm->dose_2g ? number_format($fm->dose_2g, 3) : '' }}</td>
                        <td class="text-right">{{ $fm->dose_05g ? number_format($fm->dose_05g, 3) : '' }}</td>
                        <td class="text-right">{{ $fm->sachet_30 ?? '' }}</td>
                        <td class="text-right">{{ $fm->hpp_rm ? 'Rp' . number_format($fm->hpp_rm, 2) : '' }}</td>
                    </tr>
                    @endforeach
                    <tr style="background: #f0f0f0; font-weight: 700;">
                        <td colspan="5" class="text-right" style="padding-right: 3mm;">Total Komposisi</td>
                        <td class="text-right">{{ number_format($formula->total_percentage, 2) }}%</td>
                        <td colspan="4"></td>
                    </tr>
                </tbody>
            </table>

            @if($formula->preparation_method)
            <p style="font-size: 8pt; margin-top: 1.5mm;"><em>Cara Penyajian: {{ $formula->preparation_method }}</em></p>
            @endif

            <div class="mt-3"></div>

            {{-- BOTTOM GRID (Stage, Result, Note) --}}
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.5fr; gap: 4mm;">
                {{-- PRODUCT DEVELOPMENT STAGE --}}
                <div>
                    <div class="section-title" style="font-size: 8.5pt;">PRODUCT DEVELOPMENT STAGE</div>
                    <table class="data-table" style="font-size: 7.5pt;">
                        <thead>
                            <tr>
                                <th>Stage</th>
                                <th style="width: 10mm;">X</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Product Form', 'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test'] as $stage)
                            <tr>
                                <td>{{ $stage }}</td>
                                <td class="text-center">{{ $formula->development_stage === $stage ? 'X' : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- RESULT --}}
                <div>
                    <div class="section-title" style="font-size: 8.5pt;">Result</div>
                    <table class="data-table" style="font-size: 8pt;">
                        <tbody>
                            <tr>
                                <td style="background: {{ $formula->result === 'Approved' ? '#d4edda' : '#fff' }}; font-size: 8.5pt;">
                                    <span class="checkbox-box" style="margin-right: 2mm;">{{ $formula->result === 'Approved' ? 'X' : '' }}</span> Approved
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 8.5pt;">
                                    <span class="checkbox-box" style="margin-right: 2mm;">{{ $formula->result === 'Need Improvement' ? 'X' : '' }}</span> Need Imp.
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 8.5pt;">
                                    <span class="checkbox-box" style="margin-right: 2mm;">{{ $formula->result === 'Rejected' ? 'X' : '' }}</span> Rejected
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- NOTE --}}
                <div>
                    <div class="section-title" style="font-size: 8.5pt;">Note</div>
                    <div style="border: 1px solid #000; padding: 1.5mm; min-height: 24mm; font-size: 8pt; white-space: pre-line;">{{ $formula->notes ?? '' }}</div>
                </div>
            </div>

            <div class="mt-3"></div>

            {{-- SIGNATURES --}}
            <table class="data-table" style="font-size: 8pt;">
                <thead>
                    <tr>
                        <th style="width: 22%;"></th>
                        <th style="width: 28%;">Nama</th>
                        <th style="width: 50%;">Tanda Tangan / Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-bold text-center" style="vertical-align: middle;">Disusun oleh</td>
                        <td style="vertical-align: middle;">
                            {{ $formula->creator?->name ?? '' }}
                            <br><span style="font-size: 7pt; color: #666;">(Staff R&D)</span>
                        </td>
                        <td style="vertical-align: middle; font-size: 8pt;">
                            @if($formula->creator?->signature_path)
                            <img src="{{ $resolveSigUrl($formula->creator->signature_path) }}" class="sig-image" alt="Signature"><br>
                            @endif
                            Tanggal: {{ $formula->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-bold text-center" style="vertical-align: middle;">Diperiksa oleh</td>
                        <td style="vertical-align: middle;">
                            {{ $formula->operationalManager?->name ?? '' }}
                            <br><span style="font-size: 7pt; color: #666;">(Operational Manager)</span>
                        </td>
                        <td style="vertical-align: middle; font-size: 8pt;">
                            @if($formula->operationalManager?->signature_path)
                            <img src="{{ $resolveSigUrl($formula->operationalManager->signature_path) }}" class="sig-image" alt="Signature"><br>
                            @endif
                            Tanggal: {{ in_array($formula->approval_status, ['Pending Tahap 2', 'Approved', 'Rejected']) ? $formula->updated_at->format('d M Y') : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-bold text-center" style="vertical-align: middle;">Disetujui oleh</td>
                        <td style="vertical-align: middle;">
                            {{ $formula->generalManager?->name ?? '' }}
                            <br><span style="font-size: 7pt; color: #666;">(General Manager)</span>
                        </td>
                        <td style="vertical-align: middle; font-size: 8pt;">
                            @if($formula->generalManager?->signature_path)
                            <img src="{{ $resolveSigUrl($formula->generalManager->signature_path) }}" class="sig-image" alt="Signature"><br>
                            @endif
                            Tanggal: {{ $formula->approved_at?->format('d M Y') ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <!-- ═══════════════════════════════════════════════════════
         PAGE 2: RECIPE SHEET (PORTRAIT)
    ════════════════════════════════════════════════════════ -->
    <div class="page-2">
        <div class="form-container">
            {{-- Section A: INFORMASI FORMULA --}}
            <div class="section-title">A. INFORMASI FORMULA <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
            <div class="field-row">
                <span class="field-label">Kode Formula</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->code }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Nama Produk</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->name }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Versi</span>
                <span class="field-sep">:</span>
                <span class="field-value">V{{ $formula->version }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Tahapan Pengembangan</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->development_stage }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Status Approval</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->approval_status }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Dibuat Oleh</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->creator?->name ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Tanggal Dibuat</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->created_at->isoFormat('D MMM Y') }}</span>
            </div>

            <div class="mt-3 mb-3"></div>

            {{-- Section B: KOMPOSISI MATERIAL --}}
            <div class="section-title">B. KOMPOSISI MATERIAL <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8mm;">No</th>
                        <th>Nama Bahan</th>
                        <th>Aplikasi Penggunaan</th>
                        <th>Supplier</th>
                        <th>Bentuk Sediaan</th>
                        <th style="width: 24mm;">Persentase (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $materials = $formula->materials->sortByDesc('percentage');
                        $totalPct = $formula->total_percentage;
                    @endphp
                    @foreach($materials as $i => $fm)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $fm->material?->name ?? '' }}</td>
                        <td>{{ $fm->material?->description ?? '' }}</td>
                        <td>{{ $fm->supplier?->name ?? '' }}</td>
                        <td>{{ $fm->material?->type ?? '' }}</td>
                        <td class="text-center">{{ number_format($fm->percentage, 2) }}%</td>
                    </tr>
                    @endforeach
                    {{-- Total row --}}
                    <tr style="background: #f0f0f0; font-weight: 700;">
                        <td colspan="5" class="text-right">TOTAL</td>
                        <td class="text-center">{{ number_format($totalPct, 2) }}%</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-3 mb-3"></div>

            {{-- Section C: APPROVAL --}}
            <div class="section-title">C. STATUS APPROVAL</div>
            <div class="field-row">
                <span class="field-label">Status</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->approval_status }}</span>
            </div>
            @if($formula->approval_status === 'Approved')
            <div class="field-row">
                <span class="field-label">Tanggal Disetujui</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->approved_at?->isoFormat('D MMM Y') ?? '—' }}</span>
            </div>
            @endif
            @if($formula->rejection_notes)
            <div class="field-row">
                <span class="field-label">Catatan Penolakan</span>
                <span class="field-sep">:</span>
                <span class="field-value">{{ $formula->rejection_notes }}</span>
            </div>
            @endif

            <div class="mt-3 mb-3"></div>

            {{-- Section D: TANDA TANGAN --}}
            <div class="section-title">D. TANDA TANGAN</div>
            <table class="data-table" style="font-size: 9pt;">
                <thead>
                    <tr>
                        <th style="width: 28%;"></th>
                        <th style="width: 32%;">Nama</th>
                        <th style="width: 40%;">Tanda Tangan / Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Disusun oleh --}}
                    <tr>
                        <td class="text-bold text-center" style="vertical-align: middle;">Disusun oleh</td>
                        <td style="vertical-align: middle;">
                            {{ $formula->creator?->name ?? '' }}
                            <br><span style="font-size: 8pt; color: #666;">(Staff R&D)</span>
                        </td>
                        <td style="vertical-align: middle; font-size: 8pt;">
                            @if($formula->creator?->signature_path)
                            <img src="{{ $resolveSigUrl($formula->creator->signature_path) }}" class="sig-image" alt="Signature"><br>
                            @endif
                            Tanggal: {{ $formula->created_at->isoFormat('D MMM Y') }}
                        </td>
                    </tr>
                    {{-- Diperiksa oleh --}}
                    <tr>
                        <td class="text-bold text-center" style="vertical-align: middle;">Diperiksa oleh</td>
                        <td style="vertical-align: middle;">
                            {{ $formula->operationalManager?->name ?? '' }}
                            <br><span style="font-size: 8pt; color: #666;">(Operational Manager)</span>
                        </td>
                        <td style="vertical-align: middle; font-size: 8pt;">
                            @if($formula->operationalManager?->signature_path)
                            <img src="{{ $resolveSigUrl($formula->operationalManager->signature_path) }}" class="sig-image" alt="Signature"><br>
                            @endif
                            Tanggal: {{ in_array($formula->approval_status, ['Pending Tahap 2', 'Approved', 'Rejected']) ? $formula->updated_at->isoFormat('D MMM Y') : '—' }}
                        </td>
                    </tr>
                    {{-- Disetujui oleh --}}
                    <tr>
                        <td class="text-bold text-center" style="vertical-align: middle;">Disetujui oleh</td>
                        <td style="vertical-align: middle;">
                            {{ $formula->generalManager?->name ?? '' }}
                            <br><span style="font-size: 8pt; color: #666;">(General Manager)</span>
                        </td>
                        <td style="vertical-align: middle; font-size: 8pt;">
                            @if($formula->generalManager?->signature_path)
                            <img src="{{ $resolveSigUrl($formula->generalManager->signature_path) }}" class="sig-image" alt="Signature"><br>
                            @endif
                            Tanggal: {{ $formula->approved_at?->isoFormat('D MMM Y') ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
