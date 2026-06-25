<?php

namespace Condoedge\Crm\Models;

use Condoedge\Utils\Models\Model;
use Kompo\Database\HasTranslations;

class DiciplinaryReasonType extends Model
{
    use HasTranslations;

    protected $translatable = ['name', 'description'];

    // We don't separation, but i wanna keep an easy way to extend to it in the future.
    public function scopeForTeams($query, $teamIds)
    {
        return $query;
    }
}