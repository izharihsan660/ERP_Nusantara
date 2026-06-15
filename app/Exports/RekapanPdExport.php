<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapanPdExport implements FromArray, ShouldAutoSize
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        private readonly array $rows,
        private readonly array $summary,
    ) {}

    public function array(): array
    {
        $data = [[
            'No. PD',
            'Kategori',
            'Nominal',
            'Jumlah Realisasi',
            'Status',
            'Dibuat oleh',
            'Diapprove oleh',
            'Tanggal',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_pd'],
                $row['kategori_label'],
                $row['nominal'],
                $row['jumlah_realisasi'],
                $row['status_label'],
                $row['dibuat_oleh'],
                $row['diapprove_oleh'],
                $row['tanggal'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total PD', $this->summary['total_pd']];
        $data[] = ['Summary', 'Total Nominal', $this->summary['total_nominal']];
        $data[] = ['Summary', 'Total Realisasi', $this->summary['total_realisasi']];

        return $data;
    }
}
