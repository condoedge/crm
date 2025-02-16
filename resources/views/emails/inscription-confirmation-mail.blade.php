@component('mail::message')

<p>{{!! __('inscriptions.confirmation-email-message-1')}}</p>

<p>{!! makeMailButton(__('inscriptions.confirm-inscription'), $acceptInscriptionUrl) !!}</p>

@endcomponent
