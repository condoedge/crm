<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\SpokenLanguageEnum;
use Condoedge\Utils\Kompo\Common\ImgFormLayout;
use Condoedge\Utils\Rule\InternationalPhoneRule;
use Condoedge\Utils\Rule\NameRule;

abstract class PersonBasicInfoForm2 extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\PersonFormUtilsTrait;
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $rightColumnBodyWrapperClass = '';
    protected $withNames = true;

    public $model = PersonModel::class;

    protected $teamId;

    public function created()
    {
        $this->teamId = $this->prop('team_id');

        $this->setInscriptionInfo();

        $this->model($this->mainPerson);
    }

    public function afterSave()
    {
        $this->model->createPhoneFromNumberIfNotExists(request('inscribed_phone'));
    }

    public function rightColumnBody()
    {
        return [
//			_LogoWithTitle()->class('self-center mb-8 mt-2'),
            _TitleMain($this->getTitle())->class('self-center my-8'),
            _TitleModalSub($this->getSubtitle())->class('mb-8 text-center'),

            $this->extraTopInputs(),

            _Rows(
                !$this->withNames ? null : _Rows(
                    _Input('inscriptions.first-name')->name('first_name')->required()->default($this->model->first_name ?: auth()->user()?->getFirstName()),
                    _Input('inscriptions.last-name')->name('last_name')->required()->default($this->model->last_name ?: auth()->user()?->getLastName()),
                ),
                $this->placeInput()->required()->class('place-input-without-visual')->default($this->model->address ?: auth()->user()?->address),
                _InternationalPhoneInput('inscriptions.my-phone')->name('inscribed_phone')->required()->default($this->model->inscribed_phone ?: auth()->user()?->getPrimaryPhoneNumber()),
                SpokenLanguageEnum::getMultiSelect()->default(array_keys(config('kompo.locales')))->class('mb-12'),
            ),
            _SubmitButtonBig2('inscriptions.continue')->redirect()->class('mb-12'),
        ];
    }

    abstract protected function getTitle();
    abstract protected function getSubtitle();

    protected function extraTopInputs()
    {
        return _Rows();
    }

    protected function placeInput()
    {
        // TODO Fix this
        return _SiscPlace();
    }

    public function rules()
    {
        return array_merge(!$this->withNames ? [] : [
            'first_name' => ['required', new NameRule()],
            'last_name' => ['required', new NameRule()],
        ], [
            'address.address1' => 'required',
            'inscribed_phone' => ['required', new InternationalPhoneRule()],
        ]);
    }
}
