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
            self::ABUSIVE => __('disciplinary.abusive'),
            self::HARASSMENT => __('disciplinary.harassment'),
            self::DISCRIMINATION => __('disciplinary.discrimination'),
            self::VIOLENCE => __('disciplinary.violence'),
            self::OTHER => __('disciplinary.other'),
        };
    }
}
