<?php


function getInscriptionTypes()
{
    return config('condoedge-crm.inscription-types');
}

function getInscriptionTypesKeys()
{
    return collect(getInscriptionTypes())->map(fn($e) => $e->value);
}