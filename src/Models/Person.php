<?php

namespace Condoedge\Crm\Models;

use App\Models\User;
use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Facades\RoleModel;
use Kompo\Auth\Models\Contracts\Searchable;
use Kompo\Auth\Models\Email\Email;
use Kompo\Auth\Models\Model;

abstract class Person extends Model implements Searchable
{
    use \Kompo\Auth\Models\Email\MorphManyEmails;
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

    public function save(array $options = [])
    {
        if ($this->email_identity && $this->getDirty('email_identity') && ($email = $this->emails()->where('address_em', $this->getOriginal("email_identity"))->first())) {
            $email->address_em = $this->email_identity;
            $email->save();
        }

        if ($this->exists && $this->email_identity && !$this->emails()->count()) {
            Email::createMainFor($this, $this->email_identity);
        }

        parent::save($options);
    }


    /* RELATIONS */
    public function personEvents()
    {
        return $this->hasMany(PersonEvent::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function diciplinaryActions()
    {
        return $this->hasMany(DiciplinaryAction::class);
    }

    /* SCOPES */
    public function scopeActive($query, $teamId = null)
    {
        return $query->whereHas(
            'personTeams',
            fn ($q) => $q->whereNull('to')
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
        );
    }

    public function scopeAddFullName($query)
    {
        $query->selectRaw("id, CONCAT(first_name,' ',last_name) as person_full_name");
    }

    public function scopeParent($query)
    {
        return $query->whereDoesntHave('person2Links');
    }

    public function scopeChild($query)
    {
        return $query->whereHas('person2Links');
    }

    public function scopeHasActiveTeam($query)
    {
        return $query->whereHas('personTeams', fn ($q) => $q->active());
    }

    public function scopeOnlyInThisTeam($query, $teamId = null)
    {
        return $query->whereHas('personTeams', fn ($q) => $q->where('team_id', $teamId ?? currentTeamId()));
    }

    public function scopeSearchByEmail($query, $email)
    {
        return $query->where(
            fn ($q) => $q->where('email_identity', $email)
            ->orWhereHas('emails', fn ($q) => $q->where('address_em', $email))
        );
    }

    /* CALCULATED FIELDS */
    public function getAllPersonLinks()
    {
        return $this->person1Links()->with('person2')->get()->concat(
            $this->person2Links()->with('person1')->get()
        )->map(fn ($pl) => $pl->setOtherAsPerson($this->id));
    }

    public function getFullNameAttribute()
    {
        return getFullName($this->first_name, $this->last_name);
    }

    public function hasActiveBlock()
    {
        return $this->diciplinaryActions()->active()->blockType()->exists();
    }

    public function hasActiveBan()
    {
        return $this->diciplinaryActions()->active()->banType()->exists();
    }

    public function getActivityStatus()
    {
        if ($this->hasActiveBan()) {
            return 'crm.banned';
        }

        if ($this->hasActiveBlock()) {
            return 'crm.blocked';
        }

        if (!$this->personTeams->count()) {
            return 'crm.pending';
        }

        if ($this->personTeams->whereNull('to')->first()) {
            return 'crm.active';
        }

        return 'crm.inactive';
    }

    public static function getOptionsForTeamWithFullName($teamId)
    {
        return static::active($teamId)->addFullName()->pluck('person_full_name', 'id');
    }

    public function getRegisteringPerson()
    {
        return $this->registeredBy ?: $this;
    }

    public function getRegisteringPersonEmail()
    {
        return $this->getRegisteringPerson()->email_identity;
    }

    /* ACTIONS */
    public static function retrieveByEmailIdentity($email)
    {
        $person = PersonModel::searchByEmail($email)->first();

        if (!$person) {
            $person = PersonModel::newPersonFromEmail($email);
        }

        return $person;
    }

    public function constructFakeEmail()
    {
        return \Str::slug($this->full_name) . '@user.coolecto.com';
    }

    public function createOrGetUserByRegisteredBy($inscription, $team)
    {
        $user = User::where('email', $this->email ?: $this->constructFakeEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $this->full_name,
                'email' => $this->constructFakeEmail(), // TODO we could do the email nullable
                'password' => bcrypt(value: \Str::random(12)),
            ]);
        }

        // Get the role for the children getting first the child inscription type
        if ($role = $inscription->type->getRegisteredByRole()?->getRole($inscription)) {
            RoleModel::getOrCreate($role);

            $teamRole = $user->createTeamRole($team, $role);

            PersonTeam::where('person_id', $this->id)->where('team_id', $team->id)->whereNull('team_role_id')
                ->update(['team_role_id' => $teamRole->id]);
        }

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
            _LabelWithIcon('profile', $this->full_name),
            !$phone ? null : _PhoneWithIcon($phone),
            !$this->email_identity ? null : _EmailWithIcon($this->email_identity),
            !$address ? null : _AddressWithIcon($address),
        )->class('mb-3');
    }
}
