<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

enum InscriptionTypeEnum: string
{
    /**
     * Get the title for the inscription type
     * @return string
     */
    public function registerTitle()
    {
        return match ($this) {
            
        };
    }

    /**
     * Get the description for the inscription type
     * @return string
     */
    public function registerDescription()
    {
        return match ($this) {
            
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
            
        };
    }

    public function getRegisteredByRole()
    {
        return match ($this) {
            
        };
    }

    public function confirmationRoute()
    {
        return match ($this) {
            default => 'inscription.confirmation',
        };
    }

    public function createForPerson($person, $qrCode)
    {
        return match ($this) {
            default => $person->createOrUpdateInscription($qrCode, self::class),
        };
    }

    public static function defaultPersonEvent()
    {

    }
}