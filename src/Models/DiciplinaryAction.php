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

    // ELEMENTS
    public function actionTypePill()
    {
        $diciplinaryActionType = $this->action_type;

        return _Pill($diciplinaryActionType->label())
            ->class($diciplinaryActionType->classes());
    }
}
