<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Kompo\Auth\Common\ImgFormLayout;

class InscriptionLandingPage extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

    protected $qrCode;

    public function created()
    {
        $this->qrCode = $this->prop('qr_code');
    }

    public function handle()
    {
    	if (auth()->user()) {

            $person = auth()->user()->getRelatedMainPerson();
    		return redirect($person->getInscriptionPersonRoute($this->qrCode));

    	} else {

    		return redirect()->route('inscription.email.step1', [
                'qr_code' => $this->qrCode,
            ]);

    	}
    }

	public function rightColumnBody()
	{
		return _Rows(
			_LogoOnly()->class('self-center')->style('margin-bottom:100px;'),
            _RegistrableFromQrCodeTitle($this->qrCode),
            _SubmitButtonBig2('inscriptions.i-want-to-register')->redirect(),
		);
	}
}
