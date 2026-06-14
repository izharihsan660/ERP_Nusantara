<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case COD = 'COD';
    case CBD = 'CBD';
    case TOP = 'TOP';

    public function label(): string
    {
        return $this->value;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $metode): array => ['value' => $metode->value, 'label' => $metode->label()],
            self::cases(),
        );
    }
}
