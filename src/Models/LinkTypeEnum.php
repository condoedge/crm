<?php

namespace Condoedge\Crm\Models;

/**
 * The canonical link_types rows. The enum value IS the link_types.id — the table is a
 * fixed reference list seeded by LinkTypesSeeder, so ids are stable across environments
 * and no caller needs a magic number.
 *
 * Read every case as: person1 is the {case} of person2.
 */
enum LinkTypeEnum: int
{
    use \Condoedge\Utils\Models\Traits\EnumKompo;

    case MOTHER = 1;
    case FATHER = 2;
    case PARENT = 3;
    case LEGAL_GUARDIAN = 4;
    case OTHER = 5;
    case FRIEND = 6;
    case GRANDPARENT = 7;
    case STEP_PARENT = 8;
    case SPOUSE = 9;
    case FAMILY_FRIEND = 10;
    case CHILD = 11;
    case SIBLING = 12;
    case GRANDCHILD = 13;

    public function label()
    {
        return __($this->rawTranslationKey());
    }

    public function rawTranslationKey(): string
    {
        return match ($this) {
            self::MOTHER => 'crm.mother',
            self::FATHER => 'crm.father',
            self::PARENT => 'crm.parent',
            self::LEGAL_GUARDIAN => 'crm.legal-guardian',
            self::STEP_PARENT => 'crm.step-parent',
            self::CHILD => 'crm.child',
            self::GRANDPARENT => 'crm.grandparent',
            self::GRANDCHILD => 'crm.grandchild',
            self::SIBLING => 'crm.sibling',
            self::SPOUSE => 'crm.spouse',
            self::FRIEND => 'crm.friend',
            self::FAMILY_FRIEND => 'crm.family-friend',
            self::OTHER => 'crm.other',
        };
    }

    /**
     * Guardianship, not genealogy: a grandparent is a PEER because they hold no
     * authority over the grandchild unless they are also named legal guardian.
     */
    public function direction(): LinkDirectionEnum
    {
        return match ($this) {
            self::MOTHER, self::FATHER, self::PARENT, self::LEGAL_GUARDIAN, self::STEP_PARENT => LinkDirectionEnum::PARENT_OF,
            self::CHILD => LinkDirectionEnum::CHILD_OF,
            default => LinkDirectionEnum::PEER,
        };
    }

    /**
     * The row describing the same link seen from person2. Granular parent roles all
     * invert to the single CHILD row; CHILD inverts back to the generic PARENT.
     */
    public function opposite(): self
    {
        return match ($this) {
            self::MOTHER, self::FATHER, self::PARENT, self::LEGAL_GUARDIAN, self::STEP_PARENT => self::CHILD,
            self::CHILD => self::PARENT,
            self::GRANDPARENT => self::GRANDCHILD,
            self::GRANDCHILD => self::GRANDPARENT,
            default => $this,
        };
    }

    /**
     * True when storing the link in this orientation would throw detail away: answering
     * "Child" says nothing about which kind of guardian the other end is, because the
     * opposite row is the generic Parent. The caller has to name person1's role instead,
     * and the link gets written the other way round — the granular side is always person1.
     */
    public function needsCounterpartRole(): bool
    {
        return $this->direction() === LinkDirectionEnum::CHILD_OF;
    }

    /** Types to offer when the person filling the form is the responsible adult. */
    public static function guardianCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case) => $case->direction() === LinkDirectionEnum::PARENT_OF
        ));
    }

    public function model(): ?LinkType
    {
        return LinkType::find($this->value);
    }
}
