<?php

namespace Condoedge\Crm\Models;

enum EventAttendanceStatus: int
{
    use \Kompo\Models\Traits\EnumKompo;
    
    case ATTENDED = 1;
    case ABSTENT = 2;

    /**
     * WE DON'T SAVE IT. THIS TYPE IS USED TO DELETE THE ATTENDANCE RECORD AND SHOW THE DEFAULT PILL
     */
    case NOT_TAKEN = 3;
    
    public function label(): string
    {
        return match ($this) {
            self::ATTENDED => __('events.present'),
            self::ABSTENT => __('events.absent'),
            self::NOT_TAKEN => __('events.pending'),
        };
    }

    public function classes(): string
    {
        return match ($this) {
            self::ATTENDED => 'bg-positive text-white',
            self::ABSTENT => 'bg-danger text-white',
            self::NOT_TAKEN => 'bg-greenlight text-greendark',
        };
    }

    public function nextCheck()
    {
        return match ($this) {
            self::ATTENDED => self::ABSTENT,
            self::ABSTENT => self::NOT_TAKEN,
            self::NOT_TAKEN => self::ATTENDED,
        };
    }

    public static function toSave()
    {
        return [
            self::ATTENDED,
            self::ABSTENT,
        ];
    }

    public static function valuesToShow()
    {
        return collect(self::toSave())->map(function ($case) {
            return $case->value;
        })->all();
    }

    public static function colorsToShow()
    {
        return collect(self::toSave())->map(function ($case) {
            return $case->classes();
        })->all();
    }
}