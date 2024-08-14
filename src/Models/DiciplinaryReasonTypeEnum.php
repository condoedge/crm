<?php

namespace Condoedge\Crm\Models;

enum DiciplinaryReasonTypeEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case ABUSIVE = 1;
    case HARASSMENT = 2;
    case DISCRIMINATION = 3;
    case VIOLENCE = 4;
    case OTHER = 5;

    public function label()
    {
        return match ($this) {
            self::ABUSIVE => 'translate.abusive',
            self::HARASSMENT => 'translate.harassment',
            self::DISCRIMINATION => 'translate.discrimination',
            self::VIOLENCE => 'translate.violence',
            self::OTHER => 'translate.other',
        };
    }
}