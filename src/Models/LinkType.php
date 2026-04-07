<?php

namespace Condoedge\Crm\Models;

use Kompo\Database\HasTranslations;

use Condoedge\Utils\Models\Model;

class LinkType extends Model
{
    use HasTranslations;

    protected $translatable = ['link_from_label', 'link_to_label'];

    /* RELATIONS */

    /* SCOPES */

    /* CALCULATED FIELDS */
    public function getLinkingLabel($forPersonId)
    {
        return ($this->person1_id == $forPersonId ? 
            $this->link_from_label : 
            $this->link_to_label) ?: $this->link_name;
    }


    /* ACTIONS */

    /* ELEMENTS */
}
