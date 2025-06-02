<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('person_teams', function (Blueprint $table) {
            $table->foreignId('team_role_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('team_roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_role_id');
        });
    }
};
