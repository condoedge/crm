<?php

namespace Condoedge\Crm\Models;

use Kompo\Database\HasTranslations;

use Condoedge\Utils\Models\Model;
use Kompo\Auth\Contracts\Security\NoTeamScope;

/**
 * A directed relationship role: person1 is the {label} of person2.
 *
 * Rows are the canonical LinkTypeEnum list (id = enum value). `direction` is the only
 * writable direction field — is_parent/is_child are generated columns projected from it,
 * so a row can neither claim both nor disagree with its opposite.
 */
class LinkType extends Model implements NoTeamScope
{
    use HasTranslations;

    protected $translatable = ['label'];

    protected $casts = [
        'direction' => LinkDirectionEnum::class,
    ];

    /* RELATIONS */
    public function opposite()
    {
        return $this->belongsTo(static::class, 'opposite_id');
    }

    /* SCOPES */
    public function scopeGuardianTypes($query)
    {
        return $query->where('direction', LinkDirectionEnum::PARENT_OF->value);
    }

    /* CALCULATED FIELDS */
    /** Role of person1 — that is this row itself. */
    public function getLinkingLabelForPerson1()
    {
        return $this->label ?: $this->link_name;
    }

    /**
     * Role of person2 — that is the opposite row. A row with no opposite would answer with
     * person1's label on both ends, which is the defect this model replaced, so fall back
     * to the canonical pairing before falling back to the row itself.
     */
    public function getLinkingLabelForPerson2()
    {
        if ($opposite = $this->opposite ?: LinkTypeEnum::tryFrom($this->id)?->opposite()->model()) {
            return $opposite->getLinkingLabelForPerson1();
        }

        return $this->label ?: $this->link_name;
    }

    /* ACTIONS */

    /* ELEMENTS */
    /**
     * Options for a picker whose answer describes person1 — the contact being added, or
     * the adult filling an inscription. Every link is created with the role-holder on
     * person1, so this is the only side a picker ever asks about.
     */
    public static function person1Options($query = null): array
    {
        return ($query ?: static::query())->get()
            // Guardian roles first, "Other" last, the rest alphabetical in the current locale.
            ->sortBy(fn ($type) => [
                $type->direction === LinkDirectionEnum::PARENT_OF ? 0 : ($type->id == LinkTypeEnum::OTHER->value ? 2 : 1),
                $type->getLinkingLabelForPerson1(),
            ])
            ->mapWithKeys(fn ($type) => [$type->id => $type->getLinkingLabelForPerson1()])
            ->all();
    }

    public static function guardianOptions(): array
    {
        return static::person1Options(static::query()->guardianTypes());
    }
}
