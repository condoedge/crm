<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

enum InscriptionTypeEnum: string
{
    case PARENT = 'parent';

    /**
     * Get the title for the inscription type
     * @return string
     */
    public function registerTitle()
    {
        return match ($this) {
            self::PARENT => 'inscriptions.a-parent',
        };
    }

    /**
     * Get the description for the inscription type
     * @return string
     */
    public function registerDescription()
    {
        return match ($this) {
            self::PARENT => 'inscriptions.a-parent-desc',
        };
    }

    /**
     *  Get the route for the inscription type
     * @param \Condoedge\Crm\Models\Person; $person
     * @param string|null $qrCode
     * @return string
     */
    public function registerRoute($person, $qrCode)
    {
        return match ($this) {
            self::PARENT => $person->getInscriptionFromPersonLinkRoute($qrCode),
        };
    }

    /**
     * Get the role id for the inscription type
     * @param \Condoedge\Crm\Models\Inscription $inscription
     * @return string
     */
    public function getRole($inscription)
    {
        return match ($this) {
            self::PARENT => 'parent',
        };
    }

    public static function defaultPersonEvent()
    {

    }
}