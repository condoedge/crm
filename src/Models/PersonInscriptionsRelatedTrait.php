<?php

namespace Condoedge\Crm\Models;

trait PersonInscriptionsRelatedTrait
{
    /* RELATIONS */
    public function registeredBy()
    {
        return $this->belongsTo(config('condoedge-crm.person-model-namespace'), 'registered_by');
    }

    public function registeredBys()
    {
        return $this->hasMany(config('condoedge-crm.person-model-namespace'), 'registered_by');
    }

    /* CALCULATED FIELDS */
    public function getInscriptionPersonRoute($qrCode = null)
    {
        return \URL::signedRoute('inscription.person', [
            'id' => $this->id,
            'qr_code' => $qrCode,
        ]);
    }

    public function getInscriptionPersonLinkRoute($qrCode = null, $personLinkId = null)
    {
        return \URL::signedRoute('inscription.person-link', [
            'person_id' => $this->id,
            'id' => $this->registeredBys()->value('id') ?: $personLinkId, //In case the user clicked back and then continued
            'qr_code' => $qrCode,
        ]);
    }

    public function getInscriptionRouteAsPerson2()
    {
        return \URL::signedRoute('inscription.person-link', [
            'person_id' => $this->registered_by,
            'id' => $this->id,
        ]);
    }

    public function getInscriptionTeamRoute($qrCode = null)
    {
        if ($registrable = registrableFromQrCode($qrCode)) {
            return $registrable->getRegistrableConfirmationRoute($this->id);
        }

        return \URL::signedRoute('inscription.team', [
            'id' => $this->id,
        ]);
    }

    public function getInscriptionMemberRoute()
	{
		return \URL::signedRoute('inscription.person-link', ['person_id' => $this->id]);
	}

    /* ACTIONS */
    public static function getOrCreatePersonFromEmail($email)
    {
        $person = Person::where('email_identity', $email)->latest()->first();

        if (!$person) {
            $person = Person::createPersonFromEmail($email);
        }

        return $person;
    }

    public static function createPersonFromEmail($email)
    {
        $person = Person::newPersonFromEmail($email);
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
