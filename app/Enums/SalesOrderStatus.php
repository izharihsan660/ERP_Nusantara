<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Open = 'OPEN';
    case Completed = 'COMPLETED';
    case Void = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Completed => 'Completed',
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
