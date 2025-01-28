<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
