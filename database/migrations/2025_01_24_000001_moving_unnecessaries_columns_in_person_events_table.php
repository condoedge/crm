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
        Schema::table('person_events', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropColumn('inscription_id');
            $table->dropColumn([
                'family_conditions_text',
                'family_conditions',
                'accept_care',
                'accept_photos',
                'accept_privacy',
                'accept_participation',
            ]);
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->string('family_conditions_text')->nullable();
            $table->tinyInteger('family_conditions')->nullable();
            $table->tinyInteger('accept_care')->nullable();
            $table->tinyInteger('accept_photos')->nullable();
            $table->tinyInteger('accept_privacy')->nullable();
            $table->tinyInteger('accept_participation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_events', function (Blueprint $table) {
            $table->foreignId('inscription_id')->constrained();
            $table->text('family_conditions_text')->nullable();
        });
    }
};
