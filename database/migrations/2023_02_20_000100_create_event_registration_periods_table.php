<?php

use Condoedge\Crm\Models\RegistrationTypeEnum;
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
        Schema::create('event_registration_periods', function (Blueprint $table) {
            
            addMetaData($table);

            $table->foreignId('event_id')->constrained();
            
            $table->string('registration_name')->nullable();
            $table->tinyInteger('registration_type')->default(RegistrationTypeEnum::RT_OPEN_ALL);

            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_end')->nullable();

            $table->integer('registration_max_members')->nullable();
            $table->decimal('registration_price', 12, 2)->nullable();
            
            $table->string('qrcode_rg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registration_periods');
    }
};
