<?php

namespace App\Enums;

enum WIPStatus: string
{
    case Active = 'ACTIVE';
    case Void = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Void => 'Void',
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
