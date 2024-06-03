<?php

namespace Condoedge\Crm\Models;

enum RegistrationTypeEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case PREREGISTRATION = 1;
    case REGISTRATION = 2;

    public function label()
    {
        return match ($this)
        {
            static::PREREGISTRATION => __('events-preregistrations'),
            static::REGISTRATION => __('events-registrations'),
        };
    }
}
