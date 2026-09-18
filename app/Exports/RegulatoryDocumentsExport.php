<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegulatoryDocumentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $documents;
    private int $row = 0;

    public function __construct(Collection $documents)
    {
        $this->documents = $documents;
    }

    public function collection(): Collection
    {
        return $this->documents;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Dokumen',
            'Folder / Lokasi',
            'Ekstensi',
            'Versi',
            'Ukuran File',
            'Diunggah Oleh',
            'Tanggal Unggah',
            'Terakhir Diperbarui',
            'Deskripsi',
        ];
    }

    public function map($doc): array
    {
        $this->row++;
        $folderPath = $doc->folder
            ? implode(' / ', array_map(fn($f) => $f->name, $doc->folder->breadcrumbs()))
            : 'Root (Folder Utama)';

        return [
            $this->row,
            $doc->original_name,
            $folderPath,
            strtoupper($doc->extension ?? '-'),
            'v' . $doc->version,
            $doc->formatted_size,
            $doc->uploader?->name ?? '—',
            $doc->created_at?->format('d/m/Y H:i') ?? '—',
            $doc->updated_at?->format('d/m/Y H:i') ?? '—',
            $doc->description ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2F7D46'],
                ],
            ],
        ];
    }
}
