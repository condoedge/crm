<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * link_types kept the two sides of a relation as two free-text labels (link_from_label /
 * link_to_label) and two free booleans (is_parent / is_child). Neither worked:
 *
 *   - nothing could render link_to_label, because the reverse label was resolved off the
 *     type row against a person id it does not have, so both sides read person1's label;
 *   - the booleans have no way to say "neither", so every non-parent row was filed as
 *     is_child — spouses, siblings and friends all claiming to be somebody's kid.
 *
 * A row is now one directed role ("person1 is the {label} of person2") pointing at the
 * row that describes the same link from person2. `direction` is the only writable
 * direction field; is_parent/is_child become generated projections of it, so every
 * existing query that joins on them keeps working and can no longer be contradicted.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('link_types', function (Blueprint $table) {
            $table->json('label')->nullable()->after('link_name');
            $table->string('direction', 20)->default('PEER')->after('label');
            $table->foreignId('opposite_id')->nullable()->after('direction')->constrained('link_types');
        });

        // link_from_label described person1, which is the row itself.
        DB::table('link_types')->update([
            'label' => DB::raw("COALESCE(link_from_label, JSON_OBJECT('en', link_name, 'fr', link_name))"),
            'direction' => DB::raw("CASE WHEN is_parent = 1 THEN 'PARENT_OF' WHEN is_child = 1 THEN 'CHILD_OF' ELSE 'PEER' END"),
        ]);

        Schema::table('link_types', function (Blueprint $table) {
            $table->dropColumn(['is_parent', 'is_child']);
        });

        Schema::table('link_types', function (Blueprint $table) {
            $table->boolean('is_parent')->storedAs("direction = 'PARENT_OF'");
            $table->boolean('is_child')->storedAs("direction = 'CHILD_OF'");
        });

        Schema::table('link_types', function (Blueprint $table) {
            $table->dropColumn(['link_from_label', 'link_to_label']);
        });
    }

    public function down(): void
    {
        Schema::table('link_types', function (Blueprint $table) {
            $table->json('link_from_label')->nullable();
            $table->json('link_to_label')->nullable();
        });

        DB::table('link_types')->update([
            'link_from_label' => DB::raw('label'),
            'link_to_label' => DB::raw('(select l2.label from (select id, label from link_types) as l2 where l2.id = link_types.opposite_id)'),
        ]);

        Schema::table('link_types', function (Blueprint $table) {
            $table->dropColumn(['is_parent', 'is_child']);
        });

        Schema::table('link_types', function (Blueprint $table) {
            $table->boolean('is_parent')->default(false);
            $table->boolean('is_child')->default(false);
        });

        DB::table('link_types')->update([
            'is_parent' => DB::raw("direction = 'PARENT_OF'"),
            'is_child' => DB::raw("direction = 'CHILD_OF'"),
        ]);

        Schema::table('link_types', function (Blueprint $table) {
            $table->dropForeign(['opposite_id']);
            $table->dropColumn(['label', 'direction', 'opposite_id']);
        });
    }
};
