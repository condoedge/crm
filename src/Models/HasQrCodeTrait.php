<?php

namespace Condoedge\Crm\Models;

trait HasQrCodeTrait
{
	//public const QRCODE_COLUMN_NAME = 'qrcode_col_name';
	//public const QRCODE_LENGTH = 8;

    /* RELATIONS */

    /* CALCULATED FIELDS */
    public function getQrCodeString()
    {
        return $this->{static::QRCODE_COLUMN_NAME};
    }

    /* ACTIONS */
    public function setQrCodeIfEmpty($qrCode = null)
    {
        if ($this->{static::QRCODE_COLUMN_NAME}) {
            return;
        }

        $this->setNewQrCode($qrCode);
    }

    public function setNewQrCode($qrCode = null)
    {
        $this->{static::QRCODE_COLUMN_NAME} = $qrCode ?: static::getNewQrCode();
    }

    public static function getNewQrCode()
    {
        return \Str::random(static::QRCODE_LENGTH);
    }

    /* SCOPES */
    public function scopeForQrCode($query, $qrCode)
    {
    	$query->where(static::QRCODE_COLUMN_NAME, $qrCode);
    }

}
