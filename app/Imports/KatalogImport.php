<?php

namespace App\Imports;

use App\Models\Katalog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KatalogImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $partNo = trim((string) ($row['part_no'] ?? ''));

            if ($partNo === '') {
                continue;
            }

            Katalog::updateOrCreate(
                ['part_no' => $partNo],
                [
                    'nama_barang' => trim((string) ($row['nama_barang'] ?? $partNo)),
                    'spesifikasi' => $row['spesifikasi'] ?? null,
                    'satuan' => $row['satuan'] ?? null,
                    'hpp' => (float) ($row['hpp'] ?? 0),
                    'harga_jual_default' => (float) ($row['harga_jual_default'] ?? 0),
                    'kategori' => $row['kategori'] ?? null,
                    'is_active' => true,
                ],
            );
        }
    }
}
