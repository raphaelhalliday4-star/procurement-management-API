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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('registration_number')->unique();
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('tax_number')->unique();
            $table->string('bank_name');
            $table->string('bank_account');
            $table->enum('status', ['pending','active','suspended','blacklisted'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
