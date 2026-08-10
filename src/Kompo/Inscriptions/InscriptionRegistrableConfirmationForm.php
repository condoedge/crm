<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Kompo\Common\ImgFormLayout;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InscriptionRegistrableConfirmationForm extends ImgFormLayout
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    protected $imgUrl = 'images/base-email-image.webp';

    protected $eventId;
    protected $event;

    public $model = PersonModel::class;

    public function created()
    {
        $this->setInscriptionInfo();
        $this->model($this->person);

        $this->eventId = $this->inscription->event_id;
        $this->event = $this->inscription->event;
    }

    public function rightColumnBody()
    {
        return _Rows(
            _Rows(
                $this->mainInscription->getAllRelatedInscriptions()
                    ->map(fn ($inscription) => _Rows(
                        _Rows(
                            _Html($inscription->person?->full_name)->class('text-2xl'),
                            _TitleModalSub($inscription->person?->age_label),
                        )->class('text-center mb-4'),
                        $this->customRegistrableInfo($inscription),
                    )->href($inscription->getInscriptionPersonRoute()))->toArray()
            ),
            !$this->inscription->type->basedInInscriptionForOtherPerson() ? null : _Link2Outlined('inscriptions.register-and-add-another-child')->selfPost('registerAndAddAnother')->redirect()->class('mb-4'),
            _Button('inscriptions.register-and-complete')->selfPost('registerAndFinish')->redirect(),
        )->class('p-8');
    }

    protected function customRegistrableInfo($inscription)
    {
        //Override
    }

    public function registerAndAddAnother()
    {
        $inscription = InscriptionModel::getOrCreatePendingForMainPerson($this->mainPerson->id, $this->event->team_id, $this->inscription->type);
        $inscription->related_inscription_id = $this->mainInscription->id;
        $inscription->setSelectedTeam($this->event->team_id, $this->event);

        return redirect($inscription->getInscriptionPersonLinkRoute());
    }

    public function registerAndFinish()
    {
        try {
            DB::beginTransaction();

            $this->mainInscription->getAllRelatedInscriptions()
                ->filter(fn ($inscription) => $inscription->isRegistrable())
                ->each(fn ($inscription) => $inscription->confirmInscriptionFilled());
        } catch (Exception $e) {
            Log::error('Error trying to confirm inscriptions', $e->getTrace());

            DB::rollback();

            // event_id null
            if ($e->getCode() === 100) {
                return response()->modal($this->errorNonSelectEventModal());
            }

            return response()->modal($this->errorUnknownErrorModal());
        }

        DB::commit();

        return redirect($this->mainInscription->getConsentPageRoute());
    }

    protected function errorNonSelectEventModal()
    {
        return _Rows(
            _Html('translate.you-are-trying-to-finalize-an-inscription-without-selected-event'),

            _FlexEnd(
                _Button('translate.closeModal')->run('({modal}) => modal().close()'),
            )->class('mt-4'),
        )->class('p-8');
    }

    protected function errorUnknownErrorModal()
    {
        return _Rows(
            _Html('translate.we-got-a-unknown-error-trying-to-finish-your-inscription-please-try-later'),

            _FlexEnd(
                _Button('translate.close')->run('({modal}) => modal().close()'),
            )->class('mt-4'),
        );
    }
}
