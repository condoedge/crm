<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Kompo\Common\ImgFormLayout;

abstract class PersonBasicInfoForm extends ImgFormLayout
{
    use PersonBasicInfoFormV1Trait;

    public $model = PersonModel::class;

    public function rightColumnBody()
    {
        return array_merge([
            _TitleMain($this->getTitle())->class('self-center my-8'),
            _TitleModalSub($this->getSubtitle())->class('mb-8 text-center'),
        ], $this->bodyContent());
    }
}
