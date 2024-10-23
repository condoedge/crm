<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Kompo\Inscriptions\InscriptionTypeEnum;
use App\Models\User;
use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Common\ImgFormLayout;
use Kompo\Auth\Models\Teams\EmailRequest;

class InscriptionEmailStep1Form extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

    protected $qrCode;
	protected $type;

    public function created()
    {
        $this->qrCode = $this->prop('qr_code');
		$this->type = $this->prop('type') ?? collect(getInscriptionTypesKeys())->first() ?? null;
    }

	public function handle()
	{
		$email = request('email');

		$person = PersonModel::getOrCreatePersonFromEmail($email);
		$redirectTo = $this->getRedirectUrl($person);

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

	protected function getRedirectUrl($person)
	{
		return getInscriptionTypes()[$this->type]->registerRoute($person, $this->qrCode);
	}

	public function rightColumnBody()
	{
		return [
            _TitleMain('inscriptions.your-information')->class('absolute self-center')->style('top:4rem;'),
			_Input('inscriptions.my-email')->name('email'),
            _SubmitButtonBig2('inscriptions.verify-my-email')->redirect(),
		];
	}

	public function rules()
	{
		return [
			'email' => 'required|email'
		];
	}
}
