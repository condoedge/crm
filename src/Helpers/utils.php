<?php


function getInscriptionTypes()
{
    return collect(config('condoedge-crm.inscription-types'))->mapWithKeys(fn ($e) => [$e->value => $e]);
}

function getInscriptionTypesKeys()
{
    return collect(getInscriptionTypes())->map(fn ($e) => $e->value);
}
