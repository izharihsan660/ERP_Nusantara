<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapanSpbExport implements FromArray, ShouldAutoSize
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
            'No. SPB',
            'Customer',
            'Site',
            'Referensi',
            'No. Referensi',
            'Ekspedisi',
            'ETD',
            'ETA',
            'Status',
            'Total Item',
        ]];

        foreach ($this->rows as $row) {
            $data[] = [
                $row['no_spb'],
                $row['customer'],
                $row['site'],
                $row['referensi'],
                $row['no_referensi'],
                $row['ekspedisi'],
                $row['etd'],
                $row['eta'],
                $row['status_label'],
                $row['total_item'],
            ];
        }

        $data[] = [];
        $data[] = ['Summary', 'Total SPB', $this->summary['total_spb']];
        $data[] = ['Summary', 'Total Item Dikirim', $this->summary['total_item_dikirim']];

        return $data;
    }
}
