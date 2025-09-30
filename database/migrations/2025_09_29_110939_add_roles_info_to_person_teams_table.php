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
        Schema::table('person_teams', function (Blueprint $table) {
            $table->string('role_name')->nullable();
            $table->string('role_id')->nullable();

            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person_teams', function (Blueprint $table) {
            $table->dropColumn('role_name');
            $table->dropColumn('role_id');
        });
    }
};
