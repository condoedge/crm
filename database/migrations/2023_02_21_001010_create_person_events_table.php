<?php

use App\Models\Inscriptions\RegisterStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('person_registrables');

        Schema::create('person_events', function (Blueprint $table) {

            addMetaData($table);

            $table->foreignId('person_id')->constrained('persons');
            $table->foreignId('event_id')->nullable()->constrained();
            $table->foreignId('inscription_id')->nullable()->constrained();

            $table->tinyInteger('register_status')->default(RegisterStatusEnum::RS_REQUESTED);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_events');
    }
};
