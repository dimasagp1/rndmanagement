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
    <title>Cetak Form Trial RM - {{ $trialRm->code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A4 portrait; margin: 14mm 12mm 20mm 12mm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
            background: #fff;
            padding-top: 22mm;
            padding-bottom: 18mm;
        }

        /* ── Fixed Header ─────────────────────────── */
        .print-header {
            position: fixed; top: 0; left: 0; right: 0; height: 14mm;
            display: flex; align-items: center;
            border-bottom: 2px solid #000;
            padding: 0 2mm; background: #fff; z-index: 100;
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

        /* ── Fixed Footer ─────────────────────────── */
        .print-footer {
            position: fixed; bottom: 0; left: 0; right: 0; height: 16mm;
            display: flex; align-items: flex-end; justify-content: space-between;
            padding: 0 2mm 2mm 2mm; background: #fff; z-index: 100;
        }
        .print-footer .lamp-text { font-size: 8pt; color: #333; }
        .print-footer .page-number { font-size: 8pt; color: #333; }

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

        /* ── Typography ───────────────────────────── */
        .section-title { font-size: 10pt; font-weight: 700; text-transform: uppercase; margin-bottom: 2mm; letter-spacing: 0.3pt; }
        .section-subtitle { font-size: 8pt; font-style: italic; font-weight: 400; color: #333; }
        .font-signature { font-family: 'Dancing Script', cursive; }

        /* ── Field Rows ───────────────────────────── */
        .field-row { display: flex; align-items: baseline; margin-bottom: 1.5mm; font-size: 10pt; }
        .field-label { font-weight: 700; min-width: 42mm; flex-shrink: 0; }
        .field-sep { margin: 0 2mm; }
        .field-value { flex: 1; border-bottom: 1px dotted #000; min-height: 4mm; padding-bottom: 0.5mm; }

        /* ── Tables ───────────────────────────────── */
        table.data-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 1.5mm 2mm; }
        table.data-table th { background: #d9d9d9; font-weight: 700; text-align: center; }
        table.data-table td { vertical-align: top; }

        /* ── Signature ────────────────────────────── */
        .sig-image { height: 8mm; width: auto; max-width: 24mm; object-fit: contain; display: inline-block; vertical-align: middle; }

        /* ── Form Container ───────────────────────── */
        .form-container { border: 1.5px solid #000; padding: 4mm 4mm 3mm 4mm; min-height: 240mm; }
        .page-break { page-break-before: always; break-before: page; }

        /* ── Utility ──────────────────────────────── */
        .text-bold { font-weight: 700; }
        .text-center { text-align: center; }
        .mt-2 { margin-top: 2mm; }
        .mt-3 { margin-top: 3mm; }
        .mb-2 { margin-bottom: 2mm; }
        .mb-3 { margin-bottom: 3mm; }
        .empty-row td { height: 8mm; }

        /* ── Print color adjust ───────────────────── */
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page-2-footer { display: none; }
        }
    </style>
</head>
<body>

    {{-- CONFIDENTIAL Watermark --}}
    <div class="watermark">CONFIDENTIAL</div>

    {{-- ═══════════════════════════════════════════════════════
         FIXED HEADER
    ════════════════════════════════════════════════════════ --}}
    <div class="print-header">
        <div class="logo-area">
            @if(setting('print_logo'))
                <img src="{{ asset('storage/' . setting('print_logo')) }}" style="height: 10mm; width: auto; max-width: 28mm; object-fit: contain; vertical-align: middle;">
            @else
                <div class="logo-icon">HT</div>
            @endif
            <span class="logo-text">{{ strtoupper(setting('app_name', 'HERBATECH')) }}</span>
        </div>
        <div class="title-area">FORM CATATAN TRIAL<br>PRODUK/PROSES</div>
        <div class="form-number">No. CM-05/RD/001-B.03</div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         FIXED FOOTER
    ════════════════════════════════════════════════════════ --}}
    <div class="print-footer">
        <span class="lamp-text">LAMP. B PR-05/RD/001.03</span>
        <span class="page-number">Halaman 1 dari 2</span>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE 1: No. Form, A, B, C
    ════════════════════════════════════════════════════════ --}}
    <div class="form-container">

        {{-- No. Form --}}
        <div class="field-row mb-3">
            <span class="field-label">No. Form</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialRm->code }}</span>
        </div>

        {{-- Section A: IDENTITAS SAMPEL TRIAL --}}
        <div class="section-title">A. IDENTITAS SAMPEL TRIAL <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
        <div class="field-row">
            <span class="field-label">Nama Produk</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialRm->formula?->name ?? '' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Kode Sampel Trial</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialRm->sample_identity }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Jumlah Bets Trial</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialRm->batch_qty ?? '' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Rancangan Kemasan</span>
            <span class="field-sep">:</span>
            <span class="field-value">{{ $trialRm->packaging_design ?? '' }}</span>
        </div>

        <div class="mt-3 mb-3"></div>

        {{-- Section B: TUJUAN TRIAL --}}
        <div class="section-title">B. TUJUAN TRIAL <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
        <div style="border: 1px solid #000; padding: 2mm; min-height: 20mm; white-space: pre-line;">
{{ $trialRm->trial_objective ?? '' }}</div>

        <div class="mt-3 mb-3"></div>

        {{-- Section C: FORMULA TRIAL --}}
        <div class="section-title">C. FORMULA TRIAL <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 8mm;">No</th>
                    <th rowspan="2">Nama Bahan</th>
                    <th rowspan="2">Aplikasi Penggunaan</th>
                    <th rowspan="2">Supplier</th>
                    <th rowspan="2">Bentuk Sediaan</th>
                    <th colspan="3">Formula</th>
                </tr>
                <tr>
                    <th style="width: 14mm;">F1</th>
                    <th style="width: 14mm;">F2</th>
                    <th style="width: 14mm;">F3</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $materials = $trialRm->formula?->materials ?? collect();
                    $rowCount = max($materials->count(), 16);
                @endphp
                @for($i = 0; $i < $rowCount; $i++)
                    @php $fm = $materials->values()->get($i); @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $fm?->material?->name ?? '' }}</td>
                        <td>{{ $fm?->material?->description ?? '' }}</td>
                        <td>{{ $fm?->supplier?->name ?? '' }}</td>
                        <td>{{ $fm?->material?->type ?? '' }}</td>
                        <td class="text-center">{{ $fm ? number_format($fm->percentage, 2) . '%' : '' }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE 2: D, E, F
    ════════════════════════════════════════════════════════ --}}
    <div class="page-break"></div>

    <div class="form-container">

        {{-- Section D: TAHAPAN PROSES --}}
        <div class="section-title">D. TAHAPAN PROSES <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
        <div style="border: 1px solid #000; padding: 2mm; min-height: 30mm; white-space: pre-line; font-size: 9pt;">{{ $trialRm->process_steps }}</div>

        <div class="mt-3 mb-3"></div>

        {{-- Section E: HASIL TRIAL --}}
        <div class="section-title">E. HASIL TRIAL <span class="section-subtitle">(diisi oleh Staff R&D)</span></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Parameter</th>
                    <th style="width: 30%;">Syarat</th>
                    <th style="width: 30%;">Hasil</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trialRm->verifications as $v)
                <tr>
                    <td>{{ $v->parameter_name }}</td>
                    <td>{{ $v->target_value }}</td>
                    <td>{{ $v->actual_value }}
                        @if($v->status)
                            <span style="font-size: 8pt; color: {{ $v->status === 'Pass' ? '#16a34a' : ($v->status === 'Fail' ? '#dc2626' : '#d97706') }};">({{ $v->status }})</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                @for($i = $trialRm->verifications->count(); $i < 7; $i++)
                <tr class="empty-row"><td></td><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>
        <p style="font-size: 8pt; margin-top: 1mm; font-style: italic;">Keterangan: Parameter Fisika, Kimia dan Mikrobiologi (pengujian dilakukan setelah parameter pemerian produk disetujui oleh Manager QR&D)</p>

        <div class="mt-3 mb-3"></div>

        {{-- Section F: KESIMPULAN --}}
        <div class="section-title">F. KESIMPULAN <span class="section-subtitle">(diisi oleh R&D)</span></div>
        <table class="data-table" style="font-size: 9pt;">
            <thead>
                <tr>
                    <th style="width: 28%;"></th>
                    <th style="width: 32%;">Nama</th>
                    <th style="width: 40%;">Tanda Tangan / Tanggal / Catatan</th>
                </tr>
            </thead>
            <tbody>
                {{-- Disusun oleh --}}
                <tr>
                    <td class="text-bold text-center" style="vertical-align: middle;">Disusun oleh</td>
                    <td style="vertical-align: middle;">
                        {{ $trialRm->creator?->name ?? '' }}
                        <br><span style="font-size: 8pt; color: #666;">(Staff R&D)</span>
                    </td>
                    <td style="vertical-align: middle; font-size: 8pt;">
                        @if($trialRm->creator?->signature_path)
                        <img src="{{ $resolveSigUrl($trialRm->creator->signature_path) }}" class="sig-image" alt="Signature"><br>
                        @endif
                        Tanggal: {{ $trialRm->created_at?->isoFormat('D MMM Y') ?? '—' }}
                    </td>
                </tr>
                {{-- Diperiksa oleh --}}
                <tr>
                    <td class="text-bold text-center" style="vertical-align: middle;">Diperiksa oleh</td>
                    <td style="vertical-align: middle;">
                        {{ $trialRm->operationalManager?->name ?? '' }}
                        <br><span style="font-size: 8pt; color: #666;">(Operational Manager)</span>
                    </td>
                    <td style="vertical-align: middle; font-size: 8pt;">
                        @if($trialRm->operationalManager?->signature_path)
                        <img src="{{ $resolveSigUrl($trialRm->operationalManager->signature_path) }}" class="sig-image" alt="Signature"><br>
                        @endif
                        Tanggal: {{ in_array($trialRm->approval_status, ['Pending Tahap 2', 'Approved', 'Rejected']) ? $trialRm->updated_at->isoFormat('D MMM Y') : '—' }}<br>
                        &#9744; Lanjut pengamatan stabilitas (DP/JP)<br>
                        <span style="margin-left: 12pt;">(lama pengamatan stabilitas) ................</span>
                    </td>
                </tr>
                {{-- Evaluasi --}}
                <tr>
                    <td class="text-bold text-center" style="vertical-align: middle;">
                        Evaluasi<br><span style="font-size: 8pt; font-style: italic; font-weight: 400;">(pilih salah satu)</span>
                    </td>
                    <td style="vertical-align: middle; font-size: 9pt;">
                        @if($trialRm->decision === 'Lulus')
                        <strong>&#9745; Approved : </strong><br>
                        &#9744; Reformulasi kembali :
                        @elseif($trialRm->decision === 'Reformulasi')
                        &#9744; Approved :<br>
                        <strong>&#9745; Reformulasi kembali : </strong>
                        @else
                        &#9744; Approved :<br>
                        &#9744; Reformulasi kembali :
                        @endif
                    </td>
                    <td style="vertical-align: middle; font-size: 8pt;">
                        &#9744; Lanjut perhitungan HPP (untuk RM) setelah formula approved
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Page 2 footer (screen preview only) --}}
        <div class="page-2-footer" style="position: fixed; bottom: 0; left: 0; right: 0; height: 16mm; display: flex; align-items: flex-end; justify-content: space-between; padding: 0 2mm 2mm 2mm; background: #fff; z-index: 100;">
            <span style="font-size: 8pt; color: #333;">LAMP. B PR-05/RD/001.03</span>
            <span style="font-size: 8pt; color: #333;">Halaman 2 dari 2</span>
        </div>
    </div>

    {{-- Script for page numbering in screen preview --}}
    <script>
        window.addEventListener('scroll', function() {
            var pages = document.querySelectorAll('.form-container');
            var footer = document.querySelector('.print-footer .page-number');
            if (!footer || pages.length < 2) return;
            var scrollTop = window.scrollY || document.documentElement.scrollTop;
            var page2Top = pages[1].offsetTop;
            if (scrollTop + window.innerHeight > page2Top + 100) {
                footer.textContent = 'Halaman 2 dari 2';
            } else {
                footer.textContent = 'Halaman 1 dari 2';
            }
        });
    </script>
</body>
</html>
