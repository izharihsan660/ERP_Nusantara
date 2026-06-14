<?php

namespace App\Enums;

enum StatusSupply: string
{
    case BelumTersupply = 'BELUM_TERSUPPLY';
    case Tersupply = 'TERSUPPLY';

    public function label(): string
    {
        return match ($this) {
            self::BelumTersupply => 'Belum Tersupply',
            self::Tersupply => 'Tersupply',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
