@component('mail::message')

<p>{{!! __('translate.inscriptions.confirmation-email-message-1')}}</p>

<p>{!! makeMailButton(__('translate.confirm-inscription'), $acceptInscriptionUrl) !!}</p>

@endcomponent
