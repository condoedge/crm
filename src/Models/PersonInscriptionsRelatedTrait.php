<?php

namespace Condoedge\Crm\Models;

use App\Models\Crm\Person;
use Condoedge\Crm\Facades\PersonModel;

trait PersonInscriptionsRelatedTrait
{
    /* RELATIONS */
    public function registeredBy()
    {
        return $this->belongsTo(PersonModel::getClass(), 'registered_by')->throughAuthorizedRelation();
    }

    public function registeredBys()
    {
        return $this->hasMany(PersonModel::getClass(), 'registered_by')->throughAuthorizedRelation();
    }

    /* CALCULATED FIELDS */
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

    /* SCOPES */

}
