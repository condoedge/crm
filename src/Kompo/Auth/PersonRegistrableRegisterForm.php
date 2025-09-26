<?php

namespace Condoedge\Crm\Kompo\Auth;

use App\Models\User;
use Condoedge\Utils\Kompo\Common\ImgFormLayout;

class PersonRegistrableRegisterForm extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $imgUrl = 'images/register-image.png';

    public $model = User::class;

    protected $team;
    protected $registeringEmail;

    protected function isAStepNotValidAtThisPoint()
    {
        return false;
    }

    public function created()
    {
        $this->setInscriptionInfo();

        $this->registeringEmail = $this->mainPerson->email_identity;

        $user = $this->mainPerson->relatedUser;

        if (!$user && ($user = User::where('email', $this->registeringEmail)->first())) {
            $this->mainPerson->user_id = $user->id;
            $this->mainPerson->save();
        }

        if ($user) {
            $this->model($user);
        }

        $this->model->first_name = $this->mainPerson->first_name;
        $this->model->last_name = $this->mainPerson->last_name;
    }

    public function beforeSave()
    {
        $this->model->email = $this->registeringEmail; //ensures the email in the inscription is used
        $this->model->email_verified_at = now();

        $this->model->handleRegisterNames();
    }

    public function afterSave()
    {
        $this->inscription->confirmInscriptionAsUserIfRegistered();

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

            // _InputRegisterNames($this->mainPerson->first_name, $this->mainPerson->last_name),
            $this->model->id ? null : _InputRegisterPasswords(),
            _CheckboxTerms(),
            _FlexEnd(
                _SubmitButton('inscriptions.accept-invitation')
            )
        ];
    }

    public function rules()
    {
        if ($this->model->id) {
            return [
                'terms' => ['required', 'accepted'],
            ];
        }

        return [
            'password' => passwordRules(),
            'terms' => ['required', 'accepted'],
        ];
    }
}
