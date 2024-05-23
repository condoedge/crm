<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

class PersonRegistrable extends Model
{
	use \Condoedge\Crm\Models\BelongsToPersonTrait;

	use \Condoedge\Crm\Models\BelongsToActivityTrait;
	use \Condoedge\Crm\Models\BelongsToEventTrait;

	protected $casts = [
		'register_status' => RegisterStatusEnum::class,
	];

	/* RELATIONS */

	/* SCOPES */
	public function scopeCountInTotal($query)
	{
		$query->whereIn('register_status', [
			RegisterStatusEnum::RS_ACCEPTED,
			RegisterStatusEnum::RS_PAID,
		]);
	}

	public function scopeAwaitingApproval($query)
	{
		$query->whereIn('register_status', [
			RegisterStatusEnum::RS_REQUESTED,
		]);
	}

	/* CALCULATED FIELDS */
	public function getIeStatusLabelAttribute()
	{
		return $this->register_status->label();
	}

	public function getRelatedEmail()
	{
		return $this->person->email_identity;
	}

	public function getRegistrable()
	{
		if ($this->activity_id) {
			return $this->activity;
		}

		if ($this->event_id) {
			return $this->event;
		}
	}

	public function getRelatedTargetTeam()
	{
		return $this->getRegistrable()->getTargetTeam();
	}

	/* ROUTES */
	public function getAcceptInscriptionUrl()
	{
		return \URL::signedRoute('person-registrable.accept', [
            'id' => $this->id,
        ]);
	}

	public function getPerformRegistrationUrl()
	{
		return \URL::signedRoute('person-registrable.register', [
            'pr_id' => $this->id,
        ]);
	}

	public function getInscriptionDoneRoute()
	{
        return \URL::signedRoute('inscription.done1', [
            'id' => $this->id,
        ]);
	}

	/* ACTIONS */
	public static function createPersonRegistrable($personId, $registrable)
	{
		$pr = new PersonRegistrable();
		$pr->person_id = $personId;

		if ($registrable instanceof Activity) {
			$pr->activity_id = $registrable->id;
		}
		
		if ($registrable instanceof Event) {
			$pr->event_id = $registrable->id;
		}

		$pr->save();

		return $pr;
	}

	public function acceptAndSend()
	{
		$this->register_status = RegisterStatusEnum::RS_ACCEPTED;
		$this->save();

		\Mail::to($this->getRelatedEmail())
            ->send(new \App\Mail\InscriptionConfirmationMail($this->id));
	}

	/* ELEMENTS */
}
