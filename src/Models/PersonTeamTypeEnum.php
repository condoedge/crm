<?php

namespace Condoedge\Crm\Models;

enum PersonTeamTypeEnum: int
{
    use \Kompo\Models\Traits\EnumKompo;

    public function label(): string
    {
        return match ($this) {
            default => __('crm.unknown'),
        };
    }

    public function getRegularRole()
    {
        return match ($this) {
        };
    }

    public function getTrialRole()
    {
        return match ($this) {
        };
    }

    public function getAllRoles()
    {
        return [$this->getRegularRole(), $this->getTrialRole()];
    }

    public static function parentAndScoutRoles()
    {
        return [
        ];
    }

    public static function getByRole($role)
    {
        return match ($role) {
        };
    }
}
