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
        $this->inscription->confirmInscriptionFilled($this->event->team_id, $this->event);

        $inscription = InscriptionModel::getOrCreateForMainPerson($this->personId, $this->event->team_id, InscriptionModel::getDefaultRegisteredByType());

		return redirect($inscription->getInscriptionPersonLinkRoute());
    }

    public function registerAndFinish()
    {
        $this->inscription->confirmInscriptionFilled($this->event->team_id, $this->event);

        return redirect($this->inscription->getInscriptionDoneRoute());
    }
}
