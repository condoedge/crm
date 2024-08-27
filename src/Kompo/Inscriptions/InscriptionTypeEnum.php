<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

enum InscriptionTypeEnum: string
{
    case GENERIC = 'generic';

    public function registerTitle()
    {
        return match ($this) {
            self::GENERIC => 'translate.inscriptions.a-parent',
        };
    }

    public function registerDescription()
    {
        return match ($this) {
            self::GENERIC => 'translate.inscriptions.a-parent-desc',
        };
    }

    public function registerRoute($person, $qrCode)
    {
        return match ($this) {
            self::GENERIC => $person->getInscriptionPersonRoute($qrCode),
        };
    }
}