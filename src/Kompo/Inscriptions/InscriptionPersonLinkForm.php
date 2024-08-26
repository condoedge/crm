<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\GenderEnum;
use Condoedge\Crm\Models\Inscription;
use Kompo\Auth\Common\ImgFormLayout;

class InscriptionPersonLinkForm extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

	public $model = PersonModel::class;

    protected $inscriptionId;
    protected $inscription;
	protected $mainPersonId;
	protected $mainPerson;

	public function created()
	{
        $this->inscriptionId = $this->prop('inscription_id');
        $this->inscription = Inscription::findOrFail($this->inscriptionId);

		$this->mainPersonId = $this->inscription->inscribed_by;
		$this->mainPerson = Person::findOrFail($this->mainPersonId);
	}

	public function beforeSave()
	{
		$this->model->registered_by = $this->mainPersonId;
		$this->model->inscription_id = $this->inscriptionId;
	}

	public function response()
	{
		$this->inscription->deleteInscriptionEventsIfAny($this->model->id);

		return redirect($this->model->getInscriptionTeamRoute($this->inscription));
	}

	public function rightColumnBody()
	{
		return [
            _TitleMain('inscriptions.registration-parent-of-scout')->class('self-center my-8'),
			_TitleModalSub('inscriptions.your-child')->class('mb-8 text-center'),
			_Input('inscriptions.first-name')->name('first_name'),
			_Input('inscriptions.last-name')->name('last_name'),
			_Select('inscriptions.gender')->name('gender')->options(
				GenderEnum::optionsWithLabels()
			),
			_Date('inscriptions.dob')->name('date_of_birth')->class('mb-12'),
			_TwoColumnsButtons(
				_Link2Outlined('inscriptions.back')
					->href($this->getBackLinkRoute()),
				_SubmitButton2('inscriptions.continue')->redirect(),
			),
		];
	}

	public function getBackLinkRoute()
	{
		if ($prevPerson = $this->model->getPreviousInscriptionPerson($this->inscriptionId)) {
			return $this->inscription->getInscriptionPersonLinkRoute($prevPerson->id);
		}

		return $this->mainPerson->getInscriptionPersonRoute($this->inscription->qr_inscription);
	}
}
