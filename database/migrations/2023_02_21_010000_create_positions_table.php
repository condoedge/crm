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
        Schema::create('positions', function (Blueprint $table) {
            
            addMetaData($table);

            $table->string('code');
            $table->text('description')->nullable();

            $table->foreignId('audience_id')->nullable();
            $table->tinyInteger('is_unique_per_team')->nullable();
            $table->tinyInteger('is_level1')->nullable();
            $table->tinyInteger('is_level2')->nullable();
            $table->tinyInteger('is_level3')->nullable();
            $table->tinyInteger('is_level4')->nullable();
            
            $table->string('legacy_image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
