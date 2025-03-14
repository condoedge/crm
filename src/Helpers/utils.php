<?php


function getDynamicallyModal() {
    return getAppClass(App\Kompo\Common\Modal::class, Kompo\Auth\Common\Modal::class);
}

function getInscriptionTypes()
{
    return collect(config('condoedge-crm.inscription-types'))->mapWithKeys(fn($e) => [$e->value => $e]);
}

function getInscriptionTypesKeys()
{
    return collect(getInscriptionTypes())->map(fn($e) => $e->value);
}

if (!function_exists('getAppClass')) {
	function getAppClass($namespaceInApp, $defaultNamespace = null)
	{
		if (class_exists($namespaceInApp)) {
			return $namespaceInApp;
		}

		return $defaultNamespace;
	}
}
