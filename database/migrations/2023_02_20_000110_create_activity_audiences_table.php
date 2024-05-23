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
        Schema::create('activity_audiences', function (Blueprint $table) {
            
            addMetaData($table);

            $table->foreignId('activity_id')->constrained();
            $table->tinyInteger('event_audience')->nullable();
            $table->string('audience_concern')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_audiences');
    }
};
