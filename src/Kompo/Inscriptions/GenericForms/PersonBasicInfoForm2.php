<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\SpokenLanguageEnum;
use Condoedge\Crm\Rules\PhoneNumberRule;
use Kompo\Auth\Common\ImgFormLayout;

abstract class PersonBasicInfoForm2 extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\PersonFormUtilsTrait;

    protected $rightColumnBodyWrapperClass = '';
    protected $withNames = true;

    public $model = PersonModel::class;

    protected $teamId;

    public function created()
	{
        $this->inscriptionId = $this->prop('inscription_id');
        $this->inscription = InscriptionModel::find($this->inscriptionId);

        $this->teamId = $this->prop('team_id');
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
                    _Input('inscriptions.first-name')->name('first_name')->default($this->model->first_name ?: auth()->user()?->getFirstName()),
                    _Input('inscriptions.last-name')->name('last_name')->default($this->model->last_name ?: auth()->user()?->getLastName()),
                ),
               
                $this->placeInput()->default($this->model->primaryBillingAddress ?: auth()->user()?->primaryBillingAddress),
                _Input('inscriptions.my-phone')->name('inscribed_phone')->default($this->model->inscribed_phone ?: auth()->user()?->getPrimaryPhoneNumber()),
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
            'first_name' => 'required',
			'last_name' => 'required',
        ], [
			'address.address1' => 'required',
			'inscribed_phone' => ['required', new PhoneNumberRule],
		]);
	}
}