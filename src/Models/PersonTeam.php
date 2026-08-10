<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Facades\PersonTeamTypeEnumGlobal;
use Condoedge\Utils\Models\Model;
use Kompo\Auth\Facades\RoleModel;
use Kompo\Auth\Models\Teams\TeamRole;

class PersonTeam extends Model
{
    use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;
    use \Condoedge\Crm\Models\BelongsToPersonTrait;

    protected $fillable = [
        'team_role_id',
    ];

    protected $casts = [
        'status' => PersonTeamStatusEnum::class,
        'from' => 'datetime',
        'to' => 'datetime',
        'role_type' => PersonTeamTypeEnum::class,
    ];

    public static function boot()
    {
        parent::boot();

        // "Valid" = currently held. A membership whose `to` has passed is hidden by default,
        // the same way terminated team roles are, so nothing has to fire when a date passes.
        // Genuine deletion stays with SoftDeletes; opt out with
        // ->withoutGlobalScopes(['validPersonTeam']).
        static::addGlobalScope('validPersonTeam', function ($builder) {
            static::applyValidConditions($builder);
        });

        static::saving(function ($model) {
            /** For now these are redundant fields (role_id, role_name).
             *  But it could be useful if we have people without user so without team_role
             *  here we set it to have all of them syncronized
             */
            if ($model->isDirty('team_role_id')) {
                $model->role_id = $model->teamRole?->role;
            }

            if ($model->isDirty('role_id')) {
                $model->role_name = $model->teamRole?->roleRelation?->name;
            }
        });
    }

    /* RELATIONS */
    public function teamRole()
    {
        return $this->belongsTo(TeamRole::class);
    }

    public function teamRoleIncludingDeleted()
    {
        // Argument-less withoutGlobalScopes() already strips SoftDeletingScope, so the
        // withTrashed() that used to be here was a no-op.
        return $this->teamRole()->withoutGlobalScopes();
    }

    public function role()
    {
        return $this->belongsTo(RoleModel::getClass(), 'role_id');
    }

    /* SCOPES */

    /**
     * Single source of truth for "currently held" memberships (`to` NULL or in the future).
     * Shared by the `validPersonTeam` global scope, scopeValid() and scopeActive().
     *
     * Deliberately does NOT filter deleted_at: SoftDeletes owns that, and folding it in here
     * would silently defeat withTrashed().
     *
     * Table-qualified by default because this runs inside joins (Person::getUniqueTeamsWithRoles,
     * DailyPersonTimeTrackingCommand's joinSub). Pass an alias for aliased SQL, '' for CTE bodies.
     */
    public static function applyValidConditions($query, ?string $alias = null): void
    {
        $prefix = $alias === null ? 'person_teams.' : ($alias === '' ? '' : $alias . '.');

        $query->where(fn ($q) => $q->whereNull($prefix . 'to')
            ->orWhere($prefix . 'to', '>', now()));
    }

    /** Raw-SQL twin of applyValidConditions(), for literal SQL where no builder exists. */
    public static function validSqlFragment(string $alias = 'pt'): string
    {
        $prefix = $alias === '' ? '' : $alias . '.';

        return "({$prefix}`to` IS NULL OR {$prefix}`to` > NOW())";
    }

    public function scopeValid($query)
    {
        static::applyValidConditions($query);
    }

    /** Kept by name: widely called, and still meaningful after a withoutGlobalScopes() opt-out. */
    public function scopeActive($query)
    {
        static::applyValidConditions($query);
    }

    /**
     * Memberships that expired (or were soft deleted) while their team role is
     * still live.
     */
    public function scopePendingTeamRoleSync($query)
    {
        return $query->withTrashed()->withoutGlobalScopes(['validPersonTeam'])
            ->where(fn ($q) => $q
                ->whereRaw('person_teams.to is not null and DATE(person_teams.to) <= CURDATE()')
                ->orWhereNotNull('person_teams.deleted_at'))
            // validTeamRole already means "not terminated, not suspended" — delegating instead
            // of restating it is also how those two definitions had drifted apart.
            ->whereHas('teamRole');
    }

    /* CALCULATED FIELDS */

    /**
     * The status to DISPLAY, as opposed to the one stored in the column.
     *
     * The two dimensions are deliberately split:
     *
     *  - stored `status` carries the payment/vetting dimension — PENDING_PAYMENT, PROBATION,
     *    ACTIVE. Those are real stateful facts about money and background checks, set at the
     *    events that change them, and they need to stay filterable and sortable in SQL.
     *  - the lifecycle dimension is DERIVED, from exactly the predicate validPersonTeam uses.
     *    A membership is over when `to` has passed, whether or not anyone called terminate().
     *
     * Without this split the column lies: TERMINATED is only ever written by terminate(), so a
     * position that simply lapsed kept rendering a green "Active" pill forever.
     *
     * Camp derives its whole status this way (CampStatusEnum::getStatusFromEvent). We derive
     * only the lifecycle half, because PENDING_PAYMENT and PROBATION would each need the
     * linked inscription and the person's latest background check — an N+1 on every roster,
     * export and census, and unfilterable in SQL. `to` and `deleted_at` are on the row, so
     * this costs nothing.
     *
     * Null status keeps returning null so callers can fall back to the team role's own pill.
     */
    public function getEffectiveStatus(): ?PersonTeamStatusEnum
    {
        if (!$this->status) {
            return null;
        }

        if ($this->deleted_at || ($this->to && $this->to->isPast())) {
            return PersonTeamStatusEnum::TERMINATED;
        }

        return $this->status;
    }

    public function getRoleName()
    {
        return $this->teamRoleIncludingDeleted?->roleRelation?->name ?: $this->occupation ?: __('crm.unknown');
    }

    /* ACTIONS */
    public function terminate($at = null)
    {
        $at = $at ?: now();

        // Never push an already-past end date forward: SyncTeamRolesCommand sweeps rows whose
        // `to` has already passed, and PersonTeamList::terminatePersonRoles can bulk-hit
        // historical rows when show_inactive is on. Without this guard every historical end
        // date walks forward and every bar in MembersEvolutionService shifts.
        if (is_null($this->to) || $this->to->gt($at)) {
            $this->to = $at;
        }

        $this->status = PersonTeamStatusEnum::TERMINATED;
        $this->save();

        // withTrashed() would NOT lift validTeamRole; name the mask being lifted.
        $this->teamRole()->withoutGlobalScopes(['validTeamRole'])->first()?->terminate();
    }

    public function moveToAnotherUnit($teamId)
    {
        $this->team_id = $teamId;
        $this->save();

        $teamRole = $this->teamRole;
        $teamRole->team_id = $teamId;
        $teamRole->save();
    }

    public static function createFromTeamRole($teamRole, $status = null, $expirationDate = null, $inscription = null, $personTeamType = null, $startDate = null)
    {
        // Re-use lookup: it must find the existing row even when the membership has ended,
        // or a renewal mints a duplicate person_teams row for the same team_role_id.
        if ($personTeam = static::withoutGlobalScopes(['validPersonTeam'])->where('team_role_id', $teamRole->id)->first()) {
            $personTeam->status = $status ?? $personTeam->status;
            $personTeam->to = $expirationDate;
            $personTeam->role_type = $personTeamType ?? $personTeam->role_type ?? PersonTeamTypeEnumGlobal::getByRole($teamRole->id);
            $personTeam->last_inscription_id = $inscription?->id ?? $personTeam->last_inscription_id;
            $personTeam->inscription_type = $inscription?->type?->value ?? $personTeam->inscription_type;
            $personTeam->save();

            return $personTeam;
        }

        $personTeam = new static();
        $personTeam->status = $status ?? PersonTeamStatusEnum::ACTIVE;
        $personTeam->team_role_id = $teamRole->id;
        $personTeam->person_id = PersonModel::where('user_id', $teamRole->user_id)->first()->id;
        $personTeam->team_id = $teamRole->team_id;
        $personTeam->from = $startDate ?: now();
        $personTeam->to = $expirationDate;
        $personTeam->role_type = $personTeamType ?? PersonTeamTypeEnumGlobal::getByRole($teamRole->id);
        $personTeam->inscription_type = $inscription?->type?->value;
        $personTeam->last_inscription_id = $inscription?->id;
        $personTeam->save();

        return $personTeam;
    }

    public static function getOrCreateForAdultInscription($inscription, $teamRole)
    {
        $personId = $inscription->person->getRegisteringPerson()->id;
        $newYear = $inscription->event?->scout_year;
        $inscriptionTypeValue = $inscription->type?->value;

        // Same reasoning as createFromTeamRole: a re-inscription must reuse last year's
        // (now expired) membership rather than create a second one.
        $personTeam = static::withoutGlobalScopes(['validPersonTeam'])
            ->where('person_id', $personId)->where('team_id', $inscription->team_id)
            ->where(fn ($q) => $q->where('inscription_type', $inscriptionTypeValue)->orWhereNull('inscription_type'))
            ->where(
                fn ($q) => $q->whereNull('team_role_id')
                ->orWhere('team_role_id', $teamRole->id)
            )->first();

        if (!$personTeam) {
            $personTeam =  static::createFromTeamRole($teamRole, expirationDate: $inscription->getExpirationDate(), inscription: $inscription);
        } else {
            $personTeam->team_role_id = $teamRole->id;
        }

        $personTeam->role_type = $inscription->type->getAdultPersonTeamType();
        $personTeam->status = $inscription->type->getSpecificPersonTeamStatus($inscription);
        $personTeam->to = $inscription->getExpirationDate();
        $personTeam->inscription_type = $inscriptionTypeValue;
        $personTeam->last_inscription_id = $inscription->id;
        $personTeam->save();

        return $personTeam;
    }

    public function markAsPaid()
    {
        $this->status = PersonTeamStatusEnum::ACTIVE;
        $this->save();
    }

    // We're using HasSecurity plugin that handles deleting event to manage security restrictions.
    public function deletable()
    {
        return true;
    }

    public function delete()
    {
        $this->teamRole?->delete();

        return parent::delete();
    }

    /* ELEMENTS */
    public function getStatusPillElement()
    {
        if (!$status = $this->getEffectiveStatus()) {
            return null;
        }

        return _Pill($status->label())->class($status->color())->class('text-white');
    }
}
