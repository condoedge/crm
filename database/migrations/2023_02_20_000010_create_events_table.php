<?php

use Condoedge\Crm\Models\ScheduleFrequencyEnum;

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

            $table->foreignId('team_id')->nullable()->constrained();
            $table->foreignId('event_id')->nullable()->constrained(); //recursive one event can have multiple ones below

            $table->tinyInteger('event_type')->nullable();
            $table->string('name_ev');
            $table->string('subtitle_ev')->nullable();
            $table->string('description_ev')->nullable();

            $table->tinyInteger('schedule_frequency')->default(ScheduleFrequencyEnum::SINGLE);

            $table->dateTime('first_date')->nullable();
            $table->dateTime('last_date')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->tinyInteger('registration_system')->nullable();
            $table->string('qrcode_ev')->nullable();
            
            $table->decimal('event_price', 12, 2)->nullable();
            $table->tinyInteger('is_template')->nullable();
            $table->decimal('kickback_price', 12, 2)->nullable();
            
            $table->integer('event_max_members')->nullable();

            $table->text('cover_ev')->nullable();
            $table->string('color_ev')->nullable();
            $table->string('btn_color_ev')->nullable();
            
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
