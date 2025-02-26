<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Models\User;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Common\ImgFormLayout;
use Kompo\Auth\Models\Teams\EmailRequest;

class InscriptionEmailStep1Form extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

	protected $qrCode;
	protected $type;
	protected $inscription;

    public function created()
    {
        $this->qrCode = $this->prop('inscription_code');

		$this->inscription = $this->qrCode ? InscriptionModel::forQrCode($this->qrCode)->first() : null;

		$typeId = $inscription?->type ?? $this->prop('type') ??  collect(getInscriptionTypesKeys())->first();
		$this->type = getInscriptionTypes()[$typeId] ?? null;
    }

	public function handle()
	{
		$email = request('email');

		$person = PersonModel::getOrCreatePersonFromEmail($email);
		$this->inscription?->updateRegisteringPersonId($person->id);

		$redirectTo = $this->getRedirectUrl($person);

		if ($user = User::where('email', $email)->first()) {
			return redirect(route('login.password', [
				'email' => $email,
				'redirect_to' => $redirectTo,
			]));
		} elseif (!$this->type->hasEmailVerification()){
			return redirect()->to($redirectTo);
		}  else {

			$emailRequest = EmailRequest::getOrCreateEmailRequest($email);

        	$emailRequest->setRedirectUrl($redirectTo);

            $emailRequest->sendEmailVerificationNotification();

            return redirect()->to(\URL::signedRoute('check.verify.email', ['id' => $emailRequest]));
		}
	}

	protected function getRedirectUrl($person)
	{
		return $this->inscription?->getRegistrationUrl() ?: InscriptionModel::createOrGetRegistrationUrl($person->id, null, $this->type);
	}

	public function rightColumnBody()
	{
		return [
            _TitleMain('inscriptions.your-information')->class('absolute self-center')->style('top:4rem;'),

			!$this->type->askForLegalAgeTerms() ? null 
				: _Checkbox('inscriptions.legal-age-terms')->name('legal_age_terms', false)->class('mb-6'),

			_Input('inscriptions.my-email')->name('email'),
            _SubmitButtonBig2('inscriptions.verify-my-email')->redirect(),
		];
	}

	public function rules()
	{
		return array_merge(
			[
				'email' => 'required|email',
			],
			$this->type->askForLegalAgeTerms() ? ['legal_age_terms' => 'required'] : []
		);
	}
}
