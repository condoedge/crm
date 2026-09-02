<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Models\InscriptionStatusEnum;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $inscription = InscriptionModel::findOrFail($id);

        if (!$inscription->status?->accepted() && $inscription->status !== InscriptionStatusEnum::INVITED_NOT_FILLED) {
            abort(403, __('error.inscription-not-accepted-yet'));
        }

        $user = $inscription->getRegisteringRelatedUser();

        if ($user) {
            $inscription->confirmInscriptionAsUserIfRegistered();
            auth()->login($user);

            return redirect()->to(route('dashboard'));
        } else {
            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
