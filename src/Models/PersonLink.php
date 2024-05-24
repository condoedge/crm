<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Models\Person;
use Kompo\Auth\Models\Model;

class PersonLink extends Model
{
	/* RELATIONS */
	public function person1()
	{
		return $this->belongsTo(Person::class, 'person1_id');
	}

	public function person2()
	{
		return $this->belongsTo(Person::class, 'person2_id');
	}

	/* SCOPES */

	/* CALCULATED FIELDS */

	/* ACTIONS */
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
