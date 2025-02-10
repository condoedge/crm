<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Crm\Facades\InscriptionModel;
use Kompo\Auth\Common\ImgFormLayout;

class InscriptionLandingPage extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';
    protected $rightColumnBodyWrapperClass = '';

    protected $qrCode;
    protected $inscription;

    public function created()
    {
        $this->qrCode = $this->prop('inscription_code');

        $this->inscription = $this->qrCode ? InscriptionModel::forQrCode($this->qrCode)->first() : null;
    }

    public function manageInscriptionLink($type)
    {
        $person = auth()->user()?->getRelatedMainPerson();

        if($person) $this->inscription?->updatePersonId($person->id);
        $this->inscription?->updateType($type);

        if ($this->inscription) {
            return redirect()->to($this->inscription?->getRegistrationUrl());
        } else if (auth()->user()) {
            return redirect()->to(InscriptionModel::createOrGetRegistrationUrl($person->id, null, $type));
    	}  else {
    		return redirect()->route('inscription.email.step1', [
                'inscription_code' => $this->qrCode,
                'type' => $type,
            ]);
    	}
    }

	public function rightColumnBody()
	{
		return _Rows(
			_LogoOnly()->class('self-center')->style('margin-bottom:60px;'),
            _RegistrableFromQrCodeTitle($this->qrCode),
            _Html('inscriptions.i-want-to-register-and-i-am')->class('mb-6 text-lg text-center'),

            _Rows(
                collect(getInscriptionTypes())->filter(fn($it) => !$this->inscription?->type || $this->inscription?->type?->value == $it->value)
                    ->map(function($type) {
                    return $this->optionButton($type);
                }),
            )->class('gap-6'),
		);
	}

    /**
     * @param \Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum $type 
     * @return mixed
     */
    protected function optionButton($type)
    {
        return _Rows(
            _Html($type->registerTitle())->class('text-center !text-greendark font-semibold text-2xl mb-4'),
            _Html($type->registerDescription())->class('!text-greendark'),
        )->button2()->class('rounded-lg !pt-3 p-6')->selfGet('manageInscriptionLink', ['type' => $type->value])->redirect();
    }
}