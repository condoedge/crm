<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

abstract class RegistrableModel extends Model
{
	use \Condoedge\Crm\Models\HasQrCodeTrait;
	public const QRCODE_LENGTH = 8;

	abstract public function getTargetTeam();
	abstract public function getNextEvent();

    public function save(array $options = [])
    {
        $this->setQrCodeIfEmpty();

        parent::save();
    }

	/* RELATIONS */

	/* CALCULATED FIELDS */
	public function getRegistrableConfirmationRoute($personId)
    {
        return \URL::signedRoute('inscription.registrable', [
            'qr_code' => $this->getQrCodeString(),
            'id' => $personId,
        ]);
    }

	/* ACTIONS */

	/* ELEMENTS */
	public function getNextEventScheduleEls()
	{
		return _CalendarWithIcon(
            _Rows(
                _Html($this->getNextEvent()?->getScheduleWeekLabel()),
                _Html($this->getNextEvent()?->getScheduleTimesLabel()),
            ),
        );
	}
}
