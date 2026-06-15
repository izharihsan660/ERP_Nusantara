<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapanInvoiceExport implements FromArray, ShouldAutoSize
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
            'No. Dokumen',
            'Tipe',
            'Customer',
            'No. Faktur Pajak',
            'Total Nilai',
            'Metode Bayar',
            'Jatuh Tempo',
            'Status Pembayaran',
            'Tanggal Bayar',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_dokumen'],
                $row['tipe_label'],
                $row['customer'],
                $row['no_faktur_pajak'],
                $row['total_nilai'],
                $row['metode_bayar'],
                $row['jatuh_tempo'],
                $row['status_pembayaran_label'],
                $row['tanggal_bayar'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total Tagihan', $this->summary['total_tagihan']];
        $data[] = ['Summary', 'Total Lunas', $this->summary['total_lunas']];
        $data[] = ['Summary', 'Total Outstanding', $this->summary['total_outstanding']];

        return $data;
    }
}
