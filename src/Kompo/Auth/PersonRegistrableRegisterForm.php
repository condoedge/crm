<?php

namespace Condoedge\Crm\Kompo\Auth;

use App\Models\User;
use Condoedge\Crm\Facades\InscriptionModel;
use Kompo\Auth\Common\ImgFormLayout;

class PersonRegistrableRegisterForm extends ImgFormLayout
{
    protected $imgUrl = 'images/register-image.png';

    public $model = User::class;

    protected $inscriptionId;
    protected $inscription;
    protected $team;
    protected $person;
    protected $registeringEmail;

    public function created()
    {
        $this->inscriptionId = $this->prop('inscription_id');
        $this->inscription = InscriptionModel::findOrFail($this->inscriptionId);
        $this->person = $this->inscription->person->getRegisteringPerson();
        $this->registeringEmail = $this->person->email_identity;

        $this->model->first_name = $this->person->first_name;
        $this->model->last_name = $this->person->last_name;
    }

    public function beforeSave()
    {
        $this->model->email = $this->registeringEmail; //ensures the email in the inscription is used
        $this->model->email_verified_at = now();

        $this->model->handleRegisterNames();
    }

    public function afterSave()
    {
        $this->inscription->confirmUserRegistration($this->model);

        auth()->guard()->login($this->model);
    }

    public function response()
    {
        return redirect()->route('dashboard');
    }

    public function rightColumnBody()
    {
        return [
            _Input('inscriptions.your-invitation-email')->name('show_email', false)->readOnly()
                ->value($this->registeringEmail)->inputClass('bg-gray-50 rounded-xl'),

            // _InputRegisterNames($this->person->first_name, $this->person->last_name),
            _InputRegisterPasswords(),
            _CheckboxTerms(),
            _FlexEnd(
                _SubmitButton('inscriptions.accept-invitation')
            )
        ];
    }

    public function rules()
    {
        return [
            'show_email' => ['required', 'email', 'unique:users,email'],
            'password' => passwordRules(),
            'terms' => ['required', 'accepted'],
        ];
    }
}
