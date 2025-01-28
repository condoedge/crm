<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\InscriptionModel;

trait InscriptionFormUtilsTrait 
{
    protected $inscriptionCode;
    protected $inscription;
    protected $inscriptionId;

    protected $event;
    protected $eventId;

    protected $team;
    protected $teamId;

    protected $person;
    protected $personId;
    protected $mainPerson;

    protected function setInscriptionInfo()
    {
        $this->inscriptionCode = $this->prop('inscription_code');
        $this->inscriptionId = $this->prop('inscription_id');

        if ($this->inscriptionId) {
            $this->inscription = InscriptionModel::findOrFail($this->inscriptionId);
        } else if ($this->inscriptionCode) {
            $this->inscription = InscriptionModel::forQrCode($this->inscriptionCode)->first();
        }

        $this->inscriptionId = $this->inscription->id;
        
        $this->inscription->setIsReregistration($this->prop('reregistration'));

        $this->person = $this->inscription->person;
        $this->mainPerson = $this->person?->getRegisteringPerson() ?? $this->inscription->inscribedBy;
        
        $this->personId = $this->person?->id;

        $this->event = $this->inscription->event;
        $this->eventId = $this->event?->id;

        $this->team = $this->inscription->team;
        $this->teamId = $this->team?->id;
    }
}