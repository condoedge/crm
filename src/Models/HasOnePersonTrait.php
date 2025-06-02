<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;

trait HasOnePersonTrait
{
    /* RELATIONS */
    public function person()
    {
        return $this->hasOne(PersonModel::getClass());
    }

    /* SCOPES */

    /* CALCULATED FIELDS */
    public function getRelatedMainPerson()
    {
        $person = $this->person;

        if (!$person) {
            $person = $this->createPersonFromUser();
        }

        return $person;
    }

    public function createPersonFromUser()
    {
        $person = PersonModel::newPersonFromEmail($this->email);
        $person->user_id = $this->id;
        $person->first_name = $this->first_name;
        $person->last_name = $this->last_name;
        $person->systemSave();

        return $person;
    }

    /* ACTIONS */

    /* ELEMENTS */
}
