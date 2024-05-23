<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Crm\Models\Person;
use Condoedge\Crm\Models\SpokenLanguageEnum;
use Kompo\Auth\Common\ImgFormLayout;
use Kompo\Auth\Models\Teams\EmailRequest;

class InscriptionPersonForm extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

	public $model = Person::class;

    protected $qrCode;

	public function created()
	{
        $this->qrCode = $this->prop('qr_code');

		$emailRequest = EmailRequest::getOrCreateEmailRequest($this->model->email_identity);
		$emailRequest->markEmailAsVerified();
	}

	public function afterSave()
	{
		$this->model->createPhoneFromNumberIfNotExists(request('inscribed_phone'));
	}

	public function completed()
	{
		if ($user = auth()->user()) {

			$user->setAddressableAndMakeBilling($this->model->address);
			$user->setPhonableAndMakePrimary($this->model->phone);
		}
	}

	public function response()
	{
		return redirect($this->model->getInscriptionPersonLinkRoute($this->qrCode));
	}

	public function rightColumnBody()
	{
		return [
//			_LogoWithTitle()->class('self-center mb-8 mt-2'),
			_TitleMain('inscriptions.registration-parent-of-scout')->class('self-center my-8'),
            _Rows(
                _TitleModalSub('inscriptions.your-information')->class('self-center mb-8'),
                _Input('inscriptions.first-name')->name('first_name')->default(auth()->user()?->getFirstName()),
                _Input('inscriptions.last-name')->name('last_name')->default(auth()->user()?->getLastName()),
                _SiscPlace()->default(auth()->user()?->primaryBillingAddress),
                _Input('inscriptions.my-phone')->name('inscribed_phone')->default(auth()->user()?->getPrimaryPhoneNumber()),
                SpokenLanguageEnum::getMultiSelect()->default(['en', 'fr'])->class('mb-12'),
            ),
			_SubmitButtonBig2('inscriptions.continue')->redirect()->class('mb-12'),
		];
	}

	public function rules()
	{
		return [
			'first_name' => 'required',
			'last_name' => 'required',
			'address.address1' => 'required',
			'inscribed_phone' => ['required', new \App\Rules\PhoneNumberRule],
		];
	}
}
