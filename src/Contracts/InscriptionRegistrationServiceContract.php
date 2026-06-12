<?php

namespace Condoedge\Crm\Contracts;

use Condoedge\Crm\Models\Inscription;

/**
 * The host binds this contract to its implementation (SISC binds
 * App\Services\Inscriptions\InscriptionMembershipService).
 */
interface InscriptionRegistrationServiceContract
{
    public function confirmRegistrationIfUserExists(Inscription $inscription): void;
}
