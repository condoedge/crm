<?php

namespace Condoedge\Crm\Models;

enum ScheduleFrequencyEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case SINGLE = 1;
    case CUSTOM = 5;
    case DAILY = 10;
    case WEEKLY = 30;
    case MONTHLY = 40;
    case YEARLY = 50;

    public function label()
    {
        return match ($this)
        {
            static::SINGLE => __('events.single'),
            static::DAILY => __('events.daily'),
            static::WEEKLY => __('events.weekly'),
            static::MONTHLY => __('events.monthly'),
            static::YEARLY => __('events.yearly'),
            static::CUSTOM => __('events.customs'),
        };
    }
}
