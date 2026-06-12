<?php

namespace Condoedge\Crm\Contracts;

use Condoedge\Crm\Models\Inscription;

/**
 * The host binds this contract to its implementation (SISC binds
 * App\Services\Inscriptions\InscriptionMembershipService).
 */
interface InscriptionRegistrationServiceContract
{
    /** Confirm this inscription's registration into membership if its registering user exists. */
    public function confirmPendingRegistration(Inscription $inscription): void;
}
