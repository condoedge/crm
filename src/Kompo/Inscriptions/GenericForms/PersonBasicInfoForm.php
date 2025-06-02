<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\GenderEnum;
use Condoedge\Crm\Rules\MinAgeBySchoolYear;
use Condoedge\Utils\Kompo\Common\ImgFormLayout;

abstract class PersonBasicInfoForm extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\PersonFormUtilsTrait;
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    public $model = PersonModel::class;

    protected $inscriptionId;
    protected $inscription;
    protected $teamId;

    public function created()
    {
        $this->teamId = $this->prop('team_id');

        $this->setInscriptionInfo();

        $this->model($this->mainPerson);
    }

    public function rightColumnBody()
    {
        return [
            _TitleMain($this->getTitle())->class('self-center my-8'),
            _TitleModalSub($this->getSubtitle())->class('mb-8 text-center'),

            $this->extraTopInputs(),

            _Input('inscriptions.first-name')->name('first_name'),
            _Input('inscriptions.last-name')->name('last_name'),
            _Select('inscriptions.gender')->name('gender')->options(
                GenderEnum::optionsWithLabels()
            ),
            _Date('inscriptions.dob')->name('date_of_birth')->class('mb-12'),
            _TwoColumnsButtons(
                !$this->getBackLinkRoute() ? null : _Link2Outlined('inscriptions.back')
                    ->href($this->getBackLinkRoute()),
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
            'first_name' => 'required',
            'last_name' => 'required',
            'date_of_birth' => ['required', 'date', new MinAgeBySchoolYear(6, 10, 1)],
        ];
    }
}
