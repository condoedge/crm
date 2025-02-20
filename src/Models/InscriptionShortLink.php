<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\InscriptionModel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Kompo\Auth\Models\Model;

/**
 * It's used to create a template for the inscription with certain parameters.
 * It have a code that is used to generate a short link for the inscription. 
 * When the user access to the short link, the inscription is created with the parameters from the short link.
 * It's a good way to have a pre-filled inscription form to generate links for each team 
 * (without creating one inscription per each user needing to send parameters throught the inscription process).
 */
class InscriptionShortLink extends Model
{
    protected $table = 'inscriptions_short_links';

    use \Condoedge\Crm\Models\BelongsToEventTrait;
    use \Condoedge\Crm\Models\BelongsToPersonTrait;
    use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;

    protected $casts = [
        // 'type' => InscriptionTypeEnum::class
    ];

    public static function getOrCreateShortLink($teamId, $personId, $eventId, $type)
    {
        $shortLink = static::where('team_id', $teamId)
            ->where('person_id', $personId)
            ->where('event_id', $eventId)
            ->where('type', $type)
            ->first();

        if (!$shortLink) {
            $shortLink = static::createShortLink($teamId, $personId, $eventId, $type);
        }

        return $shortLink;
    }

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
        $mainInscription = null;

        if ($this->person_id) {
            $inscription = InscriptionModel::getOrCreatePendingForMainPerson($this->person->getRegisteringPerson()->id, $this->team_id, $this->type, $this->role_id, false);
            $mainInscription = InscriptionModel::getOrCreatePendingForMainPerson($this->person->getRegisteringPerson()->id, $this->team_id, $this->type, $this->role_id, true);
        } 

        if (!$inscription) {
            $inscription = new (InscriptionModel::getClass());
            $inscription->type = $this->type;
            $inscription->team_id = $this->team_id;
            $inscription->person_id = $this->person_id;
            $inscription->inscribed_by = $this->type->basedInInscriptionForOtherPerson() ? $this->person?->getRegisteringPerson()?->id : null;
            $inscription->role_id = $this->role_id;
            $inscription->from_short_link_id = $this->id;
            $inscription->setQrCodeIfEmpty();
        }

        $inscription->related_inscription_id = $mainInscription?->id;
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

    public function getQr($size = 200)
    {
        $path = 'qr-codes/short-link-'.$this->id.'.png';
        $disk = config('kompo.default_storage_disk.image');

        if (!Storage::disk($disk)->exists($path)) {
            $qrCode = QrCode::format('png')->size($size)->generate($this->getInscriptionUrl());

            Storage::disk($disk)->put($path, $qrCode);
            Storage::disk($disk)->setVisibility($path, 'public');
        }

        return Storage::disk($disk)->url($path);
    }

    // SCOPES
    public function scopeForCode($query, $code) 
    {
        $query->where('code', $code);
    }
}