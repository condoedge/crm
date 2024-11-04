<?php

namespace Condoedge\Crm\Models;

enum GenderEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case FEMALE = 1;
    case MALE = 2;
    case OTHER = 3;

    public function label()
    {
        return match ($this)
        {
            static::FEMALE => __('inscriptions.female'),
            static::MALE => __('inscriptions.male'),
            static::OTHER => __('inscriptions.other'),
        };
    }

    public function labelFromAge($dateOfBirth)
    {
        $age = getAgeFromDob($dateOfBirth);

        return match ($this)
        {
            static::FEMALE => $age < 18 ? __('crm.girl') : __('crm.woman'),
            static::MALE => $age < 18 ? __('crm.boy') : __('crm.man'),
            static::OTHER => __('inscriptions.other'),
        };
    }

    public function letter()
    {
        return match ($this)
        {
            static::FEMALE => __('crm.female-letter'),
            static::MALE => __('crm.male-letter'),
            static::OTHER => __('crm.other-letter'),
        };
    }

    // DESIGN 1
    public function bgColor()
    {
        return match ($this)
        {
            static::FEMALE => 'bg-pinklight',
            static::MALE => 'bg-infolight',
            static::OTHER => 'bg-graylight',
        };
    }

    public function textColor()
    {
        return match ($this)
        {
            static::FEMALE => 'text-pinkdark',
            static::MALE => 'text-infodark',
            static::OTHER => 'text-graydark',
        };
    }

    // DESIGN 2
    public function bgColor2()
    {
        return match ($this)
        {
            static::FEMALE => 'bg-pink',
            static::MALE => 'bg-info',
            static::OTHER => 'bg-gray-700',
        };
    }

    public function textColor2()
    {
        return match ($this)
        {
            static::FEMALE => 'text-white',
            static::MALE => 'text-white',
            static::OTHER => 'text-white',
        };
    }
}
