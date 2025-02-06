<?php

namespace Condoedge\Crm\Models;

enum InscriptionStatusEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case CREATED = 1;
    case FILLED = 2;
    case APPROVED = 5;
    case REJECTED = 6;
    case PENDING_PAYMENT = 9;
    case COMPLETED_SUCCESSFULLY = 10;

    public function label(): string
    {
        return match ($this) {
            self::CREATED => __('translate.crm.invitated'),
            self::FILLED => __('crm.pending'),
            self::APPROVED => __('crm.approved'),
            self::REJECTED => __('crm.rejected'),
            self::PENDING_PAYMENT => __('crm.pending_payment'),
            self::COMPLETED_SUCCESSFULLY => __('crm.completed_successfully'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREATED => 'bg-info',
            self::FILLED => 'bg-warning',
            self::APPROVED => 'bg-positive',
            self::REJECTED => 'bg-danger',
            self::PENDING_PAYMENT => 'bg-warning',
            self::COMPLETED_SUCCESSFULLY => 'bg-positive',
        };
    }

    public function accepted()
    {
        return match($this) {
            self::APPROVED => true,
            default => false,
        };
    }

    public static function getInsideStatuses()
    {
        return [self::APPROVED, self::PENDING_PAYMENT, self::COMPLETED_SUCCESSFULLY];
    }
}
