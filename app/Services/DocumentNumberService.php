<?php

namespace App\Services;

use App\Models\DocumentNumber;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function generateQuotationNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = DocumentNumber::query()
                ->where('tipe_dokumen', 'QUOTATION')
                ->where('tahun', $date->year)
                ->where('bulan', $date->month)
                ->lockForUpdate()
                ->first();

            if (! $number) {
                $number = DocumentNumber::create([
                    'tipe_dokumen' => 'QUOTATION',
                    'tahun' => $date->year,
                    'bulan' => $date->month,
                    'last_number' => 0,
                ]);
            }

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/QUOT/NAJ-MKS/{$this->romanMonth($date->month)}/{$date->format('y')}";
        });
    }

    public function generatePurchaseOrderNumber(CarbonInterface $date): string
    {
        return DB::transaction(function () use ($date): string {
            $number = DocumentNumber::query()
                ->where('tipe_dokumen', 'PURCHASE_ORDER')
                ->where('tahun', $date->year)
                ->where('bulan', $date->month)
                ->lockForUpdate()
                ->first();

            if (! $number) {
                $number = DocumentNumber::create([
                    'tipe_dokumen' => 'PURCHASE_ORDER',
                    'tahun' => $date->year,
                    'bulan' => $date->month,
                    'last_number' => 0,
                ]);
            }

            $number->increment('last_number');
            $sequence = str_pad((string) $number->last_number, 3, '0', STR_PAD_LEFT);

            return "{$sequence}/PO-NAJ/{$this->romanMonth($date->month)}/{$date->format('Y')}";
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
}
