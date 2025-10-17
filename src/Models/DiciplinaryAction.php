<?php

namespace Condoedge\Crm\Models;

use Condoedge\Utils\Models\Model;

class DiciplinaryAction extends Model
{
    use BelongsToPersonTrait;

    protected $casts = [
        'action_from' => 'date',
        'action_to' => 'date',

        'action_type' => DiciplinaryActionTypeEnum::class,
        'action_reason_type' => DiciplinaryReasonTypeEnum::class,
    ];

    // SCOPE
    public function scopeActive($query)
    {
        return $query->where(
            fn ($q) => $q->whereDate('action_from', '<=', now())
                ->where(fn ($q) => $q->whereDate('action_to', '>', now())
                    ->orWhereNull('action_to'))
        );
    }

    public function scopeBlockType($query)
    {
        return $query->where('action_type', DiciplinaryActionTypeEnum::BLOCK);
    }

    public function scopeBanType($query)
    {
        return $query->where('action_type', DiciplinaryActionTypeEnum::BAN);
    }

    public function scopeSecurityForTeams($query, $teamsIds)
    {
        return $query->whereHas('person', function ($q) use ($teamsIds) {
            $q->securityForTeams($teamsIds);
        });
    }

    // ACTIONS
    public static function blockPerson($personId, $reason = null)
    {
        $diciplinaryAction = new self();
        $diciplinaryAction->person_id = $personId;
        $diciplinaryAction->action_type = DiciplinaryActionTypeEnum::BLOCK;
        $diciplinaryAction->action_from = now();
        $diciplinaryAction->action_reason_type = DiciplinaryReasonTypeEnum::OTHER;
        $diciplinaryAction->action_reason_description = $reason;

        $diciplinaryAction->save();

        $diciplinaryAction->executeLogic();

        return $diciplinaryAction;
    }

    public function executeLogic()
    {
        $this->action_type->startedAction($this);
    }

    // ELEMENTS
    public function actionTypePill()
    {
        $diciplinaryActionType = $this->action_type;

        return _Pill($diciplinaryActionType->label())
            ->class($diciplinaryActionType->classes());
    }
}
