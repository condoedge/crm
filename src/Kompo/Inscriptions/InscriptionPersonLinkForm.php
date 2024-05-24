<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Crm\Models\GenderEnum;
use Condoedge\Crm\Models\Person;
use Kompo\Auth\Common\ImgFormLayout;

class InscriptionPersonLinkForm extends ImgFormLayout
{
    protected $imgUrl = 'images/base-email-image.png';

	public $model = Person::class;

	protected $mainPersonId;
	protected $mainPerson;
    protected $qrCode;

	public function created()
	{
        $this->qrCode = $this->prop('qr_code');

		$this->mainPersonId = $this->prop('person_id');
		$this->mainPerson = Person::findOrFail($this->mainPersonId);
	}

	public function beforeSave()
	{
		$this->model->registered_by = $this->mainPersonId;
	}

	public function response()
	{
		return redirect($this->model->getInscriptionTeamRoute($this->qrCode));
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
				_Link2Outlined('inscriptions.back')->href($this->mainPerson->getInscriptionPersonRoute($this->qrCode)),
				_SubmitButton2('inscriptions.continue')->redirect(),
			),
		];
	}
}
