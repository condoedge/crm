<?php

namespace Condoedge\Crm\Models;

enum InscriptionStatusEnum: int
{
    use \Condoedge\Utils\Models\Traits\EnumKompo;

    case CANCELED = 0;
    case CREATED = 1;
    case FILLED = 2;
    case APPROVED = 5;
    case REJECTED = 6;
    case PENDING_PAYMENT = 9;
    case COMPLETED_SUCCESSFULLY = 10;

    public function label(): string
    {
        return match ($this) {
            self::CANCELED => __('crm.canceled'),
            self::CREATED => __('crm.invited'),
            self::FILLED => __('crm.pending'),
            self::APPROVED => __('crm.approved'),
            self::REJECTED => __('crm.rejected'),
            self::PENDING_PAYMENT => __('crm.pending-payment'),
            self::COMPLETED_SUCCESSFULLY => __('crm.completed-successfully'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CANCELED => 'bg-danger',
            self::CREATED => 'bg-info',
            self::FILLED => 'bg-warning',
            self::APPROVED => 'bg-greenmain',
            self::REJECTED => 'bg-danger',
            self::PENDING_PAYMENT => 'bg-warning',
            self::COMPLETED_SUCCESSFULLY => 'bg-positive',
        };
    }

    public function accepted()
    {
        return match($this) {
            self::PENDING_PAYMENT => true,
            self::APPROVED => true,
            self::COMPLETED_SUCCESSFULLY => true,
            default => false,
        };
    }

    public function completed()
    {
        return match ($this) {
            self::COMPLETED_SUCCESSFULLY => true,
            default => false,
        };
    }

    public static function getInsideStatuses()
    {
        return [self::APPROVED, self::PENDING_PAYMENT, self::COMPLETED_SUCCESSFULLY];
    }
}
