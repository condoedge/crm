<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('person_teams', function (Blueprint $table) {
            $table->string('inscription_type')->nullable();

            $table->index('inscription_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_teams', function (Blueprint $table) {
            $table->dropColumn('inscription_type');
        });
    }
};
