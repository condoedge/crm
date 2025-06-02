<?php

namespace Condoedge\Crm\Models;

enum SpokenLanguageEnum: string
{
    use \Condoedge\Utils\Models\Traits\EnumKompo;

    case EN = 'en';
    case FR = 'fr';
    case ES = 'es';

    public function label()
    {
        return match ($this) {
            static::EN => __('inscriptions.english'),
            static::FR => __('inscriptions.french'),
            static::ES => __('inscriptions.spanish'),
        };
    }

    public static function getMultiSelect()
    {
        return _MultiSelect('inscriptions.spoken-languages')->name('spoken_languages')->options(
            SpokenLanguageEnum::optionsWithLabels()
        );
    }
}
