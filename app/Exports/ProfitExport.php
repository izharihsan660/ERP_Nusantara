<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitExport implements FromArray, ShouldAutoSize
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
            'Total Nilai',
            'Total HPP',
            'Profit',
            'Margin (%)',
            'Tanggal',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_quotation'],
                $row['customer'],
                $row['total_nilai'],
                $row['total_hpp'],
                $row['profit'],
                $row['margin'],
                $row['tanggal'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total Nilai', $this->summary['total_nilai']];
        $data[] = ['Summary', 'Total HPP', $this->summary['total_hpp']];
        $data[] = ['Summary', 'Total Profit', $this->summary['total_profit']];
        $data[] = ['Summary', 'Rata-rata Margin (%)', $this->summary['rata_rata_margin']];

        return $data;
    }
}
