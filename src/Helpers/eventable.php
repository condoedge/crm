<?php 

use App\Models\Events\Event;
use App\Models\Events\EventRegistrationPeriod;

function registrableFromQrCode($qrCode)
{
	if (!$qrCode) {
		return;
	}
	
	return EventRegistrationPeriod::forQrCode($qrCode)->first() ?: Event::forQrCode($qrCode)->first();
}

/* ELEMENTS */
function _RegistrableFromQrCodeTitle($qrCode)
{
	$item = registrableFromQrCode($qrCode);

	if (!$item) {
		return;
	}

	return _Rows(
        _Html('Registering to'),
        _Title($item->getNameDisplay()),
    );
}