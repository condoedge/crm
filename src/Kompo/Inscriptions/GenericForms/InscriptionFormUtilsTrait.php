<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Models\Teams\EmailRequest;
use Kompo\Auth\Models\Teams\PermissionTypeEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait InscriptionFormUtilsTrait
{
    protected $inscriptionCode;
    protected $inscription;
    protected $inscriptionId;

    protected $mainInscription;

    protected $event;
    protected $eventId;

    protected $team;
    protected $teamId;

    protected $person;
    protected $personId;
    protected $mainPerson;

    protected function setInscriptionInfo()
    {
        $this->inscriptionCode = $this->prop('inscription_code');
        $this->inscriptionId = $this->prop('inscription_id');

        if ($this->inscriptionId) {
            $this->inscription = InscriptionModel::findOrFail($this->inscriptionId);
        } elseif ($this->inscriptionCode) {
            // firstOrFail: a missing/deleted code must 404, not leave every downstream
            // dereference to crash on null (the accepted() guard below passes on null).
            $this->inscription = InscriptionModel::forQrCode($this->inscriptionCode)->firstOrFail();
        }

        $this->inscriptionId = $this->inscription?->id;

        $this->person = $this->inscription?->person;
        $this->mainPerson = $this->inscription?->inscribedBy ?? $this->person?->getRegisteringPerson();

        $this->personId = $this->person?->id;

        $this->event = $this->inscription?->event;
        $this->eventId = $this->event?->id;

        $this->team = $this->inscription?->team;
        $this->teamId = $this->team?->id;

        $this->mainInscription = $this->inscription?->getMainInscription();

        if ($this->isAStepNotValidAtThisPoint()) {
            throw new HttpException(422, __('error.you-are-already-registered-and-accepted'));
        }

        $this->assertInscriptionBelongsToAuthUser();
    }

    protected function isAStepNotValidAtThisPoint()
    {
        return $this->inscription?->status?->accepted();
    }

    // If person is an authenticated user they can't continue unless they give proof of ownership
    protected function assertInscriptionBelongsToAuthUser(): void
    {
        if (!$this->inscription || !($user = auth()->user())) {
            return;
        }

        $parties = array_filter([(int) $this->inscription->inscribed_by, (int) $this->inscription->person_id]);

        if (!$parties || array_intersect($parties, $this->personIdsHeldBy($user))) {
            return;
        }

        throw new HttpException(422, __('error.this-registration-belongs-to-another-account'));
    }

    protected function personIdsHeldBy($user): array
    {
        return PersonModel::asSystemOperation()
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('email_identity', $user->email))
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function manageInscriptionLink($type)
    {
        $person = auth()->user()?->getRelatedMainPerson();

        // An owned shell is that family's registration; only its owner may continue it.
        $owner = $this->inscription?->getInscribingPerson();

        if ($owner && (int) $owner->id !== (int) ($person?->id ?? 0)) {
            if ($person) {
                return redirect()->to(InscriptionModel::createOrGetRegistrationUrl($person->id, $this->inscription->team_id, $type));
            }

            return redirect()->route('inscription.email.step1', [
                'inscription_code' => $this->inscriptionCode ?: $this->inscription->getExistentQrOrCreateNew(),
                'type' => $type,
            ]);
        }

        // Needed before updateRegisteringPersonId because that method uses the type inside
        $this->inscription?->updateType($type);

        if ($person) {
            $this->inscription?->updateRegisteringPersonId($person->id);
        }

        if ($this->inscription) {
            return redirect()->to($this->inscription?->getRegistrationUrl());
        } elseif (auth()->user()) {
            return redirect()->to(InscriptionModel::createOrGetRegistrationUrl($person->id, null, $type));
        } elseif ($email = $this->prop('email')) {
            $person = PersonModel::getOrCreatePersonFromEmail($email);
            $redirectTo = InscriptionModel::createOrGetRegistrationUrl($person->id, null, $type);

            $emailRequest = EmailRequest::getOrCreateEmailRequest($email);
            $emailRequest->setRedirectUrl($redirectTo);
            $emailRequest->sendEmailVerificationNotification();
            return redirect()->to(\URL::signedRoute('check.verify.email', ['id' => $emailRequest]));
        } else {
            return redirect()->route('inscription.email.step1', [
                'inscription_code' => $this->inscriptionCode,
                'type' => $type,
            ]);
        }
    }
}
