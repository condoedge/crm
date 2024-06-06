<?php

namespace Condoedge\Crm\Models;

enum RegistrationTypeEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case RT_OPEN_ALL = 1;
    case RT_MEMBERS_ONLY = 5;
    case RT_ONLY_QR_CODE = 10;

    public function label()
    {
        return match ($this)
        {
            static::RT_OPEN_ALL => __('events.registrations-open-all'),
            static::RT_MEMBERS_ONLY => __('events.registrations-members-only'),
            static::RT_ONLY_QR_CODE => __('events.registrations-qrcode'),
        };
    }
}
