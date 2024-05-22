<?php

namespace Condoedge\Crm\Models;

use App\Models\Crm\PersonEvent;
use Illuminate\Support\Carbon;
use Kompo\Auth\Models\Model;

class Activity extends Model
{
	/* KOMPO TRAITS */
	use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;
	use \Kompo\Auth\Models\Files\MorphManyFilesTrait;

	protected $casts = [
		'cover_av' => 'array',
	];

	/* RELATIONS */
	public function eventSchedules()
	{
		return $this->hasMany(EventSchedule::class);
	}

	public function eventSchedule()
	{
		return $this->hasOne(EventSchedule::class);
	}

	public function eventAudiences()
	{
		return $this->hasMany(EventAudience::class);
	}

	public function nextSchedule()
	{
		return $this->eventSchedule()->where('schedule_start', '>', Carbon::now());
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
