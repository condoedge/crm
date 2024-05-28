<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

class PersonEvent extends Model
{
	use \Condoedge\Crm\Models\BelongsToPersonTrait;
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
		return $this->event;
	}

	public function getRelatedTargetTeam()
	{
		return $this->getRegistrable()->getTargetTeam();
	}

	public function getFirstRegisteredPerson()
	{
		return $this->getRelatedRegistrations()->first();
	}

	public function getNextRegisteredPerson()
	{
		return $this->getRelatedRegistrations()->where('id', '>', $this->id)->first();
	}

	public function getRelatedRegistrations()
	{
		return PersonEvent::where('inscription_id', $this->inscription_id)->get();
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
	public static function createPersonEvent($person, $event, $inscription)
	{
		$pr = new PersonEvent();
		$pr->person_id = $person->id;
		$pr->event_id = $event->id;
		$pr->inscription_id = $inscription->id;
		$pr->save();

		return $pr;
	}

	public function approveAndSend()
	{
		$this->register_status = RegisterStatusEnum::RS_ACCEPTED;
		$this->save();

		\Mail::to($this->getRelatedEmail())
            ->send(new \App\Mail\InscriptionConfirmationMail($this->id));
	}

	/* ELEMENTS */
}
