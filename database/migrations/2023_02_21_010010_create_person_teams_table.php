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
        Schema::create('person_teams', function (Blueprint $table) {

            addMetaData($table);

            $table->foreignId('team_id')->constrained();
            $table->foreignId('person_id')->constrained('persons');

            $table->date('from')->nullable();
            $table->date('to')->nullable();

            $table->foreignId('occupation_id')->nullable()->constrained();
            $table->foreignId('position_id')->nullable()->constrained();

            $table->string('troupe_name')->nullable();
            $table->string('poste')->nullable();
            $table->integer('rank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_teams');
    }
};
