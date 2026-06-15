<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OutstandingExport implements FromArray, ShouldAutoSize
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
            'No. Invoice',
            'Customer',
            'Total Nilai',
            'Sudah Dibayar',
            'Sisa',
            'Metode Bayar',
            'Jatuh Tempo',
            'Hari Tersisa',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_invoice'],
                $row['customer'],
                $row['total_nilai'],
                $row['sudah_dibayar'],
                $row['sisa'],
                $row['metode_bayar'],
                $row['jatuh_tempo'],
                $row['hari_tersisa'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total Outstanding', $this->summary['total_outstanding']];
        $data[] = ['Summary', 'Total Invoice Belum Lunas', $this->summary['total_invoice_belum_lunas']];

        return $data;
    }
}
