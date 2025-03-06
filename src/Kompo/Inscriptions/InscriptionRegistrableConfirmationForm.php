<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Models\Events\Event;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Common\ImgFormLayout;

class InscriptionRegistrableConfirmationForm extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $imgUrl = 'images/base-email-image.png';

    protected $eventId;
    protected $event;

    public $model = PersonModel::class;

    public function created()
    {
        $this->setInscriptionInfo();
        $this->model($this->person);

        $this->eventId = $this->inscription->event_id;
        $this->event = $this->inscription->event;
    }

    public function rightColumnBody()
    {
        return _Rows(            
            _Rows(
                $this->mainInscription->getAllRelatedInscriptions()
                    ->map(fn($inscription) => _Rows(
                        _Rows(
                            _Html($inscription->person?->full_name)->class('text-2xl'),
                            _TitleModalSub($inscription->person?->age_label),
                        )->class('text-center mb-4'),
                        $this->customRegistrableInfo($inscription),
                    )->href($inscription->getInscriptionPersonRoute()))->toArray()
            ),

            !$this->inscription->type->basedInInscriptionForOtherPerson() ? null : _Link2Outlined('inscriptions.register-and-add-another-child')->selfPost('registerAndAddAnother')->redirect()->class('mb-4'),
            _Button('inscriptions.register-and-complete')->selfPost('registerAndFinish')->redirect(),
        )->class('p-8');
    }

    protected function customRegistrableInfo($inscription)
    {
        //Override
    }

    public function registerAndAddAnother()
    {
        $inscription = InscriptionModel::getOrCreatePendingForMainPerson($this->mainPerson->id, $this->event->team_id, $this->inscription->type);
        $inscription->related_inscription_id = $this->mainInscription->id;
        $inscription->setSelectedTeam($this->event->team_id, null);

		return redirect($inscription->getInscriptionPersonLinkRoute());
    }

    public function registerAndFinish()
    {
        $this->mainInscription->getAllRelatedInscriptions()
            ->filter(fn($inscription) => $inscription->isRegistrable())
            ->each(fn($inscription) => $inscription->confirmInscriptionFilled());

        return redirect($this->mainInscription->getConsentPageRoute());
    }
}
