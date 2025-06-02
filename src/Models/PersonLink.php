<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Models\Model;

class PersonLink extends Model
{
    /* RELATIONS */
    public function person1()
    {
        return $this->belongsTo(PersonModel::getClass(), 'person1_id');
    }

    public function person2()
    {
        return $this->belongsTo(PersonModel::getClass(), 'person2_id');
    }

    public function linkType()
    {
        return $this->belongsTo(LinkType::class, 'link_type_id');
    }

    /* SCOPES */

    /* CALCULATED FIELDS */

    /* ACTIONS */
    public static function upsertLinkBetween($person1, $person2, $linkTypeId)
    {
        $personLink = PersonLink::getLinkBetween($person1, $person2);

        if ($personLink) {
            $personLink->link_type_id = $linkTypeId;
            $personLink->save();
        } else {
            $personLink = PersonLink::createLinkBetween($person1, $person2, $linkTypeId);
        }

        return $personLink;
    }

    public static function createLinkBetween($person1, $person2, $linkTypeId)
    {
        $personLink = new PersonLink();
        $personLink->person1_id = $person1->id;
        $personLink->person2_id = $person2->id;
        $personLink->link_type_id = $linkTypeId;
        $personLink->save();

        return $personLink;
    }

    public function setOtherAsPerson($mainPersonId)
    {
        if ($this->person2_id == $mainPersonId) {
            $this->person = $this->person1;
        }
        if ($this->person1_id == $mainPersonId) {
            $this->person = $this->person2;
        }

        return $this;
    }

    public static function getLinkBetween($person1, $person2)
    {
        return self::where(function ($query) use ($person1, $person2) {
            $query->where('person1_id', $person1->id)->where('person2_id', $person2->id);
        })->orWhere(function ($query) use ($person1, $person2) {
            $query->where('person1_id', $person2->id)->where('person2_id', $person1->id);
        })->first();
    }

    /* ELEMENTS */
}
