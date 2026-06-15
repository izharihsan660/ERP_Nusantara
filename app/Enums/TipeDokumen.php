<?php

namespace App\Enums;

enum TipeDokumen: string
{
    case Invoice = 'INVOICE';
    case NotaPenjualan = 'NOTA_PENJUALAN';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Invoice',
            self::NotaPenjualan => 'Nota Penjualan',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $tipe): array => ['value' => $tipe->value, 'label' => $tipe->label()],
            self::cases(),
        );
    }
}
