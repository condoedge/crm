<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

class Inscription extends Model
{
	use \Condoedge\Crm\Models\HasQrCodeTrait;
	public const QRCODE_LENGTH = 8;
	public const QRCODE_COLUMN_NAME = 'qr_inscription'; //To link same inscription members together
	
	/* RELATIONS */

	/* SCOPES */

	/* CALCULATED FIELDS */
    public function getInscriptionPersonLinkRoute($personLinkId = null)
    {
        return \URL::signedRoute('inscription.person-link', [
            'inscription_id' => $this->id,
            'id' => $personLinkId,
        ]);
    }

	public function getInscriptionConfirmationRoute($personId, $eventId)
    {
        return \URL::signedRoute('inscription.confirmation', [
            'inscription_id' => $this->id,
            'event_id' => $eventId,
            'id' => $personId,
        ]);
    }

	/* ACTIONS */
	public function deleteInscriptionEventsIfAny($personId)
	{
		PersonEvent::where('inscription_id', $this->id)->where('person_id', $personId)->delete();
	}

	/* ELEMENTS */
}
