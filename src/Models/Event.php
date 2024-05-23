<?php

namespace Condoedge\Crm\Models;

class Event extends RegistrableModel
{
	use \Condoedge\Crm\Models\BelongsToActivityTrait;

	public const QRCODE_COLUMN_NAME = 'qrcode_ev';

	protected $casts = [
		'schedule_start' => 'datetime',
		'schedule_end' => 'datetime',
		'cover_av' => 'array',
	];

	/* ABSTRACT */
	public function getTargetTeam()
	{
		return $this->activity->team;
	}

	public function getNextEvent()
	{
		return $this;
	}

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
