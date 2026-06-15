<?php

namespace App\Enums;

enum KategoriPD: string
{
    case BayarRma = 'BAYAR_RMA';
    case BiayaPengiriman = 'BIAYA_PENGIRIMAN';
    case OperasionalKantor = 'OPERASIONAL_KANTOR';

    public function label(): string
    {
        return match ($this) {
            self::BayarRma => 'Bayar RMA',
            self::BiayaPengiriman => 'Biaya Pengiriman',
            self::OperasionalKantor => 'Operasional Kantor',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $kategori): array => ['value' => $kategori->value, 'label' => $kategori->label()],
            self::cases(),
        );
    }
}
