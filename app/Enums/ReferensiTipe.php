<?php

namespace App\Enums;

enum ReferensiTipe: string
{
    case PR = 'PR';
    case PO = 'PO';

    public function label(): string
    {
        return match ($this) {
            self::PR => 'PR Customer',
            self::PO => 'PO Customer',
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
