<?php

namespace Condoedge\Crm\Models;

enum PersonTeamStatusEnum: int
{
    use \Kompo\Models\Traits\EnumKompo;

    case PENDING_PAYMENT = 1;
    case ACTIVE = 2;
    case PROBATION = 3;
    case TERMINATED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => __('crm.pending-payment'),
            self::ACTIVE => __('crm.active'),
            self::PROBATION => __('crm.probation'),
            self::TERMINATED => __('crm.terminated'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'bg-warning',
            self::ACTIVE => 'bg-positive',
            self::PROBATION => 'bg-warning',
            self::TERMINATED => 'bg-danger',
        };
    }
}
