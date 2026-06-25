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
        Schema::create('diciplinary_reason_types', function (Blueprint $table) {
            addMetaData($table);

            $table->json('name'); // Translatable
            $table->json('description')->nullable(); // Translatable
        });

        Schema::table('diciplinary_actions', function (Blueprint $table) {
            $table->foreignId('action_reason_type_id')->nullable()->constrained('diciplinary_reason_types');

            $table->dropColumn('action_reason_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diciplinary_reason_types');
    }
};
