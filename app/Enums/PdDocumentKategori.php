<?php

namespace App\Enums;

enum PdDocumentKategori: string
{
    case BuktiPembelian = 'BUKTI_PEMBELIAN';
    case BuktiReimbursement = 'BUKTI_REIMBURSEMENT';

    public function label(): string
    {
        return match ($this) {
            self::BuktiPembelian => 'Bukti Pembelian',
            self::BuktiReimbursement => 'Bukti Reimbursement',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $kategori): array => [
            'value' => $kategori->value,
            'label' => $kategori->label(),
        ], self::cases());
    }
}
