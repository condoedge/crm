<?php

namespace Condoedge\Crm\Models;

enum DiciplinaryReasonTypeEnum: int
{
    use \Condoedge\Utils\Models\Traits\EnumKompo;

    case ABUSIVE = 1;
    case HARASSMENT = 2;
    case DISCRIMINATION = 3;
    case VIOLENCE = 4;
    case OTHER = 5;

    public function label()
    {
        return match ($this) {
            self::ABUSIVE => 'disciplinary.abusive',
            self::HARASSMENT => 'disciplinary.harassment',
            self::DISCRIMINATION => 'disciplinary.discrimination',
            self::VIOLENCE => 'disciplinary.violence',
            self::OTHER => 'disciplinary.other',
        };
    }
}