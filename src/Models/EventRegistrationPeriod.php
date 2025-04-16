<?php

namespace Condoedge\Crm\Models;

use Condoedge\Utils\Models\Model;

class EventRegistrationPeriod extends Model
{
	use \Condoedge\Crm\Models\BelongsToEventTrait;

	use \Condoedge\Crm\Models\HasQrCodeTrait;
	public const QRCODE_LENGTH = 8;
	public const QRCODE_COLUMN_NAME = 'qrcode_rg';

	public function save(array $options = [])
    {
        $this->setQrCodeIfEmpty();

        parent::save();
    }

	/* ABSTRACT */
	public function getRegistrableId()
	{
		return $this->event_id;
	}
	
	/* RELATIONS */

	/* SCOPES */
	public function scopeCurrentlyOpen($query)
	{
		$now = date('Y-m-d H:i');
		$query->where('registration_start', '<=', $now)->where('registration_end', '>', $now);
	}

	/* CALCULATED FIELDS */
    public function getDisplayableQrInfo()
    {
        return route('inscription.landing', ['qr_code' => $this->getQrCodeString()]);
    }

	/* ACTIONS */

	/* ELEMENTS */
}
