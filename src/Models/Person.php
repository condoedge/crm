<?php

namespace Condoedge\Crm\Models;

use App\Models\Teams\Team;
use App\Models\User;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Models\ContactInfo\Email\Email;
use Condoedge\Utils\Models\Contracts\Searchable;
use Condoedge\Utils\Models\Model;
use Illuminate\Support\Facades\DB;
use Kompo\Auth\Facades\RoleModel;

abstract class Person extends Model implements Searchable
{
    use \Condoedge\Utils\Models\ContactInfo\Email\MorphManyEmails;
    use \Condoedge\Utils\Models\ContactInfo\Maps\MorphManyAddresses;
    use \Condoedge\Utils\Models\ContactInfo\Phone\MorphManyPhones;
    use \Condoedge\Utils\Models\Files\MorphManyFilesTrait;

    use \Condoedge\Crm\Models\PersonInscriptionsRelatedTrait;
    use \Condoedge\Crm\Models\PersonCharacteristicsRelatedTrait;

    protected $casts = [
        'gender' => GenderEnum::class,
        'spoken_languages' => 'array',
        'date_of_birth' => 'date',
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

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    /* SCOPES */
    public function scopeActive($query, $teamId = null)
    {
        return $query->whereHas(
            'personTeams',
            fn($q) => $q->whereNull('to')
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
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
        return $query->whereHas('personTeams', fn($q) => $q->active());
    }

    public function scopeOnlyInThisTeam($query, $teamId = null)
    {
        return $query->whereHas('personTeams', fn($q) => $q->where('team_id', $teamId ?? currentTeamId()));
    }

    public function scopeSearchByEmail($query, $email)
    {
        return $query->where(
            fn($q) => $q->where('email_identity', $email)
                ->orWhereHas('emails', fn($q) => $q->where('address_em', $email))
        );
    }

    public function scopeUserOwnedRecords($query)
    {
        $currentUserId = auth()->id();

        if (!$currentUserId) {
            return $query->whereRaw('1 = 0'); // Return no results if no user authenticated
        }

        // Get person IDs that the current user can manage
        $directPersonIds = static::where('user_id', $currentUserId)->pluck('id');
        // Get person IDs linked through person1Links (where current user owns person2)
        $linkedThroughPerson1 = DB::table('person_links as pl1')
            ->join('persons as p2', 'pl1.person2_id', '=', 'p2.id')
            ->where('p2.user_id', $currentUserId)
            ->pluck('pl1.person1_id');

        // Get person IDs linked through person2Links (where current user owns person1)
        $linkedThroughPerson2 = DB::table('person_links as pl2')
            ->join('persons as p1', 'pl2.person1_id', '=', 'p1.id')
            ->where('p1.user_id', $currentUserId)
            ->pluck('pl2.person2_id');

        // Get sibling access if child_can_access_siblings is enabled
        $siblingIds = DB::table('person_links as pl_parent')
            ->join('person_links as pl_sibling', 'pl_parent.person1_id', '=', 'pl_sibling.person1_id')
            ->join('link_types as lt', 'pl_parent.link_type_id', '=', 'lt.id')
            ->join('persons as p_current', 'pl_parent.person2_id', '=', 'p_current.id')
            ->where('p_current.user_id', $currentUserId)
            ->where('lt.child_can_access_siblings', 1)
            ->where('pl_sibling.person2_id', '!=', 'pl_parent.person2_id')
            ->pluck('pl_sibling.person2_id');

        // Combine all accessible person IDs
        $accessiblePersonIds = $directPersonIds
            ->concat($linkedThroughPerson1)
            ->concat($linkedThroughPerson2)
            ->concat($siblingIds)
            ->unique()
            ->values();

        return $query->whereIn('id', $accessiblePersonIds);
    }

    /* CALCULATED FIELDS */
    public function getAllPersonLinks()
    {
        return $this->person1Links()->with('person2')->get()->concat(
            $this->person2Links()->with('person1')->get()
        )->map(fn($pl) => $pl->setOtherAsPerson($this->id));
    }

    public function getRelatedLinksOfPersonLinks()
    {
        return $this->getAllPersonLinks()->flatMap(fn($pl) => $pl->person->getAllPersonLinks())->unique(fn($q) => $q->person2_id)->filter(fn($q) => !in_array($this->id, [$q->person1_id, $q->person2_id], true) && $q->linkType?->child_can_access_siblings == 1);
    }

    public function getFullNameAttribute()
    {
        return getFullName($this->first_name, $this->last_name);
    }

    public function getYearsOldAttribute()
    {
        return $this->date_of_birth?->age;
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
        $team = Team::findOrFail($teamId);

        return static::forTeams($team->getAllChildrenRawSolution())->addFullName()->pluck('person_full_name', 'id');
    }

    public function getRegisteringPerson()
    {
        return $this->registeredBy ?: $this;
    }

    public function getRegisteringPersonEmail()
    {
        return $this->getRegisteringPerson()->email_identity;
    }

    public function usersIdsAllowedToManage()
    {
        return array_merge(
            [$this->relatedUser?->id],
            $this->getRelatedLinksOfPersonLinks()->map(fn($pl) => $pl->person->user_id)->filter()->all(),
            $this->getAllPersonLinks()->map(fn($pl) => $pl->person->user_id)->filter()->all(),
        );
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
        return \Str::slug($this->full_name) . $this->id . '@user.coolecto.com';
    }

    public function getLinkToTeam($teamId)
    {
        return $this->personTeams()->active()->where('team_id', $teamId)->first();
    }

    public function createOrGetUserByRegisteredBy($inscription, $team)
    {
        $user = User::where('email', $this->email_identity ?: $this->constructFakeEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $this->full_name,
                'email' => $this->email_identity ?: $this->constructFakeEmail(), // TODO we could do the email nullable
                'password' => bcrypt(value: \Str::random(12)),
            ]);
        }

        $person = $inscription->person;
        $person->user_id = $user->id;
        $person->save();

        // Get the role for the children getting first the child inscription type
        if ($role = $inscription->type->getRegisteredByRole()?->getRole($inscription)) {
            RoleModel::getOrCreate($role);

            $teamRole = $user->createTeamRole($team, $role);
            // $teamRole->terminated_at = $inscription->getExpirationDate();

            PersonTeam::createFromTeamRole($teamRole, $inscription->type->getSpecificPersonTeamStatus($inscription), $inscription->getExpirationDate(), $inscription, $inscription->type->getChildPersonTeamType());
        }

        PersonEvent::createPersonEvent($person, $inscription->getEventToAttend());
    }

    /* SEARCHABLE */
    public function scopeSearch($query, $search)
    {
        $query->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", [wildcardSpace($search)])
            ->orWhereRaw("CONCAT(last_name,' ',first_name) LIKE ?", [wildcardSpace($search)]);
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
    /**
     * @param string[] $infoToShow The info to show in the labels. Possible options
     *                             are 'address', 'phone', 'email', 'team'
     */
    public function nameAndContactLabelsEl($infoToShow = ['address', 'phone', 'email'])
    {
        $phone = in_array('phone', $infoToShow, true) ? $this->getFirstValidPhoneLabel() : null;
        $address = in_array('address', $infoToShow, true) ? $this->getFirstValidAddressLabel() : null;
        $email = in_array('email', $infoToShow, true) ? $this->email_identity : null;
        $team = in_array('team', $infoToShow, true) ? $this->getLastTeam() : null;

        return _Rows(
            _LabelWithIcon('profile', $this->full_name),
            !$team ? null : _Flex(
                _LabelWithIcon(
                    'pet',
                    _Html($team['team']->getCompleteTeamsLabel())->class('text-xs'),
                )->class('items-center !mb-0'),
                $team['pending'] ? _Pill('crm.pending')->class('bg-warning text-white !py-1 !px-3') : null,
            )->class('gap-2'),
            !$phone ? null : _PhoneWithIcon($phone),
            !$email ? null : $this->emailContactEl($email),
            !$address ? null : _AddressWithIcon($address),
        )->class('mb-3');
    }

    public function securityRelatedTeamIds()
    {
        return $this->personTeams()->active()->pluck('team_id')->unique();
    }

    protected function emailContactEl($email) //Override in project
    {
        return _EmailWithIcon($email);
    }

    public function getLastTeam()
    {
        $lastInscription = $this->inscriptions()->whereNotNull('team_id')->latest()->first();

        $lastPersonTeam = $this->personTeams()->active()->latest()->first();

        $entity = $lastInscription?->created_at > $lastPersonTeam?->created_at ? $lastInscription : $lastPersonTeam;

        if (!$entity) {
            return null;
        }

        return [
            'team' => $entity->team,
            'pending' => $entity instanceof Inscription ? true : false,
        ];
    }
}
