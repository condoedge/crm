<?php

namespace Condoedge\Crm\Models;

enum PersonEventConfirmationEnum: int
{
    use \Kompo\Models\Traits\EnumKompo;
    
    case CONFIRMED = 1;
    case ABSTENT = 2;
    case NOT_SURE = 3;

    public function label(): string
    {
        return match ($this) {
            self::CONFIRMED => __('events.confirmed'),
            self::ABSTENT => __('events.absent'),
            self::NOT_SURE => __('events.not-sure'),
        };
    }

    public function classes(): string
    {
        return match ($this) {
            self::CONFIRMED => 'bg-positive text-white',
            self::ABSTENT => 'bg-danger text-white',
            self::NOT_SURE => 'bg-warning text-white',
        };
    }
}