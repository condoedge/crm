<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum;
use Kompo\Auth\Models\Model;

class InscriptionShortLink extends Model
{
    protected $table = 'inscriptions_short_links';

    use \Condoedge\Crm\Models\BelongsToEventTrait;
    use \Condoedge\Crm\Models\BelongsToPersonTrait;
    use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;

    protected $casts = [
        'type' => InscriptionTypeEnum::class
    ];

    public static function createShortLink($teamId, $personId, $eventId, $type)
    {
        $shortLink = new static;
        $shortLink->team_id = $teamId;
        $shortLink->person_id = $personId;
        $shortLink->event_id = $eventId;
        $shortLink->code = getRandStringForModel(new static, 'code', 16);
        $shortLink->type = $type;

        $shortLink->save();

        return $shortLink;
    }

    public function createInscription()
    {
        $inscription = null;

        if ($this->person_id) {
            $inscription = InscriptionModel::getOrCreateForMainPerson($this->person_id, $this->team_id, $this->type, $this->role_id, $this->reregistration);
        } 

        if (!$inscription) {
            $inscription = new (InscriptionModel::getClass());
            $inscription->type = $this->type;
            $inscription->team_id = $this->team_id;
            $inscription->person_id = $this->person_id;
            $inscription->event_id = $this->event_id;
            $inscription->role_id = $this->role_id;
            $inscription->from_short_link_id = $this->id;
            $inscription->is_reregistration = $this->reregistration;
            $inscription->setQrCodeIfEmpty();
        }

        $inscription->event_id = $this->event_id;
        $inscription->save();

        return $inscription;
    }

    public function getInscriptionUrl()
    {
        return route('inscription-generation-page', [
            'link_code' => $this->code,
        ]);
    }

    // SCOPES
    public function scopeForCode($query, $code) 
    {
        $query->where('code', $code);
    }
}