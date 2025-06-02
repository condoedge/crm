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
        Schema::create('occupations', function (Blueprint $table) {

            addMetaData($table);

            $table->string('code');
            $table->text('description')->nullable();

            $table->foreignId('audience_id')->nullable();
            $table->tinyInteger('is_billable')->nullable();
            $table->tinyInteger('is_background_check_required')->nullable();
            $table->tinyInteger('is_youth_priority_certificate_required')->nullable();
            $table->tinyInteger('is_adult_expected_behavior_code_required')->nullable();
            $table->tinyInteger('is_general_training_course')->nullable();
            $table->tinyInteger('is_recognition_acceptance_risks_is_required')->nullable();

            $table->string('legacy_image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupations');
    }
};
