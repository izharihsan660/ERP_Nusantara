<?php

namespace App\Enums;

enum VendorType: string
{
    case Rma = 'RMA';
    case VendorLain = 'VENDOR_LAIN';

    public function label(): string
    {
        return match ($this) {
            self::Rma => 'RMA',
            self::VendorLain => 'Vendor Lain',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
