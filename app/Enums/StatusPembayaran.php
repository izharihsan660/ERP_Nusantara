<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case Belum = 'BELUM';
    case Sebagian = 'SEBAGIAN';
    case Lunas = 'LUNAS';

    public function label(): string
    {
        return match ($this) {
            self::Belum => 'Belum',
            self::Sebagian => 'Sebagian',
            self::Lunas => 'Lunas',
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
