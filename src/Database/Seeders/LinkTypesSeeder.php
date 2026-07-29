<?php

namespace Condoedge\Crm\Database\Seeders;

use Condoedge\Crm\Models\LinkDirectionEnum;
use Condoedge\Crm\Models\LinkType;
use Condoedge\Crm\Models\LinkTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * link_types is a fixed reference list: the enum value is the row id, so ids stay
 * stable across teams and environments and nothing has to hardcode a number.
 * Idempotent — re-running only re-asserts labels, direction and opposites.
 *
 * WARNING: this claims ids 1-13 and overwrites whatever sits there. Whether those ids are
 * free is a fact about the installing database, so this is deliberately NOT wired to a
 * package migration — the app calls it once it has checked (see SISC's
 * 2026_07_29_100000_seed_canonical_link_types).
 */
class LinkTypesSeeder extends Seeder
{
    public function run()
    {
        foreach (LinkTypeEnum::cases() as $enum) {
            $type = LinkType::withTrashed()->find($enum->value) ?: new LinkType();

            $type->id = $enum->value;
            $type->deleted_at = null;
            $type->team_id = null; // Global list: LinkType is NoTeamScope, every team reads the same rows.
            $type->link_name = __($enum->rawTranslationKey(), locale: 'en');
            $type->label = collect(array_keys(config('kompo.locales')))
                ->mapWithKeys(fn ($locale) => [$locale => __($enum->rawTranslationKey(), locale: $locale)])
                ->all();
            $type->direction = $enum->direction();
            $type->save();
        }

        // Second pass: the opposite row has to exist before it can be pointed at.
        foreach (LinkTypeEnum::cases() as $enum) {
            LinkType::withTrashed()->whereKey($enum->value)->update(['opposite_id' => $enum->opposite()->value]);
        }

        $this->retireNonCanonicalTypes();
    }

    /**
     * Anything outside the canonical list is folded into the equivalent canonical row and
     * retired, so a picker can never offer two rows meaning the same thing. Folding keeps
     * the legacy row's own direction — demoting a guardian type to Other would silently
     * drop can_manage authority.
     */
    protected function retireNonCanonicalTypes(): void
    {
        $legacy = LinkType::whereNotIn('id', array_column(LinkTypeEnum::cases(), 'value'))->get();

        if ($legacy->isEmpty()) {
            return;
        }

        foreach ($legacy as $type) {
            $replacement = match ($type->direction) {
                LinkDirectionEnum::PARENT_OF => LinkTypeEnum::PARENT,
                LinkDirectionEnum::CHILD_OF => LinkTypeEnum::CHILD,
                default => LinkTypeEnum::OTHER,
            };

            $moved = DB::table('person_links')
                ->where('link_type_id', $type->id)
                ->update(['link_type_id' => $replacement->value]);

            $this->command?->info("Link type #{$type->id} ({$type->link_name}) retired, {$moved} links moved to {$replacement->name}.");
        }

        LinkType::whereIn('id', $legacy->pluck('id'))->delete();
    }
}
