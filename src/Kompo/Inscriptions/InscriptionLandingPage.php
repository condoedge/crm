<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Utils\Kompo\Common\ImgFormLayout;

class InscriptionLandingPage extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $imgUrl = 'images/base-email-image.png';
    protected $rightColumnBodyWrapperClass = '';

    public function created()
    {
        $this->setInscriptionInfo();
    }

    public function rightColumnBody()
    {
        return _Rows(
            _LogoOnly()->class('self-center')->style('margin-bottom:60px;'),
            _RegistrableFromQrCodeTitle($this->inscriptionCode),
            _Html('inscriptions.i-want-to-register-and-i-am')->class('mb-6 text-lg'),
            _Rows(
                collect(getInscriptionTypes())->filter(fn ($it) => (!$this->inscription?->type || $this->inscription?->type?->value == $it->value) && !$it->isTrial())
                    ->map(function ($type) {
                        return $this->optionButton($type);
                    }),
            )->class('gap-6'),
        );
    }

    /**
     * @param \Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum $type
     *
     * @return mixed
     */
    protected function optionButton($type)
    {
        return _Rows(
            _Html($type->registerTitle())->class('text-center !text-level1 font-semibold text-2xl mb-4'),
            _Html($type->registerDescription())->class('!text-level1'),
        )->button2()->class('rounded-lg !pt-3 p-6')
            ->when(!$type->allowTrial(), fn ($el) => $el->selfGet('manageInscriptionLink', ['type' => $type->value])->redirect())
            ->when($type->allowTrial(), fn ($el) => $el->selfGet('selectRegularOrTrialModal', ['type' => $type->value])->inModal());
    }

    public function selectRegularOrTrialModal($type)
    {
        return new SelectRegularOrTrialInscriptionModal(null, [
            'inscription_code' => $this->inscriptionCode,
            'base_type' => $type,
        ]);
    }
}
