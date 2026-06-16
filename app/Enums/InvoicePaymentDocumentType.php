<?php

namespace App\Enums;

enum InvoicePaymentDocumentType: string
{
    case BuktiTransfer = 'BUKTI_TRANSFER';
    case InvoiceCustomer = 'INVOICE_CUSTOMER';

    public function label(): string
    {
        return match ($this) {
            self::BuktiTransfer => 'Bukti Transfer',
            self::InvoiceCustomer => 'Invoice Customer',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $tipe): array => [
            'value' => $tipe->value,
            'label' => $tipe->label(),
        ], self::cases());
    }
}
