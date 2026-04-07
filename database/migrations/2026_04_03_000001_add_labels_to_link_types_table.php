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
        Schema::table('link_types', function (Blueprint $table) {
            $table->json('link_from_label')->nullable();
            $table->json('link_to_label')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('link_types', function (Blueprint $table) {
            $table->dropColumn('link_from_label');
            $table->dropColumn('link_to_label');
        });
    }
};
