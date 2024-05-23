<?php

namespace Condoedge\Crm\Kompo\Auth;

use App\Models\Roles\ParentRole;
use App\Models\User;
use Condoedge\Crm\Models\PersonRegistrable;
use Kompo\Auth\Common\ImgFormLayout;

class PersonRegistrableRegisterForm extends ImgFormLayout
{
    protected $imgUrl = 'images/register-image.png';

    public $model = User::class;

    protected $prId;
    protected $personRegistrable;
    protected $team;
    protected $person;

    public function created()
    {
        $this->prId = $this->prop('pr_id');
        $this->personRegistrable = PersonRegistrable::findOrFail($this->prId);
        $this->team = $this->personRegistrable->getRelatedTargetTeam();
        $this->person = $this->personRegistrable->person;
        $this->parentEmail = $this->personRegistrable->getRelatedEmail();
    }

    public function beforeSave()
    {
        $this->model->email = $this->parentEmail; //ensures the email in the inscription is used
        $this->model->email_verified_at = now();

        $this->model->handleRegisterNames();
    }

    public function afterSave()
    {
        $this->model->createTeamRole($this->team, ParentRole::ROLE_KEY);

        fireRegisteredEvent($this->model);

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
                ->value($this->parentEmail)->inputClass('bg-gray-50 rounded-xl'),

            _InputRegisterNames($this->person->first_name, $this->person->last_name),
            _InputRegisterPasswords(),
            _CheckboxTerms(),
            _FlexEnd(
                _SubmitButton('inscriptions.accept-invitation')
            )
        ];
    }

    public function rules()
    {
        return registerRules();
    }
}
