<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Kompo\Auth\Contracts\Security\HasPermissionKey;
use Kompo\Auth\Contracts\Security\ScopedToTeam;

class PersonLink extends Model implements HasPermissionKey, ScopedToTeam
{
    public function getPermissionKey(): string
    {
        return 'Person.sensibleRelationships';
    }

    /* RELATIONS */
    public function person1()
    {
        return $this->belongsTo(PersonModel::getClass(), 'person1_id')->withoutGlobalScope('authUserHasPermissions');
    }

    public function person2()
    {
        return $this->belongsTo(PersonModel::getClass(), 'person2_id')->withoutGlobalScope('authUserHasPermissions');
    }

    public function linkType()
    {
        return $this->belongsTo(LinkType::class, 'link_type_id');
    }

    /* SCOPES */
    public function applyTeamSecurityScope(Builder $query, array $teamIds): void
    {
        $personPrototype = new (PersonModel::getClass());

        $query->where(fn ($outer) => $outer
            ->whereHas('person1', fn ($q) => $personPrototype->applyTeamSecurityScope($q, $teamIds))
            ->orWhereHas('person2', fn ($q) => $personPrototype->applyTeamSecurityScope($q, $teamIds))
        );
    }

    public function getRelatedTeamIds(): array
    {
        return collect()
            ->concat($this->person1?->getRelatedTeamIds() ?? [])
            ->concat($this->person2?->getRelatedTeamIds() ?? [])
            ->unique()
            ->values()
            ->all();
    }

    /* CALCULATED FIELDS */
    public function getLinkedPerson($forPersonId)
    {
        return $this->person1_id == $forPersonId ? 
            $this->person2 : 
            $this->person1;
    }

    public function getLinkingLabel($forPersonId)
    {
        return $this->linkType->getLinkingLabel($forPersonId);
    }

    public function anotherPersonIsEmergencyContactOf($personId)
    {
        $orderColToCheck = $this->getEmergencyContactOrderColumn($personId);

        return $this->$orderColToCheck !== null;
    }

    public function getEmergencyContactOrderForPerson($personId)
    {
        $orderColToCheck = $this->getEmergencyContactOrderColumn($personId);

        return $this->$orderColToCheck;
    }

    public function getEmergencyContactOrderColumn($personId)
    {
        return $this->person1_id == $personId ? 'emergency_contact_order_of_p1' : 'emergency_contact_order_of_p2';
    }

    public static function getLinkBetween($person1, $person2)
    {
        return self::where(function ($query) use ($person1, $person2) {
            $query->where('person1_id', $person1->id)->where('person2_id', $person2->id);
        })->orWhere(function ($query) use ($person1, $person2) {
            $query->where('person1_id', $person2->id)->where('person2_id', $person1->id);
        })->first();
    }

    public function isParentOfTheAnother($personId)
    {
        return $this->linkType->is_parent && $this->person1_id == $personId
            || $this->linkType->is_child && $this->person2_id == $personId;
    }

    public function isChildOfTheAnother($personId)
    {
        return $this->linkType->is_child && $this->person1_id == $personId
            || $this->linkType->is_parent && $this->person2_id == $personId;
    }

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

    /* ELEMENTS */
}
