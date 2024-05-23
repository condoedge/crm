<?php

namespace Condoedge\Crm\Models;

use App\Models\Crm\PersonEvent;
use Illuminate\Support\Carbon;

class Activity extends RegistrableModel
{
	/* KOMPO TRAITS */
	use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;
	use \Kompo\Auth\Models\Files\MorphManyFilesTrait;

	public const QRCODE_COLUMN_NAME = 'qrcode_av';

	protected $casts = [
		'cover_av' => 'array',
	];

	/* ABSTRACT */
	public function getTargetTeam()
	{
		return $this->team;
	}

	public function getNextEvent()
	{
		return $this->nextEvent()->first();
	}

	/* RELATIONS */
	public function events()
	{
		return $this->hasMany(Event::class);
	}

	public function event()
	{
		return $this->hasOne(Event::class);
	}

	public function nextEvent()
	{
		return $this->event()->where('schedule_start', '>', Carbon::now());
	}

	public function activityAudiences()
	{
		return $this->hasMany(ActivityAudience::class);
	}

	public function personEvents()
	{
		return $this->hasMany(PersonEvent::class);
	}

    /* SCOPES */
    public function scopeForRegistrationSystem($query)
    {
    	$query->whereNotNull('registration_system');
    }

    public function scopeWithoutRegistrationSystem($query)
    {
    	$query->whereNull('registration_system');
    }

	/* CALCULATED FIELDS */
	public function getEventCoverUrl()
	{
		$coverPath = $this->cover_ev['path'] ?? null;

		if (!$coverPath) {
			return asset('images/base-email-image.png');
		}

		return \Storage::disk('public')->url($coverPath);
	}

	/* ACTIONS */

	/* ELEMENTS */
}
