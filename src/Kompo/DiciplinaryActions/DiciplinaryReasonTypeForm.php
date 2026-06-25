<?php

namespace Condoedge\Crm\Kompo\DiciplinaryActions;

use Condoedge\Crm\Models\DiciplinaryReasonType;
use Condoedge\Utils\Kompo\Common\Modal;

class DiciplinaryReasonTypeForm extends Modal
{
    public $_Title = 'crm.manage-disciplinary-reason-type';
    public $model = DiciplinaryReasonType::class;
    protected $teamId;

    protected $noHeaderButtons = true;

    public function created()
    {
        $this->teamId = $this->prop('team_id');
    }

    public function beforeSave()
    {
        // For now it's not team specific. But if in the future we need it, we could just uncomment the next line and set the forTeams scope.
        // $this->model->team_id = $this->teamId;
    }

    public function body()
    {
        return _Rows(
            _Translatable('crm.name')->name('name'),

            _TranslatableEditor('crm.description')->name('description'),

            _FlexEnd(
                _SubmitButton('generic.save'),
            ),
        );
    }

}