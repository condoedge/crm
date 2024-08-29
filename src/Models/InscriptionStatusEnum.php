<?php

namespace Condoedge\Crm\Models;

enum InscriptionStatusEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case PENDING = 1;
    case APPROVED = 5;
    case REJECTED = 6;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'translate.pending',
            self::APPROVED => 'translate.approved',
            self::REJECTED => 'translate.rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning',
            self::APPROVED => 'bg-positive',
            self::REJECTED => 'bg-danger',
        };
    }
}
