<?php

namespace Condoedge\Crm\Models;

use App\Models\Crm\Person;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;

trait PersonInscriptionsRelatedTrait
{
    /* RELATIONS */
    public function registeredBy()
    {
        return $this->belongsTo(PersonModel::getClass(), 'registered_by');
    }

    public function registeredBys()
    {
        return $this->hasMany(PersonModel::getClass(), 'registered_by');
    }

    /* CALCULATED FIELDS */
    public function getInscriptionFromPersonLinkRoute($qrCode = null)
    {
        return \URL::signedRoute('inscription.from-person-link', [
            'id' => $this->id,
            'qr_code' => $qrCode,
        ]);
    }

    public function getInscriptionTeamRoute($inscription = null)
    {
        if ($inscription && ($registrable = registrableFromQrCode($inscription->qr_inscription))) {
            return $inscription->getInscriptionConfirmationRoute($this->id, $registrable->getRegistrableId());
        }

        return \URL::signedRoute('inscription.team', [
            'inscription_id' => $inscription->id,
            'id' => $this->id,
        ]);
    }

    public static function getSameInscriptionPersons($inscriptionId)
    {
        return PersonModel::where('inscription_id', $inscriptionId)->get();
    }

    public function getPreviousInscriptionPerson($inscriptionId)
    {
        $sortedPersons = static::getSameInscriptionPersons($inscriptionId)->sortByDesc('id');

        if ($this->id) {
            $sortedPersons = $sortedPersons->where('id', '<', $this->id);
        }
        return $sortedPersons->first();
    }

    /* ACTIONS */
    public static function getOrCreatePersonFromEmail($email)
    {
        $person = PersonModel::where('email_identity', $email)->latest()->first();

        if (!$person) {
            $person = PersonModel::createPersonFromEmail($email);
        }

        return $person;
    }

    public static function createPersonFromEmail($email)
    {
        $person = PersonModel::newPersonFromEmail($email);
        $person->save();

        return $person;
    }

    public static function newPersonFromEmail($email)
    {
        $person = new Person();
        $person->email_identity = $email;

        return $person;
    }

    public function createOrUpdateInscription($qrCode, $type = null)
    {
        $inscription = InscriptionModel::where('inscribed_by', $this->id)->where('qr_inscription', $qrCode)->first();

        if (!$inscription) {
            $inscription = new (InscriptionModel::getClass());
            $inscription->type = $type;
            $inscription->inscribed_by = $this->id;
            $inscription->setNewQrCode($qrCode);
            $inscription->save();
        }

        return $inscription;
    }

    /* SCOPES */

}
