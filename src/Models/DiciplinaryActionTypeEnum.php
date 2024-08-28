<?php

namespace Condoedge\Crm\Models;

enum DiciplinaryActionTypeEnum: int
{
    use \Kompo\Auth\Models\Traits\EnumKompo;

    case BLOCK = 1;
    case BAN = 2;

    public function label()
    {
        return match ($this) {
            self::BLOCK => 'disciplinary.block',
            self::BAN => 'disciplinary.ban',
        };
    }

    public function formTitle()
    {
        return match ($this) {
            self::BLOCK => 'disciplinary.block-this-member',
            self::BAN => 'disciplinary.ban-this-member',
        };
    }

    public function classes()
    {
        return match ($this) {
            self::BLOCK => 'bg-dangerlight text-danger',
            self::BAN => 'bg-dangerlight text-danger',
        };
    }

    public function startedAction($diciplinaryAction)
    {
        if ($this->checkIfHasOthersAction($diciplinaryAction)) {
            return;
        }
        
        $user = $diciplinaryAction->person->relatedUser;

        return match($this) {
            self::BLOCK => $user?->block(),
            self::BAN => $user?->ban(),
            default => null,
        };
    }

    public function finishedAction($diciplinaryAction)
    {
        if ($this->checkIfHasOthersAction($diciplinaryAction)) {
            return;
        }

        $user = $diciplinaryAction->person->relatedUser()->withoutGlobalScopes()->first();

        return match($this) {
            self::BLOCK => $user?->unblock(),
            self::BAN => $user?->unban(),
            default => null,
        };
    }

    protected function checkIfHasOthersAction($diciplinaryAction)
    {
        return $diciplinaryAction->person->diciplinaryActions()
            ->active()
            ->whereDate('action_to', '!=', now())
            ->where('action_type', $diciplinaryAction->action_type)
            ->count();
    }
}