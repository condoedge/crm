<?php

//Registration routes for new members

use Condoedge\Crm\Http\Controllers\CustomInscriptionGenerable;

Route::layout('layouts.guest')->middleware('disable-automatic-security')->group(function () {

    // NOTE: SISC overrides these vendor routes by repointing the class at the SISC subclass.
    // Route URIs and names are unchanged so signed magic-link emails keep working.
    // Class targets resolve at request time, so swapping the class string here is safe and
    // does not touch any other consumer.
    Route::get('join/{inscription_code?}', class_exists(\App\Kompo\Inscriptions\SiscInscriptionLandingPage::class)
        ? \App\Kompo\Inscriptions\SiscInscriptionLandingPage::class
        : Condoedge\Crm\Kompo\Inscriptions\InscriptionLandingPage::class)->name('inscription.landing');

    Route::get('join-email/{inscription_code?}', class_exists(\App\Kompo\Inscriptions\SiscInscriptionEmailStep1Form::class)
        ? \App\Kompo\Inscriptions\SiscInscriptionEmailStep1Form::class
        : Condoedge\Crm\Kompo\Inscriptions\InscriptionEmailStep1Form::class)->name('inscription.email.step1');

    Route::middleware(['signed'])->group(function () {

        Route::get('inscription/confirmation/{inscription_code}', class_exists(\App\Kompo\Inscriptions\InscriptionRegistrableConfirmationForm::class)
            ? \App\Kompo\Inscriptions\InscriptionRegistrableConfirmationForm::class
            : Condoedge\Crm\Kompo\Inscriptions\InscriptionRegistrableConfirmationForm::class)->name('inscription.confirmation');

        Route::get('create-account/{inscription_code}', class_exists(\App\Kompo\Inscriptions\SiscPersonRegistrableRegisterForm::class)
            ? \App\Kompo\Inscriptions\SiscPersonRegistrableRegisterForm::class
            : Condoedge\Crm\Kompo\Auth\PersonRegistrableRegisterForm::class)->name('person-registrable.register');


    });

    Route::get('inscription-page/{link_code}', CustomInscriptionGenerable::class)->name('inscription-generation-page');

});

Route::middleware(['signed', 'throttle:10,1', 'disable-automatic-security'])->group(function () {

    Route::get('accept-inscription/{id}', Condoedge\Crm\Http\Controllers\PersonRegistrableAcceptController::class)->name('person-registrable.accept');

});


//Registration Management in dashboard
Route::layout('layouts.dashboard')->middleware(['auth'])->group(function () {

    Route::get('inscriptions/{event_id}', Condoedge\Crm\Kompo\InscriptionHandling\InscriptionsList::class)
        ->name('inscriptions.list');
});
