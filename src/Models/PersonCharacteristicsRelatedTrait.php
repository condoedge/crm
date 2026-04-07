<?php

namespace Condoedge\Crm\Models;

trait PersonCharacteristicsRelatedTrait
{
    /* RELATIONS */

    /* CALCULATED FIELDS */
    public function getAgeLabelAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return getAgeFromDob($this->date_of_birth) .' '.__('general-years');
    }

    public function getGenderLabelAttribute()
    {
        return $this->gender?->labelFromAge($this->date_of_birth);
    }

    /* ACTIONS */

    /* SCOPES */
    public function scopeForGender($query, $gender)
    {
        $query->where('gender', $gender);
    }

    public function scopeIsMale($query)
    {
        $query->forGender(GenderEnum::MALE);
    }

    public function scopeIsFemale($query)
    {
        $query->forGender(GenderEnum::FEMALE);
    }

    public function scopeIsOther($query)
    {
        $query->forGender(GenderEnum::OTHER);
    }

    /* ELEMENTS */
    public function ageLabelPill()
    {
        if (!$this->date_of_birth) {
            return _Pill()->class('hidden');
        }

        return _Pill($this->age_label);
    }

    public function genderLabelPill()
    {
        if (!$this->gender) {
            return _Pill(__('crm.unknown'))->class('bg-gray-300 text-gray-800');
        }

        return _Pill($this->gender_label)->class($this->gender->bgColor2() . ' ' . $this->gender->textColor2());
    }
}
