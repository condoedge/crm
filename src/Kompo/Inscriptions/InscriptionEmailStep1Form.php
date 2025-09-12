<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Models\User;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Kompo\Common\ImgFormLayout;
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
            return response()->modal($this->alreadyRegisteredComponent($email, $redirectTo));
        } elseif (!$this->type->hasEmailVerification()) {
            return redirect()->to($redirectTo);
        } else {

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

            _Input('inscriptions.my-email')->name('email')->required(),
            _SubmitButtonBig2('inscriptions.verify-my-email'),
        ];
    }

    protected function alreadyRegisteredComponent($email, $redirectTo)
    {
        return _Rows(
            _Html('inscriptions.you-are-already-registered')->class('mb-6 text-lg text-white font-semibold'),
            _Html('inscriptions.you-can-login-to-your-account-and-continue-the-registration-process')->class('mb-6 text-sm text-white'),
            _Link2Button('inscriptions.auth-login')->href(route('login.password', [
                'email' => $email,
                'redirect_to' => $redirectTo,
            ])),
        )->class('bg-level1 p-6 rounded-xl');
    }

    public function rules()
    {
        return array_merge(
            [
                'email' => 'required|email|max:255',
            ],
            $this->type->askForLegalAgeTerms() ? ['legal_age_terms' => 'required'] : []
        );
    }
}
