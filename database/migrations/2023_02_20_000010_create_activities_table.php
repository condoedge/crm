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
        Schema::create('activities', function (Blueprint $table) {
            
            addMetaData($table);

            $table->foreignId('team_id')->nullable()->constrained();
            $table->string('name_av');
            $table->string('subtitle_av')->nullable();
            $table->string('description_av')->nullable();

            $table->tinyInteger('schedule_frequency')->default(ScheduleFrequencyEnum::SINGLE);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('event_max_members')->nullable();

            $table->string('qrcode_av')->nullable();

            $table->text('cover_av')->nullable();
            $table->string('color_av')->nullable();
            $table->string('btn_color_av')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
