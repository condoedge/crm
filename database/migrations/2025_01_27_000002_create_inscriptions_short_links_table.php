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
        if (!Schema::hasTable('inscriptions_short_links')) {
            Schema::create('inscriptions_short_links', function (Blueprint $table) {
                addMetaData($table);

                $table->string('code', 16);

                $table->foreignId('person_id')->nullable()->constrained('persons');
                $table->foreignId('team_id')->nullable()->constrained('teams');
                $table->foreignId('event_id')->nullable()->constrained('events');

                $table->string('role_id')->nullable()->constrained('roles');

                $table->tinyInteger('reregistration')->nullable();

                $table->tinyInteger('type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions_short_links');
    }
};
