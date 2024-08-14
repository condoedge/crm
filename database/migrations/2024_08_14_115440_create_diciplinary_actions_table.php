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
        Schema::create('diciplinary_actions', function (Blueprint $table) {
            addMetaData($table);

            $table->tinyInteger('action_reason_type'); // ENUM
            $table->text('action_reason_description');
            $table->tinyInteger('action_type'); // ENUM

            $table->foreignId('person_id')->constrained()->cascadeOnDelete();

            $table->timestamp('action_from');
            $table->timestamp('action_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diciplinary_actions');
    }
};
