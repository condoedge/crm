<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Models\Crm\PersonEvent;
use App\Models\Events\Event;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Common\ImgFormLayout;

class InscriptionRegistrableConfirmationForm extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

    protected $inscriptionId;
    protected $inscription;
    protected $eventId;
    protected $event;

    public $model = PersonModel::class;

    public function created()
    {
        $this->inscriptionId = $this->prop('inscription_id');
        $this->inscription = InscriptionModel::findOrFail($this->inscriptionId);

        $this->eventId = $this->prop('event_id');
        $this->event = Event::findOrFail($this->eventId);
    }

    public function rightColumnBody()
    {
        return _Rows(
            _Rows(
                _Html($this->model->full_name)->class('text-2xl'),
                _TitleModalSub($this->model->age_label),
            )->class('text-center mb-4'),
            
            $this->customRegistrableInfo(),

            !$this->model->registered_by ? null : _Link2Outlined('inscriptions.register-and-add-another-child')->selfPost('registerAndAddAnother')->redirect()->class('mb-4'),
            _Button('inscriptions.register-and-complete')->selfPost('registerAndFinish')->redirect(),
        )->class('p-8');
    }

    protected function customRegistrableInfo()
    {
        //Override
    }

    public function registerAndAddAnother()
    {
		$inscription = $this->inscription->type?->createForPerson($this->model, [
            'qr_code' => $this->inscription->qr_inscription,
        ]) ?: $this->model->createOrUpdateInscription($this->inscription->qr_inscription, $this->inscription->type);

		return redirect($inscription->getInscriptionPersonLinkRoute());
    }

    public function registerAndFinish()
    {
        $pe = $this->assignMemberToUnit();

        return redirect($pe->getInscriptionDoneRoute());
    }

    protected function assignMemberToUnit()
    {
        $pe = PersonEvent::createPersonEvent($this->model, $this->event, $this->inscription); 

        return $pe;
    }
}
