<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Crm\Models\Person;
use Condoedge\Crm\Models\PersonEvent;
use Condoedge\Crm\Models\PersonRegistrable;
use Kompo\Auth\Common\ImgFormLayout;

class InscriptionRegistrableConfirmationForm extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

    protected $qrCode;
    protected $registrable;

    public $model = Person::class;

    public function created()
    {
        $this->qrCode = $this->prop('qr_code');
        $this->registrable = registrableFromQrCode($this->qrCode);
    }

    public function rightColumnBody()
    {
        return _Rows(
            _Rows(
                _TitleModal($this->model->full_name),
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

        return redirect($this->model->registeredBy->getInscriptionMemberRoute());
    }

    public function registerAndFinish()
    {
        $iev = $this->assignMemberToUnit();

        return redirect($iev->getInscriptionDoneRoute());
    }

    protected function assignMemberToUnit()
    {
        $pr = PersonEvent::createPersonRegistrable($this->model->id, $this->registrable); 

        return $pr;
    }
}
