<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

class Event extends Model
{
	use BelongsToActivityTrait;

	protected $casts = [
		'schedule_start' => 'datetime',
		'schedule_end' => 'datetime',
		'cover_av' => 'array',
	];

	/* RELATIONS */

	/* CALCULATED FIELDS */
	public function getScheduleWeekLabel()
	{
		return $this->schedule_start?->translatedFormat('l');
	}

	public function getScheduleTimesLabel()
	{
		return $this->schedule_start?->format('H:i').' - '.$this->schedule_end?->format('H:i');
	}

	/* ACTIONS */

	/* ELEMENTS */
}
