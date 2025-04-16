<?php

namespace Condoedge\Crm\Models;

enum ScheduleFrequencyEnum: int
{
    use \Condoedge\Utils\Models\Traits\EnumKompo;

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

    public function nextDate($date, $diff = 0)
    {
        $dateCopy = $date->copy();

        return match ($this) 
        {
            static::DAILY => $dateCopy->addDays($diff + 1),
            static::WEEKLY => $dateCopy->addWeeks($diff + 1),
            static::MONTHLY => $dateCopy->addMonths($diff + 1),
            static::YEARLY => $dateCopy->addYears($diff + 1),
            static::SINGLE => null,
            static::CUSTOM => null,
        };
    }

    public function diffToNow($date)
    {
        return match ($this) 
        {
            static::DAILY => $date->diffInDays(now()),
            static::WEEKLY => $date->diffInWeeks(now()),
            static::MONTHLY => $date->diffInMonths(now()),
            static::YEARLY => $date->diffInYears(now()),
            static::SINGLE => null,
            static::CUSTOM => null,
        };
    }
}
