<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Log Book PM</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 landscape; margin: 8mm 6mm 10mm 6mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 7.5pt; color: #000; line-height: 1.2; background: #fff; padding-top: 15mm; }

        /* Fixed Header & Footer */
        .print-header { position: fixed; top: 0; left: 0; right: 0; height: 12mm; display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 1.5mm; background: #fff; z-index: 100; }
        .print-header .logo-area { display: flex; align-items: center; gap: 2mm; width: 30%; }
        .print-header .logo-icon { width: 8mm; height: 8mm; background: #1a6b3c; border-radius: 1mm; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 7pt; }
        .print-header .logo-text { font-size: 8pt; font-weight: 700; color: #1a6b3c; }
        .print-header .title-area { flex: 1; text-align: center; font-size: 10pt; font-weight: 700; text-transform: uppercase; }
        .print-header .form-number { width: 30%; text-align: right; font-size: 7.5pt; color: #555; }

        .print-footer { position: fixed; bottom: 0; left: 0; right: 0; height: 8mm; display: flex; align-items: flex-end; justify-content: space-between; background: #fff; z-index: 100; font-size: 6.5pt; color: #555; }

        /* Watermark */
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 72pt; font-weight: 900; color: rgba(0,0,0,0.03); letter-spacing: 6pt; z-index: 50; pointer-events: none; }

        /* Table */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 2mm; font-size: 7pt; }
        table.data-table th, table.data-table td { border: 0.5px solid #000; padding: 1.2mm 1.5mm; vertical-align: top; }
        table.data-table th { background: #e8e8e8; font-weight: 700; text-align: center; }
        table.data-table td { line-height: 1.25; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: 700; }
        .whitespace-nowrap { whitespace: nowrap; }

        /* Badges for print */
        .badge { display: inline-block; font-weight: 700; padding: 0.2mm 1mm; border-radius: 0.5mm; border: 0.5px solid #ccc; font-size: 6pt; text-transform: uppercase; }

        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <script>
        if (window.self === window.top) {
            window.onload = function() { window.print(); }
        }
    </script>

    <div class="watermark">R&D HERBATECH</div>

    <div class="print-header">
        <div class="logo-area">
            <div class="logo-icon">HT</div>
            <span class="logo-text">HERBATECH</span>
        </div>
        <div class="title-area">LOG BOOK BAHAN PENGEMAS (PM)</div>
        <div class="form-number">CM-04/RD/LBP-001</div>
    </div>

    <div class="print-footer">
        <span>R&D DEPARTMENT — R&D MANAGEMENT SYSTEM</span>
        <span>Halaman 1 dari 1</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4mm;">No</th>
                <th style="width: 14mm;">Tgl Terima</th>
                <th style="width: 25mm;">Nama Supplier/Produsen</th>
                <th style="width: 20mm;">Jenis Kemasan</th>
                <th style="width: 35mm;">Nama / Deskripsi Material</th>
                <th style="width: 18mm;">Kode / Sample No.</th>
                <th style="width: 12mm;">Jumlah</th>
                <th style="width: 10mm;">Satuan</th>
                <th style="width: 15mm;">Kelengkapan Dok.</th>
                <th style="width: 30mm;">Kondisi Fisik Aktual</th>
                <th style="width: 45mm;">Catatan Trial PM</th>
                <th style="width: 14mm;">Status Uji</th>
                <th style="width: 12mm;">Fisik</th>
                <th style="width: 15mm;">OM Approval</th>
                <th style="width: 18mm;">Penerima (R&D)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $i => $entry)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="whitespace-nowrap">{{ $entry->tanggal_terima->format('d/m/Y') }}</td>
                <td class="text-bold">{{ $entry->nama_supplier_display }}</td>
                <td>{{ $entry->jenis_kemasan }}</td>
                <td>
                    <span class="text-bold">{{ $entry->nama_material }}</span>
                    @if($entry->deskripsi_material)
                    <br><span style="font-size: 6pt; color: #555;">{{ $entry->deskripsi_material }}</span>
                    @endif
                </td>
                <td style="font-family: monospace; font-size: 6.5pt;">
                    {{ $entry->kode_bahan ?? '—' }}<br>
                    <span style="color:#666;">{{ $entry->no_sample ?? '—' }}</span>
                </td>
                <td class="text-right text-bold">{{ number_format($entry->jumlah_diterima, 0) }}</td>
                <td>{{ $entry->satuan }}</td>
                <td class="text-center">{{ $entry->kelengkapan_dokumen }}</td>
                <td style="font-size: 6.5pt; line-height: 1.2;">{{ $entry->kondisi_fisik_aktual }}</td>
                <td style="font-size: 6pt; font-family: monospace; line-height: 1.1; white-space: pre-wrap;">{{ $entry->trialPm ? '['.$entry->trialPm->code.'] ' : '' }}{{ $entry->catatan_trial }}</td>
                <td class="text-center text-bold">{{ $entry->status_pengujian }}</td>
                <td class="text-center">{{ $entry->kondisi_fisik ?? '—' }}</td>
                <td class="text-center">
                    <span class="text-bold">{{ $entry->om_approval }}</span>
                    @if($entry->omApprover)
                    <br><span style="font-size:5.5pt; color:#666;">by {{ $entry->omApprover->name }}</span>
                    @endif
                </td>
                <td class="text-bold">{{ $entry->nama_penerima }}</td>
                <td style="font-size: 6.5pt; color: #555;">{{ $entry->keterangan ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="16" class="text-center" style="padding: 10mm; font-style: italic;">
                    Tidak ada data log book untuk dicetak.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
