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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj',20)->unique();
            $table->string('corporate_name',250);
            $table->string('trade_name',250)->nullable();
            $table->string('email',250)->unique();
            $table->string('cep',12);
            $table->string('phone',20)->unique();
            $table->string('address_city',150);
            $table->string('address_state',50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
