<?php

namespace Condoedge\Crm\Models;

use App\Models\Events\EventAudienceMemberEnum;
use App\Models\Events\EventAudienceTeamEnum;
use Condoedge\Utils\Models\ModelBase;

class EventAudience extends ModelBase //No need for softdeletes here
{
    use \Condoedge\Crm\Models\BelongsToEventTrait;

    /* RELATIONS */

    /* SCOPES */
    public function scopeForAudienceConcern($query, $concern)
    {
        $query->where('audience_concern', $concern);
    }

    public function scopeForAudienceValue($query, $audienceValue)
    {
        $query->where('event_audience', $audienceValue);
    }

    /* CALCULATED FIELDS */
    public function getEnumAttribute()
    {
        return match ($this->audience_concern) {
            EventAudienceTeamEnum::AUDIENCE_CONCERN => EventAudienceTeamEnum::from($this->event_audience),
            EventAudienceMemberEnum::AUDIENCE_CONCERN => EventAudienceMemberEnum::from($this->event_audience),
            default => null,
        };
    }

    public function getLabelAttribute()
    {
        return $this->enum?->label() ?? null;
    }

    /* ACTIONS */

    /* ELEMENTS */
    public function getPill()
    {
        return _Pill($this->label)->class($this->enum?->color())->class('text-white');
    }
}
