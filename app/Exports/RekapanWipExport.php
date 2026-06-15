<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapanWipExport implements FromArray, ShouldAutoSize
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
            'No. WIP',
            'Tipe',
            'No. Quotation',
            'Customer',
            'Ekspedisi',
            'Status Supply',
            'Tanggal Input',
            'Tanggal Tersupply',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_wip'],
                $row['tipe'],
                $row['no_quotation'],
                $row['customer'],
                $row['ekspedisi'],
                $row['status_supply_label'],
                $row['tanggal_input'],
                $row['tanggal_tersupply'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total WIP', $this->summary['total_wip']];
        $data[] = ['Summary', 'Belum Tersupply', $this->summary['belum_tersupply']];
        $data[] = ['Summary', 'Tersupply', $this->summary['tersupply']];

        return $data;
    }
}
