<?php

namespace App\Enums;

enum DocumentType: string
{
    case Quotation = 'QUOTATION';
    case Spb = 'SPB';
    case Invoice = 'INVOICE';
    case Nota = 'NOTA';
    case PoNaj = 'PO_NAJ';

    public function label(): string
    {
        return match ($this) {
            self::Quotation => 'Quotation',
            self::Spb => 'SPB',
            self::Invoice => 'Invoice',
            self::Nota => 'Nota',
            self::PoNaj => 'PO NAJ',
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
