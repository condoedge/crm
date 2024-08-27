<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Kompo\Auth\Common\ImgFormLayout;

class InscriptionLandingPage extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';
    protected $rightColumnBodyWrapperClass = '';

    protected $qrCode;

    public function created()
    {
        $this->qrCode = $this->prop('qr_code');
    }

    public function manageInscriptionLink($type)
    {
        if (auth()->user()) {
            $person = auth()->user()->getRelatedMainPerson();

    		return redirect(getInscriptionTypesKeys()[$type]->registerRoute($person, $this->qrCode));
    	} else {
    		return redirect()->route('inscription.email.step1', [
                'qr_code' => $this->qrCode,
                'type' => $type,
            ]);
    	}
    }

	public function rightColumnBody()
	{
		return _Rows(
			_LogoOnly()->class('self-center')->style('margin-bottom:60px;'),
            _RegistrableFromQrCodeTitle($this->qrCode),
            _Html('translate.inscriptions.i-want-to-register-and-i-am')->class('mb-6 text-lg'),

            _Rows(
                collect(getInscriptionTypes())->map(function($type) {
                    return $this->optionButton($type);
                }),
            )->class('gap-6'),
		);
	}

    /**
     * Summary of optionButton
     * @param \Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum $type 
     * @return mixed
     */
    protected function optionButton($type)
    {
        return _Rows(
            _Html($type->registerTitle())->class('text-center !text-level1 font-semibold text-xl mb-2'),
            _Html($type->registerDescription())->class('!text-level1'),
        )->button2()->class('rounded-lg !pt-3 p-6')->selfGet('manageInscriptionLink', ['type' => $type->value])->redirect();
    }
}