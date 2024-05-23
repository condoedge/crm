<?php 

use Condoedge\Crm\Models\Event;

function registrableFromQrCode($qrCode)
{
	return Event::forQrCode($qrCode)->first();
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