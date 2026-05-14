<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Models\GenderEnum;
use Condoedge\Crm\Rules\MinAgeBySchoolYear;
use Condoedge\Utils\Rule\NameRule;

/**
 * Body + lifecycle for the V1 "person identity" inscription form
 * (first/last name, gender, date of birth).
 *
 * Consuming classes MUST declare: `public $model = \Condoedge\Crm\Facades\PersonModel::class;`
 * (it can't live in this trait — Kompo\Form already declares $model with a null default,
 * and PHP forbids a trait redeclaring a parent property with a different initializer).
 */
trait PersonBasicInfoFormV1Trait
{
    use PersonFormUtilsTrait;
    use InscriptionFormUtilsTrait;

    protected $inscriptionId;
    protected $inscription;
    protected $teamId;

    public function created()
    {
        $this->teamId = $this->prop('team_id');
        $this->setInscriptionInfo();
        $this->model($this->mainPerson);
    }

    /** Body of the form — fields + nav buttons only. Title/subtitle handled by the layout. */
    public function bodyContent(): array
    {
        return [
            $this->extraTopInputs(),
            _Input('inscriptions.first-name')->name('first_name')->required(),
            _Input('inscriptions.last-name')->name('last_name')->required(),
            _Select('inscriptions.gender')->name('gender')->options(GenderEnum::optionsWithLabels()),
            _Date('inscriptions.dob')->name('date_of_birth')->class('mb-12')->required(),
            _TwoColumnsButtons(
                !$this->getBackLinkRoute() ? null : _Link2Outlined('inscriptions.back')->href($this->getBackLinkRoute()),
                _SubmitButton2('inscriptions.continue')->redirect(),
            ),
        ];
    }

    protected function extraTopInputs()
    {
        return _Rows();
    }

    abstract protected function getTitle();
    abstract protected function getSubtitle();
    abstract public function getBackLinkRoute();

    public function rules()
    {
        return [
            'first_name' => ['required', new NameRule()],
            'last_name' => ['required', new NameRule()],
            'gender' => ['required', 'in:' . collect(GenderEnum::cases())->pluck('value')->join(',')],
            'date_of_birth' => ['required', 'date', new MinAgeBySchoolYear(6, 10, 1)],
        ];
    }
}
