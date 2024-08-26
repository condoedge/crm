<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\Person;

trait HasManyPersonsTrait
{
	/* RELATIONS */
    public function persons()
    {
        return $this->hasMany(PersonModel::getClass());
    }

    public function latestPersons()
    {
        return $this->persons()->latest();
    }

    /* SCOPES */

	/* CALCULATED FIELDS */
    public function getRelatedMainPerson()
    {
        //Todo review logic here
        $person = $this->latestPersons()->first();

        if (!$person) {
            $person = $this->createPersonFromUser();
        }

        return $person;
    }

    public function createPersonFromUser()
    {
        $person = Person::newPersonFromEmail($this->email);
        $person->user_id = $this->id;
        $person->first_name = $this->first_name;
        $person->last_name = $this->last_name;
        $person->save();

        return $person;
    }

	/* ACTIONS */

	/* ELEMENTS */
}
