<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\InscriptionModel;

trait InscriptionFormUtilsTrait 
{
    protected $inscriptionCode;
    protected $inscription;
    protected $inscriptionId;

    protected $mainInscription;

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

        $this->inscriptionId = $this->inscription?->id;
        
        $this->person = $this->inscription?->person;
        $this->mainPerson = $this->person?->getRegisteringPerson() ?? $this->inscription?->inscribedBy;
        
        $this->personId = $this->person?->id;

        $this->event = $this->inscription?->event;
        $this->eventId = $this->event?->id;

        $this->team = $this->inscription?->team;
        $this->teamId = $this->team?->id;

        $this->mainInscription = $this->inscription?->getMainInscription();
    }

    public function manageInscriptionLink($type)
    {
        $person = auth()->user()?->getRelatedMainPerson();

        if($person) $this->inscription?->updatePersonId($person->id);
        $this->inscription?->updateType($type);

        if ($this->inscription) {
            return redirect()->to($this->inscription?->getRegistrationUrl());
        } else if (auth()->user()) {
            return redirect()->to(InscriptionModel::createOrGetRegistrationUrl($person->id, null, $type));
    	}  else {
    		return redirect()->route('inscription.email.step1', [
                'inscription_code' => $this->inscriptionCode,
                'type' => $type,
            ]);
    	}
    }
}