<?php

namespace Condoedge\Crm\Models;

enum RegisterStatusEnum: int
{
    use \Condoedge\Utils\Models\Traits\EnumKompo;

    case RS_REQUESTED = 1;
    case RS_ACCEPTED = 5;
    case RS_REJECTED = 6;
    case RS_PAID = 9;

    public function label()
    {
        return match ($this) {
            static::RS_REQUESTED => __('inscriptions.requested'),
            static::RS_ACCEPTED => __('inscriptions.approved'),
            static::RS_REJECTED => __('inscriptions.rejected'),
            static::RS_PAID => __('inscriptions.paid'),
        };
    }
}
