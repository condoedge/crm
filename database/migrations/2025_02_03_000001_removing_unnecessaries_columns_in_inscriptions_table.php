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
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn('is_reinscription');
        });

        Schema::table('inscriptions_short_links', function (Blueprint $table) {
            $table->dropColumn('reregistration');

            $table->string('type', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->tinyInteger('is_reinscription')->default(0);
        });

        Schema::table('inscriptions_short_links', function (Blueprint $table) {
            $table->tinyInteger('reregistration')->default(0);
        });
    }
};
