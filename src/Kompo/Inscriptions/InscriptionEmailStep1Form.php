<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Models\User;
use App\Models\Crm\Person;
use Kompo\Auth\Common\ImgFormLayout;
use Kompo\Auth\Models\Teams\EmailRequest;

class InscriptionEmailStep1Form extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

    protected $qrCode;

    public function created()
    {
        $this->qrCode = $this->prop('qr_code');
    }

	public function handle()
	{
		$email = request('email');

		$person = Person::getOrCreatePersonFromEmail($email);
		$redirectTo = $person->getInscriptionPersonRoute($this->qrCode);

		if ($user = User::where('email', $email)->first()) {
			return redirect(route('login.password', [
				'email' => $email,
				'redirect_to' => $redirectTo,
			]));
		} else {

			$emailRequest = EmailRequest::getOrCreateEmailRequest($email);

        	$emailRequest->setRedirectUrl($redirectTo);

            $emailRequest->sendEmailVerificationNotification();

            return redirect()->to(\Url::signedRoute('check.verify.email', ['id' => $emailRequest]));
		}


	}

	public function rightColumnBody()
	{
		return [
            _TitleMain('inscriptions.your-information')->class('absolute self-center')->style('top:4rem;'),
			_Input('inscriptions.my-email')->name('email'),
            _Flex(
                _SubmitButtonBig2('inscriptions.verify-my-email')->redirect()->class('w-full md:mx-16 mx-6'),
            )->class('absolute')->style('bottom:6rem; left: 0; right: 0;'),
		];
	}
}
