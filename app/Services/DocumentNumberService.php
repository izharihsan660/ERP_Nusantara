<?php

namespace App\Services;

use App\Models\DocumentNumber;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function generateQuotationNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = $this->lockedNumber('QUOTATION', $date);

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/QUOT/NAJ-MKS/{$this->romanMonth($date->month)}/{$date->format('y')}";
        });
    }

    public function generatePurchaseOrderNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = $this->lockedNumber('PURCHASE_ORDER', $date);

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/PO-NAJ/{$this->romanMonth($date->month)}/{$date->format('Y')}";
        });
    }

    public function generateSpbNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = $this->lockedNumber('SPB', $date);

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/WHMKS/NAJ/{$this->romanMonth($date->month)}/{$date->format('y')}";
        });
    }

    public function generateInvoiceNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = $this->lockedNumber('INVOICE', $date);

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/NOTA-NAJ/MKS/NAJGROUP/{$this->romanMonth($date->month)}/{$date->format('Y')}";
        });
    }

    public function generatePermintaanDanaNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = $this->lockedNumber('PD', $date);

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/PD-NAJ/{$this->romanMonth($date->month)}/{$date->format('Y')}";
        });
    }

    private function romanMonth(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month];
    }

    private function lockedNumber(string $type, CarbonInterface $date): DocumentNumber
    {
        $attributes = [
            'tipe_dokumen' => $type,
            'tahun' => $date->year,
            'bulan' => $date->month,
        ];

        try {
            DocumentNumber::query()->firstOrCreate($attributes, ['last_number' => 0]);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateKey($exception)) {
                throw $exception;
            }
        }

        return DocumentNumber::query()->where($attributes)->lockForUpdate()->firstOrFail();
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        return in_array((string) ($exception->errorInfo[0] ?? ''), ['23000', '23505'], true);
    }
}
