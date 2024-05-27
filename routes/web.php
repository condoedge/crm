<?php

//Registration routes for new members
Route::layout('layouts.guest')->group(function(){

    Route::get('join/{qr_code?}', Condoedge\Crm\Kompo\Inscriptions\InscriptionLandingPage::class)->name('inscription.landing');

    Route::get('join-email/{qr_code?}', Condoedge\Crm\Kompo\Inscriptions\InscriptionEmailStep1Form::class)->name('inscription.email.step1');

	Route::middleware(['signed'])->group(function(){

    	Route::get('inscription/person-info/{id}/{qr_code?}', Condoedge\Crm\Kompo\Inscriptions\InscriptionPersonForm::class)->name('inscription.person');

    	Route::get('inscription/person-link-info/{person_id}/link{id?}/{qr_code?}', Condoedge\Crm\Kompo\Inscriptions\InscriptionPersonLinkForm::class)->name('inscription.person-link');

    	Route::get('inscription/registrable/{qr_code}/{id}', Condoedge\Crm\Kompo\Inscriptions\InscriptionRegistrableConfirmationForm::class)->name('inscription.registrable');

    	Route::get('create-account/{pr_id}', Condoedge\Crm\Kompo\Auth\PersonRegistrableRegisterForm::class)->name('person-registrable.register');


    });

});

Route::middleware(['signed', 'throttle:10,1'])->group(function(){

    Route::get('accept-inscription/{id}', Condoedge\Crm\Http\Controllers\PersonRegistrableAcceptController::class)->name('person-registrable.accept');

});


//Registration Management in dashboard
Route::layout('layouts.dashboard')->middleware(['auth'])->group(function(){

    Route::get('person-events/{event_id}', Condoedge\Crm\Kompo\InscriptionHandling\PersonEventsList::class)
    	->name('person-events.list');
});

