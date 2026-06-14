<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'DRAFT';
    case PendingApproval = 'PENDING_APPROVAL';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Void = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
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
