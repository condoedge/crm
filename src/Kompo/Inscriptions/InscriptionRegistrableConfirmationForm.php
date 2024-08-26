<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Models\Crm\PersonEvent;
use App\Models\Events\Event;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\Inscription;
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
        $this->inscription = Inscription::findOrFail($this->inscriptionId);

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

            _Link2Outlined('inscriptions.register-and-add-another-child')->selfPost('registerAndAddAnother')->redirect()->class('mb-4'),
            _Button('inscriptions.register-and-complete')->selfPost('registerAndFinish')->redirect(),
        )->class('p-8');
    }

    protected function customRegistrableInfo()
    {
        //Override
    }

    public function registerAndAddAnother()
    {
        $this->assignMemberToUnit();

        return redirect($this->inscription->getInscriptionPersonLinkRoute());
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
