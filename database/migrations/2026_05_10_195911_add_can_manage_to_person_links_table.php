<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `person_links.can_manage` — when true, the linked person's user inherits
 * ownership over the other side. Used by `Person::ownedRecordIdsForUser` to
 * grant parent-style access to managed persons.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('person_links')) {
            return;
        }

        if (!Schema::hasColumn('person_links', 'can_manage')) {
            Schema::table('person_links', function (Blueprint $table) {
                $table->boolean('can_manage')->default(false)->after('link_type_id');
            });
        }

        $this->createIndexIfMissing(
            'person_links',
            'person_links_can_manage_index',
            ['can_manage'],
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('person_links')) {
            return;
        }

        $this->dropIndexIfExists('person_links', 'person_links_can_manage_index');

        if (Schema::hasColumn('person_links', 'can_manage')) {
            Schema::table('person_links', function (Blueprint $table) {
                $table->dropColumn('can_manage');
            });
        }
    }

    private function createIndexIfMissing(string $table, string $name, array $columns): void
    {
        $exists = DB::selectOne(
            'SELECT 1 AS x
               FROM information_schema.statistics
              WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
              LIMIT 1',
            [$table, $name],
        );

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $tbl) use ($columns, $name) {
            $tbl->index($columns, $name);
        });
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        $exists = DB::selectOne(
            'SELECT 1 AS x
               FROM information_schema.statistics
              WHERE table_schema = DATABASE()
                AND table_name = ?
                AND index_name = ?
              LIMIT 1',
            [$table, $name],
        );

        if (!$exists) {
            return;
        }

        Schema::table($table, function (Blueprint $tbl) use ($name) {
            $tbl->dropIndex($name);
        });
    }
};
