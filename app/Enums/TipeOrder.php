<?php

namespace App\Enums;

enum TipeOrder: string
{
    case VOR = 'VOR';
    case STK = 'STK';

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
            fn (self $tipe): array => ['value' => $tipe->value, 'label' => $tipe->label()],
            self::cases(),
        );
    }
}
