@component('mail::message')

<p>Vous avez ete accepte. Veuillez payer:</p>

<p>{!! makeMailButton('Create account and pay', $acceptInscriptionUrl) !!}</p>

@endcomponent
