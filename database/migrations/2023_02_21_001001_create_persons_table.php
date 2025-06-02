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
        Schema::create('persons', function (Blueprint $table) {

            addMetaData($table);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('registered_by')->nullable()->constrained('persons');

            $table->string('email_identity')->nullable();
            $table->tinyInteger('email_status')->nullable(); //null, 1 => verified, 2 => accept-communications

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('inscribed_phone')->nullable();
            $table->string('spoken_languages')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->date('date_of_birth')->nullable();

            $table->tinyInteger('process_completed')->nullable();
        });

        $this->addContactFieldsColumns('persons');

        Schema::table('users', function (Blueprint $table) { //to delete?

            $table->foreignId('person_id')->nullable()->constrained('persons');
            $table->string('permanent_code')->nullable();

        });

        Schema::create('person_links', function (Blueprint $table) {

            addMetaData($table);

            $table->foreignId('person1_id')->nullable()->constrained('persons');
            $table->foreignId('person2_id')->nullable()->constrained('persons');
            $table->foreignId('link_type_id')->nullable()->constrained();
        });
    }

    protected function addContactFieldsColumns($table)
    {
        Schema::table($table, function (Blueprint $table) {
            //$table->foreignId('primary_email_id')->nullable()->constrained('emails');
            $table->foreignId('primary_phone_id')->nullable()->constrained('phones');
            $table->foreignId('primary_billing_address_id')->nullable()->constrained('addresses');
            $table->foreignId('primary_shipping_address_id')->nullable()->constrained('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
