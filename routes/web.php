<?php

//Registration routes for new members

use Condoedge\Crm\Http\Controllers\CustomInscriptionGenerable;

Route::layout('layouts.guest')->group(function(){

    Route::get('join/{inscription_code?}', Condoedge\Crm\Kompo\Inscriptions\InscriptionLandingPage::class)->name('inscription.landing');

    Route::get('join-email/{inscription_code?}', Condoedge\Crm\Kompo\Inscriptions\InscriptionEmailStep1Form::class)->name('inscription.email.step1');

	Route::middleware(['signed'])->group(function(){

    	Route::get('inscription/confirmation/{inscription_code}/{event_id}', Condoedge\Crm\Kompo\Inscriptions\InscriptionRegistrableConfirmationForm::class)->name('inscription.confirmation');

    	Route::get('create-account/{inscription_code}', Condoedge\Crm\Kompo\Auth\PersonRegistrableRegisterForm::class)->name('person-registrable.register');


    });

    Route::get('inscription-page/{link_code}', CustomInscriptionGenerable::class)->name('inscription-generation-page');

});

Route::middleware(['signed', 'throttle:10,1'])->group(function(){

    Route::get('accept-inscription/{id}', Condoedge\Crm\Http\Controllers\PersonRegistrableAcceptController::class)->name('person-registrable.accept');

});


//Registration Management in dashboard
Route::layout('layouts.dashboard')->middleware(['auth'])->group(function(){

    Route::get('inscriptions/{event_id}', Condoedge\Crm\Kompo\InscriptionHandling\InscriptionsList::class)
    	->name('inscriptions.list');
});

