<?php

namespace Condoedge\Crm\Models;

trait BelongsToActivityTrait
{
    /* RELATIONS */
    public function activity()
    {
        return $this->belongsTo(config('condoedge-crm.activity-model-namespace'));
    }

    /* CALCULATED FIELDS */
    public function getActivityName()
    {
        return $this->activity->name_av;
    }

    /* ACTIONS */

    /* SCOPES */
    public function scopeForActivity($query, $itemOrItems)
    {
        scopeWhereBelongsTo($query, 'activity_id', $itemOrItems);
    }

}
