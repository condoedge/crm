<?php

namespace Condoedge\Crm\Models;

use App\Models\User;
use Kompo\Auth\Models\Contracts\Searchable;
use Kompo\Auth\Models\Model;

class Person extends Model implements Searchable
{
	use \Kompo\Auth\Models\Maps\MorphManyAddresses;
	use \Kompo\Auth\Models\Phone\MorphManyPhones;
	use \Kompo\Auth\Models\Files\MorphManyFilesTrait;

	use \Condoedge\Crm\Models\PersonInscriptionsRelatedTrait;
	use \Condoedge\Crm\Models\PersonCharacteristicsRelatedTrait;

	protected $casts = [
		'gender' => GenderEnum::class,
		'spoken_languages' => 'array',
	];

	protected $table = 'persons';


	/* RELATIONS */
	public function personEvents()
	{
		return $this->hasMany(PersonEvent::class);
	}

	public function relatedUser()
	{
		return $this->hasOne(User::class);
	}

	public function person1Links()
	{
		return $this->hasMany(PersonLink::class, 'person1_id');
	}

	public function person2Links()
	{
		return $this->hasMany(PersonLink::class, 'person2_id');
	}

	public function personTeams()
	{
		return $this->hasMany(PersonTeam::class);
	}

	/* SCOPES */

	/* CALCULATED FIELDS */
	public function getAllPersonLinks()
	{
		return $this->person1Links()->with('person2')->get()->concat(
			$this->person2Links()->with('person1')->get()
		)->map(fn($pl) => $pl->setOtherAsPerson($this->id));
	}

	public function getFullNameAttribute()
	{
		return getFullName($this->first_name, $this->last_name);
	}

	public function getActivityStatus()
	{
		if (!$this->personTeams->count()) {
			return 'En attente';
		}

		if ($this->personTeams->whereNull('to')->first()) {
			return 'Active';
		}

		return 'Inactive';
	}

	/* ACTIONS */
	public static function retrieveByEmailIdentity($email)
	{
		$person = Person::where('email_identity', $email)->first();

		if (!$person) {
			$person = Person::newPersonFromEmail($email);
		}

		return $person;
	}

	/* SEARCHABLE */
    public function scopeSearch($query, $search)
    {
    	$query->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", [ wildcardSpace($search)])
            ->orWhereRaw("CONCAT(last_name,' ',first_name) LIKE ?", [ wildcardSpace($search)]);
    }

    public function searchElement($result, $search)
    {
        return _SearchResult(
            $search,
            $result->full_name,
            [
                _TextSmGray($result->email_identity),
            ],
        )->redirect('member.page', ['id' => $result->id]);
    }

	/* ELEMENTS */
	public function nameAndContactLabelsEl()
	{
		$phone = $this->getFirstValidPhoneLabel();
		$address = $this->getFirstValidAddressLabel();

		return _Rows(
            _TitleMiniStandard($this->full_name)->class('mb-2'),
			!$phone ? null : _PhoneWithIcon($phone),
			!$this->email_identity ? null : _EmailWithIcon($this->email_identity),
            !$address ? null : _AddressWithIcon($address),
		);
	}
}
