<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapanPoExport implements FromArray, ShouldAutoSize
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
            'No. Quotation',
            'Customer',
            'No. PO Customer',
            'Metode Bayar',
            'Total Nilai',
            'Total HPP',
            'Profit',
            'Status PO',
            'Tanggal PO',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_quotation'],
                $row['customer'],
                $row['no_po_customer'],
                $row['metode_bayar'],
                $row['total_nilai'],
                $row['total_hpp'],
                $row['profit'],
                $row['status_po_label'],
                $row['tanggal_po'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total PO', $this->summary['total_po']];
        $data[] = ['Summary', 'Total Nilai', $this->summary['total_nilai']];
        $data[] = ['Summary', 'Total Profit', $this->summary['total_profit']];

        return $data;
    }
}
