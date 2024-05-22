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
        Schema::create('events', function (Blueprint $table) {
            
            addMetaData($table);

            $table->foreignId('activity_id')->constrained();
            $table->dateTime('schedule_start')->nullable();
            $table->dateTime('schedule_end')->nullable();

            $table->string('qrcode_ev')->nullable();
            $table->text('cover_ev')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
