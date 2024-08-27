<?php


function getInscriptionTypes()
{
    return config('condoedge-crm.inscription-types')::cases();
}

function getInscriptionTypesKeys()
{
    return collect(getInscriptionRoutes())->map(fn($e) => $e->value);
}