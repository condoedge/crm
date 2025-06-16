<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Utils\Kompo\Common\Modal;

class SelectRegularOrTrialInscriptionModal extends Modal
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $_Title = 'inscriptions.select-regular-or-oneday';

    public $class = 'max-w-lg !bg-level1 rounded-lg';
    protected $headerClass = "!bg-level1 rounded-lg !border-none";
    protected $bodyWrapperClass = '!pt-0';

    protected $baseType;

    public function created()
    {
        $this->setInscriptionInfo();

        $this->baseType = getInscriptionTypes()[$this->prop('base_type')];
    }

    public function header()
    {
        return [
            _ModalTitle('inscriptions.select-regular-or-oneday')?->class('!text-white !text-xl sm:!text-2xl'),
        ];
    }

    public function body()
    {
        return _Rows(
            _Rows(
                _Html('inscriptions.regular-inscription')->class('text-center !text-level1 font-semibold text-2xl mb-4'),
                _Html('inscriptions.regular-inscription-desc')->class('!text-level1'),
            )->button2()->class('rounded-lg !pt-3 p-6 flex-1')->selfGet('manageInscriptionLink', ['type' => $this->baseType->value])->redirect(),
            _Rows(
                _Html('inscriptions.oneday-inscription')->class('text-center !text-level1 font-semibold text-2xl mb-4'),
                _Html('inscriptions.oneday-inscription-desc')->class('!text-level1'),
            )->button2()->class('rounded-lg !pt-3 p-6 flex-1')->selfGet('manageInscriptionLink', ['type' => $this->baseType->regularToTrial()->value])->redirect(),
        )->class('gap-6 w-full md:p-6');
    }
}
