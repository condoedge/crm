<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

abstract class Event extends Model
{
	use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;
	use \Condoedge\Crm\Models\BelongsToEventTrait;
	use \Kompo\Auth\Models\Files\MorphManyFilesTrait;

	use \Condoedge\Crm\Models\HasQrCodeTrait;
	public const QRCODE_LENGTH = 8;
	public const QRCODE_COLUMN_NAME = 'qrcode_ev';

	protected $casts = [
		'schedule_start' => 'datetime',
		'schedule_end' => 'datetime',
		'cover_av' => 'array',
	];

	public function save(array $options = [])
    {
        $this->setQrCodeIfEmpty();

        parent::save();
    }

	/* ABSTRACT */
	public function getTargetTeam()
	{
		return $this->team;
	}

	public function getNextEvent()
	{
		return $this;
	}

	/* RELATIONS */
	public function eventAudiences()
	{
		return $this->hasMany(EventAudience::class);
	}

	public function personEvents()
	{
		return $this->hasMany(PersonEvent::class);
	}

    public function countedPersonEvents()
    {
    	return $this->personEvents()->countInTotal();
    }

    public function awaitingPersonEvents()
    {
    	return $this->personEvents()->awaitingApproval();
    }

    /* SCOPES */
    public function scopeForRegistrationSystem($query)
    {
    	$query->whereNotNull('registration_system');
    }

    public function scopeWithoutRegistrationSystem($query)
    {
    	$query->where(fn($q) => $q->whereNull('registration_system')->orWhere('registration_system', 0));
    }

	/* CALCULATED FIELDS */
	public function getScheduleWeekLabel()
	{
		return $this->schedule_start?->translatedFormat('l');
	}

	public function getScheduleTimesLabel()
	{
		return $this->schedule_start?->format('H:i').' - '.$this->schedule_end?->format('H:i');
	}

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
	public function getNextEventScheduleEls()
	{
		return _CalendarWithIcon(
            _Rows(
                _Html($this->getScheduleWeekLabel()),
                _Html($this->getScheduleTimesLabel()),
            ),
        );
	}
}
