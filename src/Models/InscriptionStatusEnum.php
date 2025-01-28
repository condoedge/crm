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
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREATED => 'bg-info',
            self::FILLED => 'bg-warning',
            self::APPROVED => 'bg-positive',
            self::REJECTED => 'bg-danger',
        };
    }

    public function accepted()
    {
        return match($this) {
            self::APPROVED => true,
            default => false,
        };
    }
}
