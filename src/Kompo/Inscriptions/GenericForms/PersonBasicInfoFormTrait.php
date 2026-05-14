<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Models\SpokenLanguageEnum;
use Condoedge\Utils\Rule\InternationalPhoneRule;
use Condoedge\Utils\Rule\NameRule;

trait PersonBasicInfoFormTrait
{
    use PersonFormUtilsTrait;
    use InscriptionFormUtilsTrait;

    // Consuming classes MUST declare: `public $model = \Condoedge\Crm\Facades\PersonModel::class;`
    // (it can't live in this trait because Kompo\Form already declares $model with a null default,
    // and PHP forbids a trait from redeclaring a parent property with a different initializer).

    protected $teamId;
    protected $withNames = true;

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

    /** Body of the right column — fields only. Title/subtitle handled separately. */
    public function bodyContent(): array
    {
        return [
            $this->extraTopInputs(),
            _Rows(
                !$this->withNames ? null : _Rows(
                    _Input('inscriptions.first-name')->name('first_name')->required()
                        ->default($this->model->first_name ?: auth()->user()?->getFirstName()),
                    _Input('inscriptions.last-name')->name('last_name')->required()
                        ->default($this->model->last_name ?: auth()->user()?->getLastName()),
                ),
                $this->placeInput()->required()->class('place-input-without-visual')
                    ->default($this->model->address ?: auth()->user()?->address),
                _InternationalPhoneInput('inscriptions.my-phone')->name('inscribed_phone')->required()
                    ->default($this->model->inscribed_phone ?: auth()->user()?->getPrimaryPhoneNumber()),
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
