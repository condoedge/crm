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
    public function registerRoute($inscription, $extra = [])
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

    public function basedInInscriptionForOtherPerson()
    {
        return match ($this) {
            default => false,
        };
    } 

    public function requiresPayment()
    {
        return match ($this) {
            default => false
        };
    }

    public function allowTrial()
    {
        return (boolean) $this->regularToTrial();
    }

    public function isTrial()
    {
        return (boolean) $this->trialToRegular();
    }

    public function regularToTrial()
    {
        return match ($this) {
            default => null,
        };
    }

    public function trialToRegular()
    {
        return match ($this) {
            default => null,
        };
    }

    public function hasEmailVerification()
    {
        return match ($this) {
            default => true,
        };
    }
    
    public static function defaultPersonEvent()
    {

    }
    
    public function statusPill($inscription)
    {
        return match ($this) {
            default => $this->defaultStatusPill($inscription),
        };
    }

    protected function defaultStatusPill($inscription)
    {
        if ($personTeam = $inscription->getActiveRelatedPersonTeam()) {
            return $personTeam->getStatusPillElement();
        }

        return $inscription->statusPill();
    }

    public function expirationDate($inscription)
    {
        return match ($this) {
            default => null,
        };
    }

    public function askForLegalAgeTerms()
    {
        return match($this) {
            default => false,
        };
    }
}