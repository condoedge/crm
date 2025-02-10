<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

// Trick to get the project modal if it exists, otherwise get the kompo modal
class_alias(getDynamicallyModal(), 'Condoedge\Crm\Kompo\Inscriptions\Modal');

class SelectRegularOrTrialInscriptionModal extends Modal
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $_Title = 'translate.inscriptions.select-regular-or-trial';

    public $class = 'overflow-y-auto mini-scroll max-w-xl bg-level1 rounded-lg';

    protected $baseType;

    public function created()
    {
        $this->setInscriptionInfo();

        $this->baseType = getInscriptionTypes()[$this->prop('base_type')];
    }

    public function header()
    {
        return [
            _ModalTitle('translate.inscriptions.select-regular-or-trial')?->class('!text-white !text-xl sm:!text-2xl'),
        ];
    }

    public function body()
    {
        return _Flex(
            _Rows(
                _Html('translate.regular-inscription')->class('text-center !text-level1 font-semibold text-2xl mb-4'),
                _Html('translate.regular-inscription-desc')->class('!text-level1'),
            )->button2()->class('rounded-lg !pt-3 p-6 flex-1')->selfGet('manageInscriptionLink', ['type' => $this->baseType->value])->redirect(),
            _Rows(
                _Html('translate.trial-inscription')->class('text-center !text-level1 font-semibold text-2xl mb-4'),
                _Html('translate.trial-inscription-desc')->class('!text-level1'),
            )->button2()->class('rounded-lg !pt-3 p-6 flex-1')->selfGet('manageInscriptionLink', ['type' => $this->baseType->regularToTrial()->value])->redirect(),
        )->class('gap-3 w-full');
    }
}